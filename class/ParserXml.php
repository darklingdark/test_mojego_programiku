<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ParserXml
{
    function __construct(DB_Database $DB)
    {
        $this->DB = $DB;
        
    }
    
    /**
     * odczytak XML z dysku do zmiennej
     * 
     * @param string $sFileNamem - sciezka do pliku
     * @return string
     */
    private function readXmlFile($sFileNamem, $bDebug=false)
    {
        $handle = fopen($sFileNamem, 'r');
        $sXmlContent = fread($handle, filesize($sFileNamem));
        fclose($handle);

        try
        {
           @$content = json_decode(json_encode(simplexml_load_string($sXmlContent)), true);
        }
        catch (Exception $ex)
        {
            throw new Exception('L:'.basename(__FILE__).'('.__LINE__.'): Nie udało sie przeanalizowac XMLa. Xml ma błędny format.' . $ex->getMessage());
        }
        
        if(FALSE === $content)
        {
            throw new Exception('L:'.basename(__FILE__).'('.__LINE__.'): Nie udało sie przeanalizowac XMLa. Xml ma błędny format.'. Debug::vars(error_get_last()));
        }
        
        return $content;
    }

    /**
     * Przeanalizuj XML i zapisz dane w bazie danych
     * 
     * @param string $sFileNamem sciezka do pliku z XML-em
     * @param bool $bDebug - true: tylko wyswietl XML i zatrzymaj skrypt
     * @return int id dodanej paczki danych
     * @throws Exception
     */
    public function analizujXml($sFileNamem, $bDebug = FALSE)
    {
        $sContent = $this->readXmlFile($sFileNamem, $bDebug);
        
        /*
         * Jezeli nie ma tagow Jednostki lub Jednostka
         * konczymy i kasujemy dodana paczke
         */
        if(
               ! isset($sContent['Okres'])
            OR ! isset($sContent['Jednostki']['Jednostka'])
        )
        {
            throw New Exception('L:'.basename(__FILE__).'('.__LINE__.'): Zmienił się schemat XMLa wejściowego Brakuje tagu Okres lub Jednostka.');
        }
        
        $Parser = new Parser($this->DB);
        
        try
        {
            $iPaczkaId = $Parser->addPaczka($sContent['Okres']);
        }
        catch (Exception $ex)
        {
            throw $ex;
        }

        /*
         * podano plik, ktory byl juz analizowany
         */
        if(FALSE === $iPaczkaId)
        {
            throw new Exception('L:'.basename(__FILE__).'('.__LINE__.'): Plik był już wcześniej analizowany.');
        }

        /*
         * Jezeli nie ma tagow Jednostki lub Jednostka
         * konczymy i kasujemy dodana paczke
         */
        if(
               ! isset($sContent['Jednostki'])
            OR ! isset($sContent['Jednostki']['Jednostka'])
        )
        {
            try
            {
                $aResult = $Parser->skasujPaczke($iPaczkaId);
            }
            catch (Exception $ex)
            {
                throw New Exception('L:'.basename(__FILE__).'('.__LINE__.'): Zmienił się schemat XMLa wejściowego Brakuje tagu Jednostki lub Jednostka.' . "\n<br />" . $ex->getMessage());
            }
            
            throw New Exception('L:'.basename(__FILE__).'('.__LINE__.'): Zmienił się schemat XMLa wejściowego Brakuje tagu Jednostki lub Jednostka.');
        }

        try
        {
            $mWynik = $Parser->addJednostka($sContent['Jednostki']['Jednostka'], $iPaczkaId);
        }
        catch (Exception $ex)
        {
            try
            {
                $aResult = $Parser->skasujPaczke($iPaczkaId);
            }
            catch (Exception $ex1)
            {
                throw new Exception('L:'.basename(__FILE__).'('.__LINE__.'): ' . $ex->getMessage() . "\n" . $ex1->getMessage());
            }

            throw $ex;
        }

        /*
         * Nie udalo sie  pobrac zmapowanej nazwy placowki
         * kasujemy dodana paczeke sprawozdan
         */
        if(FALSE === $mWynik)
        {
            try
            {
                $aResult = $Parser->skasujPaczke($iPaczkaId);
            }
            catch (Exception $ex)
            {
                throw New Exception('L:'.basename(__FILE__).'('.__LINE__.'): ' . 'Prosze uzupelnic slowniki placówek. Nie udało się pobrac placówki dla badanego XMLa.' . "\n<br />". $ex->getMessage());
            }

            throw New Exception('L:'.basename(__FILE__).'('.__LINE__.'): ' . 'Prosze uzupelnic slowniki placówek. Nie udało się pobrac placówki dla badanego XMLa.');
        }

        /*
         * Pobieramy
         */

        foreach ($mWynik['sprawozdania'] as $sNazwaSprawozdania => $aSprawozdanie)
        {
            /*
             * Jezeli brakuje jakiegos z tagow oznacza to ze cos sie zmienlo i nie mozemy dalej analizowac danych
             */
            if(
                   ! isset($aSprawozdanie['Naglowek'])
                OR ! isset($aSprawozdanie['Pozycje'])
                OR ! isset($aSprawozdanie['Pozycje']['Pozycja'])
            )
            {
                try
                {
                    $aResult = $Parser->skasujPaczke($iPaczkaId);
                }
                catch (Exception $ex)
                {
                    throw New Exception('L:'.basename(__FILE__).'('.__LINE__.'): ' . 'Zmienił się schemat XMLa wejściowego Brakuje tagu Nagłowek lub Pozycja.' . "\n<br />" . $ex->getMessage());
                }

                try
                {
                    $aResult = $Parser->skasujPlacowke($mWynik['id']);
                }
                catch (Exception $ex)
                {
                    throw New Exception('L:'.basename(__FILE__).'('.__LINE__.'): ' . 'Zmienił się schemat XMLa wejściowego Brakuje tagu Nagłowek lub Pozycja.' . "\n<br />" . $ex->getMessage());
                }

                throw New Exception('Zmienił się schemat XMLa wejściowego Brakuje tagu Nagłowek lub Pozycja.');
            }

            $aParams = [
                'id' => $mWynik['id'],
                'Naglowek' => $aSprawozdanie['Naglowek'],
                'Nazwa' => $sNazwaSprawozdania,
            ];

            try
            {
                $aSprawozdanieWynik = $Parser->addSprawozdanie($aParams);
            }
            catch (Exception $ex)
            {
                try
                {
                    $aResult = $Parser->skasujPaczke($iPaczkaId);
                }
                catch (Exception $ex1)
                {
                    throw new Exception('L:'.basename(__FILE__).'('.__LINE__.'): ' . $ex->getMessage() . "\n" . $ex1->getMessage());
                }

                try
                {
                    $aResult = $Parser->skasujPlacowke($mWynik['id']);
                }
                catch (Exception $ex1)
                {
                    throw new Exception('L:'.basename(__FILE__).'('.__LINE__.'): ' . $ex->getMessage() . "\n" . $ex1->getMessage());
                }

                throw $ex;
            }

            $aListaPozycji = [];

            foreach ($aSprawozdanie['Pozycje']['Pozycja'] as $aPozycja)
            {
                $aDane = [
                    'id' => $aSprawozdanieWynik['id'],
                    'Pozycja' => $aPozycja,
                    'funkcja' => $aSprawozdanieWynik['funkcja'],
                ];

                try
                {
                    $iPozycja = $Parser->addPozycja($aDane);
                }
                catch (Exception $ex)
                {
                    try
                    {
                        $aResult = $Parser->skasujPaczke($iPaczkaId);
                    }
                    catch (Exception $ex1)
                    {
                        throw new Exception('L:'.basename(__FILE__).'('.__LINE__.'): ' . $ex->getMessage() . "\n" . $ex1->getMessage());
                    }

                    try
                    {
                        $aResult = $Parser->skasujPlacowke($mWynik['id']);
                    }
                    catch (Exception $ex1)
                    {
                        throw new Exception('L:'.basename(__FILE__).'('.__LINE__.'): ' . $ex->getMessage() . "\n" . $ex1->getMessage());
                    }

                    try
                    {
                        $aResult = $Parser->skasujSprawozdanie($aSprawozdanieWynik['id']);
                    }
                    catch (Exception $ex1)
                    {
                        throw new Exception('L:'.basename(__FILE__).'('.__LINE__.'): ' . $ex->getMessage() . "\n" . $ex1->getMessage());
                    }

                    try
                    {
                        $aResult = $Parser->skasujSprawozdanie($aSprawozdanieWynik['id']);
                    }
                    catch (Exception $ex1)
                    {
                        throw new Exception('L:'.basename(__FILE__).'('.__LINE__.'): ' . $ex->getMessage() . "\n" . $ex1->getMessage());
                    }

                    try
                    {
                        $aResult = $Parser->skasujPozycje($aListaPozycji);
                    }
                    catch (Exception $ex1)
                    {
                        throw new Exception('L:'.basename(__FILE__).'('.__LINE__.'): ' . $ex->getMessage() . "\n" . $ex1->getMessage());
                    }

                    throw $ex;
                }

                $aListaPozycji[$iPozycja] = $iPozycja;
            }
        }
        
        return $iPaczkaId;
    }
}
