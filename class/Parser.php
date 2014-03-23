<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Parser
{
    /**
     * Polaczenie z baza danych
     * @var DB_Database
     */
    protected $sDataDodania;
    protected $DB;
    protected $aOkres = [];
    protected $aPozycjeSprawozdan = [];
    protected $aJednostki = [
        'sprawozdania' => []
    ];
    
    /**
     * 
     * @param DB_Database $DB
     */
    public function __construct(DB_Database $DB)
    {
        $this->DB = $DB;
        $this->sDataDodania = date('Y-m-d H:i:s');
    }
    
    /**
     * Dodanie informacji o nowej paczce
     * 
     * @param array $aOkresInfo
     * @return int|boolean - false jezeli jest juz paczka o podanych danych, 
     * int - id dodanej paczki
     * @throws Exception
     */
    public function addPaczka($aOkresInfo)
    {
        if(
               ! isset($aOkresInfo['Rok'])
            OR ! isset($aOkresInfo['TypOkresu'])
            OR ! isset($aOkresInfo['Okres'])
        )
        {
            throw new Exception('L:' . basename(__FILE__) . '(' 
                    . __LINE__ . '): Zmienił sie schemat danych w nagłówku paczki ' . Debug::vars($aOkresInfo));
        }
        
        $this->aOkres = $aOkresInfo;
        
        $aSelect = [
            'select' => [
                'id_psp',
            ],
            'from' => [
                ['paczka_sprawozdan'],
            ],
            'where' => [
                ['psp_rok', '=', $aOkresInfo['Rok']],
                ['psp_typ_okresu', '=', $aOkresInfo['TypOkresu']],
                ['psp_okres', '=', $aOkresInfo['Okres']],
                ['psp_s', '=', 'N'],
            ]
        ];
        $QueryBuilder = DB_Query_Builder::factory(DB_Query_Builder::type_mysql, $aSelect);
        
        try
        {
            $rResult = $this->DB->query(DB_Database::SELECT, $QueryBuilder->query());
        }
        catch (Exception $ex)
        {
            throw New Exception('L:' . basename(__FILE__) . '(' 
                    . __LINE__ . '): Błąd dodawania paczki sprawozdan do bazy danych: ' 
                    . $ex->getMessage());
        }
        
        $iIdStarejPaczki = false;
        if(0 < $this->DB->count_found_rows())
        {
            $aIdStarejPaczki = $this->DB->fetch($rResult);
            $iIdStarejPaczki = $aIdStarejPaczki['id_psp'];
            
            $aUpdate = [
                'table' => 'paczka_sprawozdan',
                'set' => [
                    'psp_s' => 'Y',
                    'psp_data_usu' => $this->sDataDodania,
                ],
                'where' => [
                    'id_psp' => $iIdStarejPaczki,
                    'psp_s' => 'N',
                ]
            ];
            
            $sqlUpdate = $QueryBuilder->createUpdateQuery($aUpdate, $this->DB);

            try
            {
                $aResult = $this->DB->query(DB_Database::UPDATE, $sqlUpdate);
            }
            catch (Exception $ex)
            {
                throw New Exception('L:' . basename(__FILE__) . '(' 
                        . __LINE__ . '): Błąd kasowania starej paczki sprawozdan w bazie danych: ' 
                        . $ex->getMessage());
            }
            
        }
        
        $aInsert = [
            'table' => 'paczka_sprawozdan',
            'values' => [
                'psp_rok' => trim($aOkresInfo['Rok']),
                'psp_typ_okresu' => trim($aOkresInfo['TypOkresu']),
                'psp_okres' => trim($aOkresInfo['Okres']),
                'psp_data_wpr' => $this->sDataDodania,
            ]
        ];
        
        $sqlInsert = $QueryBuilder->createInsertQuery($aInsert, $this->DB);
        
        try
        {
            $aResult = $this->DB->query(DB_Database::INSERT, $sqlInsert);
        }
        catch (Exception $ex)
        {
            throw New Exception('L:' . basename(__FILE__) . '(' 
                    . __LINE__ . '): Błąd dodawania paczki sprawozdan do bazy danych: ' 
                    . $ex->getMessage());
        }
        
        return $this->DB->insert_id();
    }
    
    /**
     * analiza informacji na temat jednostki
     * 
     * @param array $aJednostki
     * @param int $iPaczkaId
     * @return array|false - false jezeli nie udalo si dopasowac mapowania jednostki w postaci: <pre>
     * [
     *      'id' => 'id jednostki w bazie danych',
            'sprawozdania' => 'Sprawozdania',
     * ]
     * </pre>
     * @throws Exception
     */
    public function addJednostka($aJednostki, $iPaczkaId)
    {
        /*
         * Jezeli brakuje jakiegos z tagow oznacza to ze cos sie zmienlo i nie mozemy dalej analizowac danych
         */
        if(
               ! is_numeric($iPaczkaId)
            OR ! isset($aJednostki['Nazwa'])
            OR ! isset($aJednostki['Typ'])
            OR ! isset($aJednostki['Regon'])
            OR ! isset($aJednostki['WK'])
            OR ! isset($aJednostki['PK'])
            OR ! isset($aJednostki['GK'])
            OR ! isset($aJednostki['GT'])
            OR ! isset($aJednostki['PT'])
            OR ! isset($aJednostki['Sprawozdania'])
        )
        {
            unset($aJednostki['Sprawozdania']);
            throw new Exception('L:' . basename(__FILE__) . '(' 
                    . __LINE__ . '): Zmienił sie schemat danych w jednostce dla id=' . $iPaczkaId . ': ' . Debug::vars($aJednostki) . ' is_numeric($iPaczkaId):' . Debug::vars(is_numeric($iPaczkaId)));
        }
        
        /*
         * Pobieramy mapowanie nazwy jednostki
         */
        $aSelect = [
            'select' => [
                'id_slj',
            ],
            'from' => [
                ['slownik_jednostek'],
            ],
            'where' => [
                ['slj_regon', '=', trim($aJednostki['Regon'])],
                ['slj_s', '=', 'N'],
            ]
        ];
        $QueryBuilder = DB_Query_Builder::factory(DB_Query_Builder::type_mysql, $aSelect);
        
        try
        {
            $rResult = $this->DB->query(DB_Database::SELECT, $QueryBuilder->query());
        }
        catch (Exception $ex)
        {
            throw New Exception('L:' . basename(__FILE__) . '(' 
                    . __LINE__ . '): Błąd dodawania slownika modelu do bazy danych: ' 
                    . $ex->getMessage());
        }
        
        if(0 == $this->DB->count_found_rows())
        {
            return FALSE;
        }
        
        $aMapowanieJednostki = $this->DB->fetch($rResult);
        
        /*
         * dodajemy nowa wpis dla jednostki do bazy danych
         */
        $aInsert = [
            'table' => 'placowka',
            'values' => [
                'id_psp' => $iPaczkaId,
                'id_slj' => trim($aMapowanieJednostki['id_slj']),
                'plc_nazwa' => trim($aJednostki['Nazwa']),
                'plc_typ' => trim($aJednostki['Typ']),
                'plc_regon' => $aJednostki['Regon'],
                'plc_wk' => trim($aJednostki['WK']),
                'plc_pk' => trim($aJednostki['PK']),
                'plc_gk' => trim($aJednostki['GK']),
                'plc_gt' => trim($aJednostki['GT']),
                'plc_pt' => trim($aJednostki['PT']),
                'plc_data_dodania' => $this->sDataDodania,
            ]
        ];
        
        $sqlInsert = $QueryBuilder->createInsertQuery($aInsert, $this->DB);
        try
        {
            $aResult = $this->DB->query(DB_Database::INSERT, $sqlInsert);
        }
        catch (Exception $ex)
        {
            throw New Exception('L:' . basename(__FILE__) . '(' 
                    . __LINE__ . '): Błąd dodawania jednostki do bazy danych: ' 
                    . $ex->getMessage());
        }
        
        $iJednostka = $this->DB->insert_id();
        
        return [
            'id' => $iJednostka,
            'sprawozdania' => $aJednostki['Sprawozdania']
        ];
    }
    
    /**
     * 
     * @param array $aJednostka tablica w postaci: <pre>
     * [
     *      'id' => $idJednostki,
     *      'Naglowek' => 'Dane naglowka sprawozdania',
     *      'Nazwa' => 'numer sprawozdania',
     * ]
     * </pre>
     *
     * @return array w postaci:
     * [
     *      'id' => 'id dodanego sprawozdania',
     *      'funkcja' => 'nazwa funkcji analizy',
     * ]
     * 
     * @throws Exception
     */
    public function addSprawozdanie(array $aSprawozdanie)
    {
        /*
         * Jezeli brakuje jakiegos z tagow oznacza to ze cos sie zmienlo i nie mozemy dalej analizowac danych
         */
        if(
               ! isset($aSprawozdanie['Naglowek'])
            OR ! isset($aSprawozdanie['Nazwa'])
            OR ! isset($aSprawozdanie['id'])
            OR ! isset($aSprawozdanie['Naglowek']['Wersja'])
            OR ! isset($aSprawozdanie['Naglowek']['DataSprawozdania'])
        )
        {
            throw new Exception('L:' . basename(__FILE__) . '(' 
                    . __LINE__ . '): Zmienił sie schemat danych w sprawozdaniu ' . Debug::vars($aSprawozdanie));
        }
        
        $aInsert = [
            'table' => 'sprawozdanie',
            'values' => [
                'id_plc' => trim($aSprawozdanie['id']),
                'spr_data_wpr' => $this->sDataDodania,
                'spr_wersja' => trim($aSprawozdanie['Naglowek']['Wersja']),
                'spr_nazwa' => trim($aSprawozdanie['Nazwa']),
                'spr_data_sprawozdania' => trim($aSprawozdanie['Naglowek']['DataSprawozdania']),
            ],
        ];
        
        $QueryBuilder = DB_Query_Builder::factory(DB_Query_Builder::type_mysql);
        
        $sqlInsert = $QueryBuilder->createInsertQuery($aInsert, $this->DB);
        
        try
        {
            $aResult = $this->DB->query(DB_Database::INSERT, $sqlInsert);
        }
        catch (Exception $ex)
        {
            throw New Exception('L:' . basename(__FILE__) . '(' 
                    . __LINE__ . '): Błąd dodawania informacji o sprawozdaniu do bazy danych: ' 
                    . $ex->getMessage());
        }
        
        $aSprawozdanieWynik = [
            'id' => $this->DB->insert_id(),
            'funkcja' => str_ireplace('-', '_', $aSprawozdanie['Nazwa'])
        ];
        
        return $aSprawozdanieWynik;
    }
    
    /**
     * 
     * @param array $aDane tablica w postaci: <pre>
     * [
     *      'id' => 'idSprawozdania',
     *      'Pozycja' => 'dane pozycji',
     *      'funkcja' => 'funkcja parsujaca',
     * ];
     * </pre>
     * @return type
     */
    public function addPozycja($aDane)
    {
        /*
         * Jezeli brakuje jakiegos z tagow oznacza to ze cos sie zmienlo i nie mozemy dalej analizowac danych
         */
        if(
               ! isset($aDane['id'])
            OR ! isset($aDane['funkcja'])
        )
        {
            throw new Exception('L:' . basename(__FILE__) . '(' 
                    . __LINE__ . '): Zmienił sie schemat danych w Pozycji ' . Debug::vars($aDane));
        }
        
        $sFunkcja = $aDane['funkcja'];
        
        try
        {
            return $this->$sFunkcja($aDane);
        }
        catch (Exception $ex)
        {
            throw $ex;
        }
    }
    
    //==========================================================================
    //========= INdywidualne Funkcje analizy raportow wg nazwy sprawozdania ====
    //==========================================================================
    
    
    /**
     * Analiza sprawozdania Rb-28s
     * 
     * @param array $aDane - tablica w postaci: <pre>
     * [
     *      'id' => 'idSprawozdania',
     *      'funkcja' => 'funkcja parsujaca',
     *      'Pozycja' => 'dane pozycji',
     * ]
     * </pre>
     * @return int - id dodanej pozycji
     * @throws Exception
     */
    protected function Rb_28s($aDane)
    {
        /*
         * Jezeli brakuje jakiegos z tagow oznacza to ze cos sie zmienlo i nie mozemy dalej analizowac danych
         */
        if(
               ! isset($aDane['id'])
            OR ! isset($aDane['Pozycja'])
            OR ! isset($aDane['Pozycja']['Dzial'])
            OR ! isset($aDane['Pozycja']['Rozdzial'])
            OR ! isset($aDane['Pozycja']['Paragraf'])
            OR ! isset($aDane['Pozycja']['P4'])
            OR ! isset($aDane['Pozycja']['PL'])
            OR ! isset($aDane['Pozycja']['ZA'])
            OR ! isset($aDane['Pozycja']['WW'])
            OR ! isset($aDane['Pozycja']['ZO'])
            OR ! isset($aDane['Pozycja']['ZW'])
            OR ! isset($aDane['Pozycja']['WN'])
            OR ! isset($aDane['Pozycja']['LU'])
            OR ! isset($aDane['Pozycja']['RB'])
        )
        {
            throw new Exception('L:' . basename(__FILE__) . '(' 
                    . __LINE__ . '): Zmienił sie schemat danych w Pozycji ' . Debug::vars($aDane));
        }
        $aInsert = [
            'table' => 'pozycja',
            'values' => [
                'id_spr' => trim($aDane['id']),
                'poz_dzial' => trim($aDane['Pozycja']['Dzial']),
                'poz_rozdzial' => trim($aDane['Pozycja']['Rozdzial']),
                'poz_paragraf' => trim($aDane['Pozycja']['Paragraf']),
                'poz_p4' => trim($aDane['Pozycja']['P4']),
                'poz_pl' => trim($aDane['Pozycja']['PL']),
                'poz_za' => trim($aDane['Pozycja']['ZA']),
                'poz_ww' => trim($aDane['Pozycja']['WW']),
                'poz_zo' => trim($aDane['Pozycja']['ZO']),
                'poz_zw' => trim($aDane['Pozycja']['ZW']),
                'poz_wn' => trim($aDane['Pozycja']['WN']),
                'poz_lu' => trim($aDane['Pozycja']['LU']),
                'poz_rb' => trim($aDane['Pozycja']['RB']),
                'poz_data_wpr' => $this->sDataDodania,
            ],
        ];
        
        $QueryBuilder = DB_Query_Builder::factory(DB_Query_Builder::type_mysql);
        
        $sqlInsert = $QueryBuilder->createInsertQuery($aInsert, $this->DB);
        
        try
        {
            $aResult = $this->DB->query(DB_Database::INSERT, $sqlInsert);
        }
        catch (Exception $ex)
        {
            throw New Exception('L:' . basename(__FILE__) . '(' 
                    . __LINE__ . '): Błąd dodawania informacji o sprawozdaniu do bazy danych: ' 
                    . $ex->getMessage());
        }
        
        return $this->DB->insert_id();
    }
    
    //==========================================================================
    //========= Funkcje czyszczace dane w przypadku bledu ======================
    //==========================================================================
    
    /**
     * Kasowanie paczki sprawozdan 
     * wykonywane w przypadku bledu gdzie trzeba wyczyscic dodane dane
     * 
     * @param int $iPaczkaId
     * @throws Exception
     */
    public function skasujPaczke($iPaczkaId)
    {
        $QueryBuilder = DB_Query_Builder::factory(DB_Query_Builder::type_mysql);

        $aDelete = [
            'table' => 'paczka_sprawozdan',
            'where' => [
                'id_psp' => $iPaczkaId,
            ]
        ];

        $sqlDelete = $QueryBuilder->createDeleteQuery($aDelete, $this->DB);

        try
        {
            $aResult = $this->DB->query(DB_Database::DELETE, $sqlDelete);
        }
        catch (Exception $ex)
        {
            throw New Exception('L:' . basename(__FILE__) . '(' 
                    . __LINE__ . '): Błąd dodawania paczki sprawozdan do bazy danych: ' 
                    . $ex->getMessage());
        }
        
        return $this;
    }
    
    /**
     * Kasowanie placowki
     * wykonywane w przypadku bledu gdzie trzeba wyczyscic dodane dane
     * 
     * @param int $iPlacowkaId
     * @throws Exception
     */
    public function skasujPlacowke($iPlacowkaId)
    {
        $QueryBuilder = DB_Query_Builder::factory(DB_Query_Builder::type_mysql);

        $aDelete = [
            'table' => 'placowka',
            'where' => [
                'id_plc' => $iPlacowkaId,
            ]
        ];

        $sqlDelete = $QueryBuilder->createDeleteQuery($aDelete, $this->DB);

        try
        {
            $aResult = $this->DB->query(DB_Database::DELETE, $sqlDelete);
        }
        catch (Exception $ex)
        {
            throw New Exception('L:' . basename(__FILE__) . '(' 
                    . __LINE__ . '): Błąd dodawania kasowania placowki z bazy danych: ' 
                    . $ex->getMessage());
        }
        
        return $this;
    }
    
    /**
     * Kasowanie sprawozdania
     * wykonywane w przypadku bledu gdzie trzeba wyczyscic dodane dane
     * 
     * @param int $iSprawozdanieId
     * @throws Exception
     */
    public function skasujSprawozdanie($iSprawozdanieId)
    {
        $QueryBuilder = DB_Query_Builder::factory(DB_Query_Builder::type_mysql);

        $aDelete = [
            'table' => 'sprawozdanie',
            'where' => [
                'id_spr' => $iSprawozdanieId,
            ]
        ];

        $sqlDelete = $QueryBuilder->createDeleteQuery($aDelete, $this->DB);

        try
        {
            $aResult = $this->DB->query(DB_Database::DELETE, $sqlDelete);
        }
        catch (Exception $ex)
        {
            throw New Exception('L:' . basename(__FILE__) . '(' 
                    . __LINE__ . '): Błąd dodawania kasowania sprawozdania z bazy danych: ' 
                    . $ex->getMessage());
        }
        
        return $this;
    }
    
    /**
     * Kasowanie sprawozdania
     * wykonywane w przypadku bledu gdzie trzeba wyczyscic dodane dane
     * 
     * @param array $aPozycjaIds tablica id pozycji sprawozdania
     * @throws Exception
     */
    public function skasujPozycje(array $aPozycjaIds)
    {
        $QueryBuilder = DB_Query_Builder::factory(DB_Query_Builder::type_mysql);

        foreach($aPozycjaIds as $iPozycjaId)
        {
            $aDelete = [
                'table' => 'sprawozdanie',
                'where' => [
                    'id_spr' => $iPozycjaId,
                ]
            ];

            $sqlDelete = $QueryBuilder->createDeleteQuery($aDelete, $this->DB);

            try
            {
                $aResult = $this->DB->query(DB_Database::DELETE, $sqlDelete);
            }
            catch (Exception $ex)
            {
                throw New Exception('L:' . basename(__FILE__) . '(' 
                        . __LINE__ . '): Błąd dodawania kasowania sprawozdania z bazy danych: ' 
                        . $ex->getMessage());
            }
        }
        
        return $this;
    }
}