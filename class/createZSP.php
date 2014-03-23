<?php

class createZSP
{
    /**
     *
     * @var DB_Database
     */
    protected $DB;
    
    /**
     *
     * @var DOMDocument
     */
    protected $XML;
    
    /**
     *
     * @var DOMElement
     */
    protected $Sprawozdanie;
    
    /**
     * 
     * @param DB_Database $DB
     */
    function __construct(DB_Database $DB)
    {
        $this->DB = $DB;
    }
    
    public function przygotujXml($iPaczkaId)
    {
        $QueryBuilder = DB_Query_Builder::factory(
                DB_Query_Builder::type_mysql, 
                Query_Sql::paczkaFullInfo($iPaczkaId)
        );
        
        try
        {
            $rResult = $this->DB->query(DB_Database::SELECT, $QueryBuilder->query());
        }
        catch (Exception $ex)
        {
            throw New Exception('L:' . basename(__FILE__) . '(' 
                    . __LINE__ . '): Błąd dodawania paczki sprawozdan do bazy danych: ' 
                    . $ex->getMessage(), $ex->getCode());
        }
        
        $aPaczka = $this->DB->fetch($rResult);
        
        if(FALSE === $aPaczka)
        {
            return FALSE;
        }
        
        $XML = new DOMDocument('1.0', 'UTF-8');
        $Sprawozdanie = $XML->appendChild(new DOMElement('Sprawozdanie'));
        
        $Sprawozdanie->setAttribute('wersja', '2.0');
        
        
        $this
                ->dodajParametry($aPaczka, $Sprawozdanie)
                ->dodajWydatki($aPaczka, $Sprawozdanie);
        
        $ZBIOR = $Sprawozdanie->appendChild(new DOMElement('ZBIOR'));
        $ZBIOR->setAttribute('NAZWA', 'R:\Wydatki\2014\luty 2014\ZSP Nr 21402.xml');
        $ZBIOR->setAttribute('CZAS', '20140307123248');
        $ZBIOR->setAttribute('MD5', '1786980D309380C78C9AE0039DEAA9CC');
        
        $XML->formatOutput = true;
        return $XML->saveXML();
        
    }

    
    /**
     * Dodanie tagow parametrow na poczatku XMLa
     * 
     * @param array $aPaczka
     * @return \createZSP
     */
    protected function dodajParametry($aPaczka, &$Sprawozdanie)
    {
        $Sprawozdanie->setAttribute('Jednostka', $aPaczka['slj_nazwa']);
        $Sprawozdanie->setAttribute('Data', $aPaczka['spr_data_sprawozdania']);
        
        $Parametry = $Sprawozdanie->appendChild(new DOMElement('Parametry'));
        
        $Woje = $Parametry->appendChild(new DOMElement('WOJEWODZTWO'));
        $Woje->setAttribute('kod', '24');
        $Woje->setAttribute('nazwa', 'śląskie');
        
        $Powiat = $Parametry->appendChild(new DOMElement('POWIAT'));
        $Powiat->setAttribute('kod', '68');
        $Powiat->setAttribute('nazwa', 'Jaworzno');
        $Powiat->setAttribute('typ', '');
        
        $Gmina = $Parametry->appendChild(new DOMElement('GMINA'));
        $Gmina->setAttribute('kod', '00');
        $Gmina->setAttribute('nazwa', 'Jaworzno                                ');
        $Gmina->setAttribute('typ', '0');
        
        $Adresat = $Parametry->appendChild(new DOMElement('Adresat'));
        $Adresat->setAttribute('nazwa', 'Urząd Miejski wJaworznie');
        $Adresat->setAttribute('miasto', '');
        $Adresat->setAttribute('adres', '');
        $Adresat->setAttribute('REGON', '');
        $Adresat->setAttribute('skrot', '');
        
        $Nadawca = $Parametry->appendChild(new DOMElement('Nadawca'));
        $Nadawca->setAttribute('nazwa', str_ireplace(' ', '  ', $aPaczka['slj_nazwa']));
        $Nadawca->setAttribute('miasto', $aPaczka['slj_miasto']);
        $Nadawca->setAttribute('REGON', $aPaczka['plc_regon']);
        $Nadawca->setAttribute('adres', $aPaczka['slj_miasto']);
        $Nadawca->setAttribute('skrot', $aPaczka['slj_skrot']);
        $Nadawca->setAttribute('NIP', $aPaczka['slj_nip']);
        $Nadawca->setAttribute('URZAD_SKARB', $aPaczka['slj_urzad_skarb']);
        $Nadawca->setAttribute('RODZ_POD', $aPaczka['slj_rodz_pod']);
        
        $Opisy = $Parametry->appendChild(new DOMElement('opisy'));
        
        $K = $Opisy->appendChild(new DOMElement('K'));
        
        $K1801801300690 = $K->appendChild(new DOMElement('K1801801300690'));
        $K1801801300690->setAttribute('opis', 'Wpływy z różnych opłat"');

        $K1801801300920 = $K->appendChild(new DOMElement('K1801801300920'));
        $K1801801300920->setAttribute('opis', 'Pozostałe odsetki"');

        $K2801801103020 = $K->appendChild(new DOMElement('K2801801103020'));
        $K2801801103020->setAttribute('opis', 'Wydatki osobowe niezaliczone do wynagrodzeń"');

        $K2801801104010 = $K->appendChild(new DOMElement('K2801801104010'));
        $K2801801104010->setAttribute('opis', 'Wynagrodzenia osobowe pracowników"');

        $K2801801104040 = $K->appendChild(new DOMElement('K2801801104040'));
        $K2801801104040->setAttribute('opis', 'Dodatkowe wynagrodzenie roczne"');

        $K2801801104110 = $K->appendChild(new DOMElement('K2801801104110'));
        $K2801801104110->setAttribute('opis', 'Składki na ubezpieczenia społeczne"');

        $K2801801104120 = $K->appendChild(new DOMElement('K2801801104120'));
        $K2801801104120->setAttribute('opis', 'Składki na Fundusz Pracy"');

        $K2801801104210 = $K->appendChild(new DOMElement('K2801801104210'));
        $K2801801104210->setAttribute('opis', 'Zakup materiałów i wyposażenia"');

        $K2801801104240 = $K->appendChild(new DOMElement('K2801801104240'));
        $K2801801104240->setAttribute('opis', 'Zakup pomocy naukowych, dydaktycznych i książek"');

        $K2801801104260 = $K->appendChild(new DOMElement('K2801801104260'));
        $K2801801104260->setAttribute('opis', 'Zakup energii"');

        $K2801801104280 = $K->appendChild(new DOMElement('K2801801104280'));
        $K2801801104280->setAttribute('opis', 'Zakup usług zdrowotnych"');

        $K2801801104300 = $K->appendChild(new DOMElement('K2801801104300'));
        $K2801801104300->setAttribute('opis', 'Zakup usług pozostałych"');

        $K2801801104370 = $K->appendChild(new DOMElement('K2801801104370'));
        $K2801801104370->setAttribute('opis', 'Opłaty z tytułu zakupu uslug telekomunikacyjnych świadczonych w stacjonarnej publicznej sieci telefonicznej"');

        $K2801801104410 = $K->appendChild(new DOMElement('K2801801104410'));
        $K2801801104410->setAttribute('opis', 'Podróże służbowe krajowe"');

        $K2801801104430 = $K->appendChild(new DOMElement('K2801801104430'));
        $K2801801104430->setAttribute('opis', 'Różne opłaty i składki"');

        $K2801801104440 = $K->appendChild(new DOMElement('K2801801104440'));
        $K2801801104440->setAttribute('opis', 'Odpisy na zakładowy fundusz świadczen socjalnych"');

        $K2801801104520 = $K->appendChild(new DOMElement('K2801801104520'));
        $K2801801104520->setAttribute('opis', 'Opłaty na rzecz budżetów jednostek samorządu terytorialnego"');

        $K2801801104700 = $K->appendChild(new DOMElement('K2801801104700'));
        $K2801801104700->setAttribute('opis', 'Szkolenia pracowników nie będących członkami korpusu służby cywilnej"');

        $K2801801204010 = $K->appendChild(new DOMElement('K2801801204010'));
        $K2801801204010->setAttribute('opis', 'Wynagrodzenia osobowe pracowników"');

        $K2801801204040 = $K->appendChild(new DOMElement('K2801801204040'));
        $K2801801204040->setAttribute('opis', 'Dodatkowe wynagrodzenie roczne"');

        $K2801801204110 = $K->appendChild(new DOMElement('K2801801204110'));
        $K2801801204110->setAttribute('opis', 'Składki na ubezpieczenia społeczne"');

        $K2801801204120 = $K->appendChild(new DOMElement('K2801801204120'));
        $K2801801204120->setAttribute('opis', 'Składki na Fundusz Pracy"');

        $K2801801204210 = $K->appendChild(new DOMElement('K2801801204210'));
        $K2801801204210->setAttribute('opis', 'Zakup materiałów i wyposażenia"');

        $K2801801204260 = $K->appendChild(new DOMElement('K2801801204260'));
        $K2801801204260->setAttribute('opis', 'Zakup energii"');

        $K2801801204280 = $K->appendChild(new DOMElement('K2801801204280'));
        $K2801801204280->setAttribute('opis', 'Zakup usług zdrowotnych"');

        $K2801801204300 = $K->appendChild(new DOMElement('K2801801204300'));
        $K2801801204300->setAttribute('opis', 'Zakup usług pozostałych"');

        $K2801801204370 = $K->appendChild(new DOMElement('K2801801204370'));
        $K2801801204370->setAttribute('opis', 'Opłaty z tytułu zakupu uslug telekomunikacyjnych świadczonych w stacjonarnej publicznej sieci telefonicznej"');

        $K2801801204410 = $K->appendChild(new DOMElement('K2801801204410'));
        $K2801801204410->setAttribute('opis', 'Podróże służbowe krajowe"');

        $K2801801204430 = $K->appendChild(new DOMElement('K2801801204430'));
        $K2801801204430->setAttribute('opis', 'Różne opłaty i składki"');

        $K2801801204440 = $K->appendChild(new DOMElement('K2801801204440'));
        $K2801801204440->setAttribute('opis', 'Odpisy na zakładowy fundusz świadczen socjalnych"');

        $K2801801204520 = $K->appendChild(new DOMElement('K2801801204520'));
        $K2801801204520->setAttribute('opis', 'Opłaty na rzecz budżetów jednostek samorządu terytorialnego"');

        $K2801801204700 = $K->appendChild(new DOMElement('K2801801204700'));
        $K2801801204700->setAttribute('opis', 'Szkolenia pracowników nie będących członkami korpusu służby cywilnej"');

        $K2801801303020 = $K->appendChild(new DOMElement('K2801801303020'));
        $K2801801303020->setAttribute('opis', 'Wydatki osobowe niezaliczone do wynagrodzeń"');

        $K2801801304010 = $K->appendChild(new DOMElement('K2801801304010'));
        $K2801801304010->setAttribute('opis', 'Wynagrodzenia osobowe pracowników"');

        $K2801801304040 = $K->appendChild(new DOMElement('K2801801304040'));
        $K2801801304040->setAttribute('opis', 'Dodatkowe wynagrodzenie roczne"');

        $K2801801304110 = $K->appendChild(new DOMElement('K2801801304110'));
        $K2801801304110->setAttribute('opis', 'Składki na ubezpieczenia społeczne"');

        $K2801801304120 = $K->appendChild(new DOMElement('K2801801304120'));
        $K2801801304120->setAttribute('opis', 'Składki na Fundusz Pracy"');

        $K2801801304170 = $K->appendChild(new DOMElement('K2801801304170'));
        $K2801801304170->setAttribute('opis', 'Wynagrodzenia bezosobowe"');

        $K2801801304171 = $K->appendChild(new DOMElement('K2801801304171'));
        $K2801801304171->setAttribute('opis', 'Wynagrodzenia bezosobowe"');

        $K2801801304210 = $K->appendChild(new DOMElement('K2801801304210'));
        $K2801801304210->setAttribute('opis', 'Zakup materiałów i wyposażenia"');

        $K2801801304240 = $K->appendChild(new DOMElement('K2801801304240'));
        $K2801801304240->setAttribute('opis', 'Zakup pomocy naukowych, dydaktycznych i książek"');

        $K2801801304260 = $K->appendChild(new DOMElement('K2801801304260'));
        $K2801801304260->setAttribute('opis', 'Zakup energii"');

        $K2801801304280 = $K->appendChild(new DOMElement('K2801801304280'));
        $K2801801304280->setAttribute('opis', 'Zakup usług zdrowotnych"');

        $K2801801304300 = $K->appendChild(new DOMElement('K2801801304300'));
        $K2801801304300->setAttribute('opis', 'Zakup usług pozostałych"');

        $K2801801304301 = $K->appendChild(new DOMElement('K2801801304301'));
        $K2801801304301->setAttribute('opis', 'Zakup usług pozostałych"');

        $K2801801304350 = $K->appendChild(new DOMElement('K2801801304350'));
        $K2801801304350->setAttribute('opis', 'Zakup usług dostępu do sieci Internet"');

        $K2801801304370 = $K->appendChild(new DOMElement('K2801801304370'));
        $K2801801304370->setAttribute('opis', 'Opłaty z tytułu zakupu uslug telekomunikacyjnych świadczonych w stacjonarnej publicznej sieci telefonicznej"');

        $K2801801304410 = $K->appendChild(new DOMElement('K2801801304410'));
        $K2801801304410->setAttribute('opis', 'Podróże służbowe krajowe"');

        $K2801801304421 = $K->appendChild(new DOMElement('K2801801304421'));
        $K2801801304421->setAttribute('opis', 'Podróże służbowe zagraniczne"');

        $K2801801304430 = $K->appendChild(new DOMElement('K2801801304430'));
        $K2801801304430->setAttribute('opis', 'Różne opłaty i składki"');

        $K2801801304440 = $K->appendChild(new DOMElement('K2801801304440'));
        $K2801801304440->setAttribute('opis', 'Odpisy na zakładowy fundusz świadczen socjalnych"');

        $K2801801304520 = $K->appendChild(new DOMElement('K2801801304520'));
        $K2801801304520->setAttribute('opis', 'Opłaty na rzecz budżetów jednostek samorządu terytorialnego"');

        $K2801801304700 = $K->appendChild(new DOMElement('K2801801304700'));
        $K2801801304700->setAttribute('opis', 'Szkolenia pracowników nie będących członkami korpusu służby cywilnej"');

        $K2801801306050 = $K->appendChild(new DOMElement('K2801801306050'));
        $K2801801306050->setAttribute('opis', 'Wydatki inwestycyjne jednostek budżetowych"');

        $K2801801463020 = $K->appendChild(new DOMElement('K2801801463020'));
        $K2801801463020->setAttribute('opis', 'Wydatki osobowe niezaliczone do wynagrodzeń"');

        $K2801801464210 = $K->appendChild(new DOMElement('K2801801464210'));
        $K2801801464210->setAttribute('opis', 'Zakup materiałów i wyposażenia"');

        $K2801801464410 = $K->appendChild(new DOMElement('K2801801464410'));
        $K2801801464410->setAttribute('opis', 'Podróże służbowe krajowe"');

        $K2801801464700 = $K->appendChild(new DOMElement('K2801801464700'));
        $K2801801464700->setAttribute('opis', 'Szkolenia pracowników nie będących członkami korpusu służby cywilnej"');

        $Z = $Opisy->appendChild(new DOMElement('Z'));
        
        $D = $Opisy->appendChild(new DOMElement('D'));
        
        $DJ50 = $D->appendChild(new DOMElement('DJ50'));
        $DJ50->setAttribute('opis', 'ZESPÓŁ SZKÓŁ PONADGIMNAZJALNYCH NR2');
        
        $R = $Opisy->appendChild(new DOMElement('R'));
        $RWP = $R->appendChild(new DOMElement('RWP'));
        $RWP->setAttribute('opis', 'Własne Powiat');
        $RWG = $R->appendChild(new DOMElement('RWG'));
        $RWG->setAttribute('opis', 'Własne Gmina');
        $RUt = $R->appendChild(new DOMElement('RUt'));
        $RUt->setAttribute('opis', 'ZAGRANICZNE PRAKTYKI I STANDARDY (ZSP 2)');
        
        $SlownikRodz = $Parametry->appendChild(new DOMElement('SLOWNIK_RODZ'));
        $SlownikRodz->setAttribute('lista', 'WG,WP,ZG,ZP,PP,SG,SP,DP,DG,PJ,PG,DF,DS,ZS,UG,GS,UP,DW,PZ,US,UT,FP,UE,UW,DZ,UM,PF,GF,WZ,WS,P,R,DU,UA,UN,UF,ug,UZ,Uz,Us,UK,UB,UC,UD,UH,UR,UI,UJ,Ub,Uc,DR,UU,UO,Ut,UL');
        
        $POZABUDZETOWE = $Parametry->appendChild(new DOMElement('POZABUDZETOWE'));
        $POZABUDZETOWE->setAttribute('lista', 'DS,DW,DZ');
        
        $RODZ_27ZZ = $Parametry->appendChild(new DOMElement('RODZ.27ZZ'));
        $RODZ_27ZZ->setAttribute('lista', 'DS,DZ');
        
        $RODZ_50 = $Parametry->appendChild(new DOMElement('RODZ.50'));
        $RODZ_50->setAttribute('lista', 'ZG,ZP,DS,DZ');
        
        $RODZ_30 = $Parametry->appendChild(new DOMElement('RODZ.30'));
        $RODZ_30->setAttribute('lista', '');
        
        $RODZ_31 = $Parametry->appendChild(new DOMElement('RODZ.31'));
        $RODZ_31->setAttribute('lista', '');
        
        $RODZ_33 = $Parametry->appendChild(new DOMElement('RODZ.33'));
        $RODZ_33->setAttribute('lista', '');
        
        $RODZ_34 = $Parametry->appendChild(new DOMElement('RODZ.34'));
        $RODZ_34->setAttribute('lista', 'DW');
        
        $RODZ_WNW = $Parametry->appendChild(new DOMElement('RODZ.WNW'));
        $RODZ_WNW->setAttribute('lista', '&quot;&quot;');
        
        $FUNDUSZE = $Parametry->appendChild(new DOMElement('FUNDUSZE'));
        $FUNDUSZE->setAttribute('lista', '');
        
        $PAR_PRZYCHODOWE = $Parametry->appendChild(new DOMElement('PAR_PRZYCHODOWE'));
        $PAR_PRZYCHODOWE->setAttribute('lista', '');
        
        $PAR_ROZCHODOWE = $Parametry->appendChild(new DOMElement('PAR_ROZCHODOWE'));
        $PAR_ROZCHODOWE->setAttribute('lista', '');
        
        $Osoby = $Parametry->appendChild(new DOMElement('Osoby'));
        $Osoby->setAttribute('kierownik', '');
        $Osoby->setAttribute('kierownik_tel', '');
        $Osoby->setAttribute('skarbnik', '');
        $Osoby->setAttribute('skarbnik_tel', '');
        
        $ParamBest = $Parametry->appendChild(new DOMElement('ParamBest'));
        $ParamBest->setAttribute('bestia_nazwa', '');
        $ParamBest->setAttribute('bestia_typ', '');
        
        return $this;
    }
    
    protected function dodajWydatki($aPaczka, &$Sprawozdanie)
    {
        $aSelect = [
            'select' => [
                'poz' => [
                    'id_spr',
                    'poz_dzial',
                    'poz_rozdzial',
                    'poz_paragraf',
                    'poz_p4',
                    'poz_pl',
                    'poz_za',
                    'poz_ww',
                    'poz_zo',
                    'poz_zw',
                    'poz_wn',
                    'poz_lu',
                    'poz_rb',
                ],
            ],
            'from' => [
                ['pozycja', 'poz'],
            ],
            'where' => [
                ['poz.id_spr', '=', $aPaczka['id_spr']],
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
                    . $ex->getMessage(), $ex->getCode());
        }
        
        $aPozycje = $this->DB->fetchAll($rResult);
        
        $Wydatki = $Sprawozdanie->appendChild(new DOMElement('Wydatki'));
        
        $Dane = $Wydatki->appendChild(new DOMElement('Dane'));
        
        foreach ($aPozycje as $aPozycja)
        {
            $DaneWew = $Dane->appendChild(new DOMElement('Dane'));
            
            $DaneWew->setAttribute('DB', 'J50');
            $DaneWew->setAttribute('RODZ', 'WG');
            $DaneWew->setAttribute('KONTO', $aPozycja['poz_dzial'].$aPozycja['poz_rozdzial'].$aPozycja['poz_paragraf']);
            $DaneWew->setAttribute('FPLAN', str_ireplace('.00', '', $aPozycja['poz_pl']));
            $DaneWew->setAttribute('FWYK', $aPozycja['poz_pl']);
            $DaneWew->setAttribute('ZAAN', $aPozycja['poz_za']);
            $DaneWew->setAttribute('ZOB', $aPozycja['poz_zo']);
            $DaneWew->setAttribute('ZOBW', $aPozycja['poz_zw']);
            $DaneWew->setAttribute('DZIAL', $aPozycja['poz_dzial']);
            $DaneWew->setAttribute('ROZDZIAL', $aPozycja['poz_rozdzial']);
            $DaneWew->setAttribute('PAR', $aPozycja['poz_paragraf']);
            $DaneWew->setAttribute('POZ', '0');
            $DaneWew->setAttribute('ZADANIE', '000000000');
            $DaneWew->setAttribute('ZOBWU', '0');
            $DaneWew->setAttribute('FUNDS', '0');
            $DaneWew->setAttribute('WNWGAS', '0');
        }
        
        return $this;
//        Debug::println('L:' . basename(__FILE__) . '(' . __LINE__ . ')$aPozycje',$aPozycje);
//        Debug::_exit('L:' . basename(__FILE__) . '(' . __LINE__ . ') Exit.');
    }
}