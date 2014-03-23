<?php

/**
 * Eksport XML w formacie Akol.pl
 */
class Eksport_Akol_XML extends Eksport_Factory 
{
    /**
     * @var DOMDocument 
     */
    protected $XML;

    /**
     * @var Ogloszenie_Default[]
     */
    protected $aLista = array();
    
    /**
     * @var Ogloszenie_Default[]
     */
    protected $aListaUsun = array();
    
    /**
     * Limit ogloszen w paczce
     * 
     * @var int
     */
    protected $iLimit = 1000;
    
    /**
     * Limit ogloszen w paczce
     * 
     * @var int
     */
    protected $iPaczka = 1;

    /**
     * Lista zdjec
     * 
     * @var array
     */
    protected $aFiles = array();
    
    /**
     *
     * @var array
     */
    protected $aAuiAdded = array();

    /**
     *
     * @var array
     */
    
    protected $aAuiModified = array();
    
    /**
     *
     * @var array
     */
    protected $aAuiRemoved = array();

    /**
     * Konstruktor 
     * 
     * @param \Eksport_Lang $Lang Slownik
     * @param array $this->aEksport Tablica parametrow eksportu
     */
    public function __construct($Lang, $aEksport)
    {
        $this->Lang = $Lang;
        $this->aEksport = $aEksport;
    }

    /**
     * @inheritdoc
     */
    public function addAdvert(Ogloszenie_Default $Advert)
    {
        if(!isset($this->aLista[$this->iPaczka]))
        {
            $this->aLista[$this->iPaczka] = array();
            $this->aFiles[$this->iPaczka] = array();
            $this->aAuiAdded[$this->iPaczka] = array();
            $this->aAuiModified[$this->iPaczka] = array();
            $this->aAuiRemoved[$this->iPaczka] = array();
        }
        
        if(count($this->aLista[$this->iPaczka]) < $this->iLimit)
        {
            $this->aLista[$this->iPaczka][] = $Advert;
        }
        else
        {
            $this->aLista[$this->iPaczka][] = $Advert;
            $this->iPaczka++;
        }
    }
    
    /**
     * @inheritdoc
     */
    public function removeAdvert(Ogloszenie_Default $Advert)
    {
        $this->aListaUsun[] = $Advert;
    }
    
    public function generate()
    {
        $aXmls = array();
        
        //echo Debug::vars($this->aLista);
        
        if( 0 == count($this->aLista))
        {
            $this->aLista[1] = array();
            $this->aFiles[1] = array();
            $this->aAuiAdded[1] = array();
            $this->aAuiModified[1] = array();
            $this->aAuiRemoved[1] = array();
        }
        
        if( 0 == count($this->aAuiRemoved))
        {
            $this->aAuiRemoved = array();
        }
        
        foreach (array_keys($this->aLista) as $paczka)
        {
            $EksportData = new Eksport_Data($this->_generate($paczka));
            
            if('Y' == $this->aEksport['eks_eksport_pelny'])
            {
                $this->aEksport['eks_eksport_pelny'] = 'N';
                $this->aEksport['eks_wymus_eksport'] = 'Y';
            }
            
            if($this->aEksport['eks_plik_data'] == 'N')
            {
                $EksportData->setDestinationName('Akol_' . $this->aEksport['eks_klucz_komisu'] . '.xml');
                $EksportData->setZipName('Akol_' . $this->aEksport['eks_klucz_komisu'] . '.zip');
            }
            else
            {
                $EksportData->setDestinationName('Akol_' . $this->aEksport['eks_klucz_komisu'] . '_' . date('YmdHis') . '.xml');
                $EksportData->setZipName('Akol_' . $this->aEksport['eks_klucz_komisu'] . '_' . date('YmdHis') . '.zip');
            }
            
            $EksportData->setSourceName(tempnam(sys_get_temp_dir(), $this->aEksport['id_eks']));
            $EksportData->setFiles($this->aFiles[$paczka]);
            
            foreach ($this->aAuiAdded[$paczka] as $id => $tab)
            {
                $EksportData->addAdded($id, $tab);
            }
            
            foreach ($this->aAuiModified[$paczka] as $id => $tab)
            {
                $EksportData->addModified($id, $tab);
            }

            foreach ($this->aAuiRemoved[$paczka] as $id => $tab)
            {
                $EksportData->addRemoved($id, $tab);
            }

            $aXmls[] = $EksportData;
        }
        
        return $aXmls;
    }

    public function _generate($paczka)
    {

        $this->XML = new DOMDocument('1.0', 'UTF-8');

        $XML = $this->XML->appendChild(new DOMElement('eksport_ogloszen'));
        $XML->setAttribute('wersja', '1.0');
        $XML->setAttribute('data_wygenerowania', "" . date('Y-m-d', time()));
        $XML->setAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
        $XML->setAttribute('xsi:noNamespaceSchemaLocation', $this->aEksport['CFG_WWW'] . "/xml/akol_schema.xsd");

        $xmlTyp = $XML->appendChild(new DOMElement('rodzaj_eksportu'));

        if ('Y' == $this->aEksport['eks_eksport_pelny'])
        {
            $xmlTyp->appendChild($this->XML->createTextNode('pelny'));
        }
        else
        {
            $xmlTyp->appendChild($this->XML->createTextNode('przyrostowy'));
        }

        $xmlKomis = $XML->appendChild(new DOMElement('komis'));

        $this->_array2xml($xmlKomis, $this->_komis(), 'createTextNode');

        $xmlOgloszenia = $XML->appendChild(new DOMElement('ogloszenia'));

        $aXmlRd = array(
            'o' => $xmlOgloszenia->appendChild(new DOMElement('osobowe')),
            'd' => $xmlOgloszenia->appendChild(new DOMElement('dostawcze')),
            'c' => $xmlOgloszenia->appendChild(new DOMElement('ciezarowe')),
            'a' => $xmlOgloszenia->appendChild(new DOMElement('autobusy')),
            'm' => $xmlOgloszenia->appendChild(new DOMElement('motocykle')),
            's' => $xmlOgloszenia->appendChild(new DOMElement('specjalne')),
            'p' => $xmlOgloszenia->appendChild(new DOMElement('czesci')),
        );

        foreach ($this->aLista[$paczka] as $Adv)
        {
            $xmlOgloszenie = $aXmlRd[$Adv->rd()]->appendChild(new DOMElement('ogloszenie'));

            switch ($Adv->rd())
            {
                case 'o':
                case 'd':

                    $aXmlFieldCdata = array(
                        'marka' => $Adv->mrx_nazwa,
                        'model' => $Adv->mdx_nazwa,
                        'kolor_producenta' => $Adv->aux_kolor_prod,
                        'wersja_modelu' => $Adv->aux_wer_mod,
                        'wersja_wyposazenia' => $Adv->aux_wer_wyp,
                        'wersja_silnika' => $Adv->aux_wer_sil,
                        'nr_vin' => $Adv->aux_nr_vin,
                    );

                    $aXmlField = array(
                        'ogloszenie_id' => $Adv->rd() .'_' . $Adv->id_aux,
                        'nowy' => $Adv->aux_nowy,
                        'nadwozie' => $Adv->ndx_nazwa,
                        'kolor' => $this->Lang->get('aux_kolory|' . $Adv->kol_nazwa),
                        'rok_produkcji' => $Adv->aux_rok_prod,
                        'przebieg_w_km' => $Adv->aux_przebieg,
                        'pojemnosc_w_cm3' => $Adv->aux_pojemnosc,
                        'moc_silnika_w_KM' => $Adv->aux_moc_sil,
                        'paliwo' => $this->Lang->get('aux_paliwo|' . $Adv->aux_paliwo),
                        'cena' => $Adv->aux_cena,
                        'waluta' => $Adv->wal_nazwa,
                        'cena_typ' => $Adv->aux_cena_typ,
                        'cena_do_negocjacji' => $this->Lang->get('no_yes|' . $Adv->aux_cena_neg),
                        'faktura' => $this->Lang->get('aux_faktura|' . $Adv->aux_faktura),
                        'kredyt' => array(
                            'mozliwy_kredyt' => $this->Lang->get('no_yes|' . $Adv->aux_kredyt),
                            'rata' => $Adv->aux_rata,
                        ),
                        'leasing' => array(
                            'opcja_leasingu' => $Adv->aux_leasing,
                            'kwota_odstepnego' => $Adv->aux_kwota_odstepnego,
                            'leasing_rata' => $Adv->aux_leasing_rata,
                            'pozostalo_rat' => $Adv->aux_pozostalo_rat,
                            'kwota_wykupu' => $Adv->aux_kwota_wykupu,
                        ),
                        'klimatyzacja' => $this->Lang->get('aux_klima|' . $Adv->aux_klima),
                        'audio' => $this->Lang->get('aux_audio|' . $Adv->aux_audio),
                        'liczba_poduszek_powietrznych' => $Adv->aux_poduszki,
                        'homologacja' => $this->Lang->get('no_yes|' . $Adv->aux_homologacja),
                        'liczba_drzwi' => $Adv->aux_drzwi,
                        'uszkodzony' => $this->Lang->get('no_yes|' . $Adv->aux_uszkodzony),
                        'naped' => $this->Lang->get('auo_naped|' . $Adv->aux_naped),
                        'liczba_miejsc' => $Adv->aux_miejsca,
                        'skrzynia_biegow' => $this->Lang->get('aux_skrzynia|' . $Adv->aux_skrzynia),
                        'pochodzenie' => $this->Lang->get('aux_pochodzenie|' . Common::pochodzenieKonwersja($Adv->aux_kraj_pochodzenia, $Adv->aux_status_sprowadzonego)),
                        'zarejestrowany' => $this->Lang->get('aux_rejestracja|' . Common::rejestracja_konwersja($Adv->aux_kraj_rejestracji, $Adv->aux_status_sprowadzonego)),
                        'liczba_wlascicieli' => $Adv->aux_liczba_wlascicieli,
                        'data_wprowadzenia' => $Adv->aux_data_wpr,
                        'data_modyfikacji' => $Adv->aux_data_mod,
                        'data_modyfikacji_zdjec' => $Adv->aux_data_mod_zdjec,
                        'pelne_odliczenie_vat' => $this->Lang->get('no_yes|' . $Adv->aux_odl_vat),
                        'kraj_pochodzenia' => $Adv->aux_kraj_pochodzenia,
                        'kraj_rejestracji' => $Adv->aux_kraj_rejestracji,
                        'status_sprowadzonego' => $Adv->aux_status_sprowadzonego,
                        'przeglad_wazny_do' => Common::ym_date_fix($Adv->aux_data_przeglad),
                        'ubezpieczenie_wazne_do' => Common::ym_date_fix($Adv->aux_data_ubezp),
                        'pierwsza_rejestracja' => Common::ym_date_fix($Adv->aux_data_rejestracja),
                        'tapicerka' => $Adv->aux_tapicerka,
                        'tapicerka_kolor' => $Adv->aux_tapicerka_kolor,
                    );
                    if(!in_array($Adv->aux_leasing, array('auto_w_leasingu','mozliwy_leasing')))
                    {
                        unset($aXmlField['leasing']);
                    }
                    if('Y' != $Adv->aux_kredyt)
                    {
                        unset($aXmlField['kredyt']);
                    }

                    $aXmlField['kolor_metalik'] = $this->Lang->get('no_yes|N');

                    if ($Adv->aux_kolor_atr & 1)
                    {
                        $aXmlField['kolor_metalik'] = $this->Lang->get('no_yes|Y');
                    }

                    $aXmlField['kolor_perla'] = $this->Lang->get('no_yes|N');

                    if ($Adv->aux_kolor_atr & 2)
                    {
                        $aXmlField['kolor_perla'] = $this->Lang->get('no_yes|Y');
                    }

                    break;

                case 'c':
                case 'a':
                    $aXmlFieldCdata = array(
                        'marka' => $Adv->mrx_nazwa,
                        'model' => $Adv->aux_model,
                        'nr_vin' => $Adv->aux_nr_vin,
                        'kolor_producenta' => $Adv->aux_kolor_prod,
                    );

                    $aXmlField = array(
                        'ogloszenie_id' => $Adv->rd() .'_' . $Adv->id_aux,
                        'nowy' => $Adv->aux_nowy,
                        'rok_produkcji' => $Adv->aux_rok_prod,
                        'przebieg_w_km' => $Adv->aux_przebieg,
                        'pojemnosc_w_cm3' => $Adv->aux_pojemnosc,
                        'moc_silnika_w_KM' => $Adv->aux_moc_sil,
                        'paliwo' => $this->Lang->get('aux_paliwo|' . $Adv->aux_paliwo),
                        'cena' => $Adv->aux_cena,
                        'waluta' => $Adv->wal_nazwa,
                        'cena_typ' => $Adv->aux_cena_typ,
                        'cena_do_negocjacji' => $this->Lang->get('no_yes|' . $Adv->aux_cena_neg),
                        'faktura' => $this->Lang->get('aux_faktura|' . $Adv->aux_faktura),
                        'kredyt' => array(
                            'mozliwy_kredyt' => $this->Lang->get('no_yes|' . $Adv->aux_kredyt),
                            'rata' => $Adv->aux_rata,
                        ),
                        'leasing' => array(
                            'opcja_leasingu' => $Adv->aux_leasing,
                            'kwota_odstepnego' => $Adv->aux_kwota_odstepnego,
                            'leasing_rata' => $Adv->aux_leasing_rata,
                            'pozostalo_rat' => $Adv->aux_pozostalo_rat,
                            'kwota_wykupu' => $Adv->aux_kwota_wykupu,
                        ),
                        'klimatyzacja' => $this->Lang->get('aux_klima|' . $Adv->aux_klima),
                        'audio' => $this->Lang->get('aux_audio|' . $Adv->aux_audio),
                        'liczba_poduszek_powietrznych' => $Adv->aux_poduszki,
                        'przeglad_wazny_do' => Common::ym_date_fix($Adv->aux_data_przeglad),
                        'ubezpieczenie_wazne_do' => Common::ym_date_fix($Adv->aux_data_ubezp),
                        'uszkodzony' => $this->Lang->get('no_yes|' . $Adv->aux_uszkodzony),
                        'liczba_miejsc' => $Adv->aux_miejsca,
                        'skrzynia_biegow' => $this->Lang->get('aux_skrzynia|' . $Adv->aux_skrzynia),
                        'pochodzenie' => $this->Lang->get('aux_pochodzenie|' . Common::pochodzenieKonwersja($Adv->aux_kraj_pochodzenia, $Adv->aux_status_sprowadzonego)),
                        'zarejestrowany' => $this->Lang->get('aux_rejestracja|' . Common::rejestracja_konwersja($Adv->aux_kraj_rejestracji, $Adv->aux_status_sprowadzonego)),
                        'kraj_pochodzenia' => $Adv->aux_kraj_pochodzenia,
                        'kraj_rejestracji' => $Adv->aux_kraj_rejestracji,
                        'status_sprowadzonego' => $Adv->aux_status_sprowadzonego,
                        'pierwsza_rejestracja' => Common::ym_date_fix($Adv->aux_data_rejestracja),
                        'data_wprowadzenia' => $Adv->aux_data_wpr,
                        'data_modyfikacji' => $Adv->aux_data_mod,
                        'data_modyfikacji_zdjec' => $Adv->aux_data_mod_zdjec,
                        'pelne_odliczenie_vat' => $this->Lang->get('no_yes|' . $Adv->aux_odl_vat),
                        'norma_euro' => $Adv->aux_euro_norma,
                        'liczba_wlascicieli' => $Adv->aux_liczba_wlascicieli,
                    );
                    if(!in_array($Adv->aux_leasing, array('auto_w_leasingu','mozliwy_leasing')))
                    {
                        unset($aXmlField['leasing']);
                    }
                    if('Y' != $Adv->aux_kredyt)
                    {
                        unset($aXmlField['kredyt']);
                    }
                    break;

                case 'm':
                    
                    $aXmlFieldCdata = array(
                        'marka' => $Adv->mrx_nazwa,
                        'model' => $Adv->mdx_nazwa,
                        'wersja_modelu' => $Adv->aux_wer_mod,
                    );
                    
                    $aXmlField = array(
                        'ogloszenie_id' => $Adv->rd() .'_' . $Adv->id_aux,
                        'nowy' => $Adv->aux_nowy,
                        'typ_motocykla' => $this->Lang->get('aum_typ_motocykla|' . $Adv->aux_typ),
                        'rok_produkcji' => $Adv->aux_rok_prod,
                        'przebieg_w_km' => $Adv->aux_przebieg,
                        'pojemnosc_w_cm3' => $Adv->aux_pojemnosc,
                        'moc_silnika_w_KM' => $Adv->aux_moc_sil,
                        'paliwo' => $this->Lang->get('aux_paliwo|' . $Adv->aux_paliwo),
                        'cena' => $Adv->aux_cena,
                        'waluta' => $Adv->wal_nazwa,
                        'cena_typ' => $Adv->aux_cena_typ,
                        'cena_do_negocjacji' => $this->Lang->get('no_yes|' . $Adv->aux_cena_neg),
                        'faktura' => $this->Lang->get('aux_faktura|' . $Adv->aux_faktura),
                        'kredyt' => array(
                            'mozliwy_kredyt' => $this->Lang->get('no_yes|' . $Adv->aux_kredyt),
                            'rata' => $Adv->aux_rata,
                        ),
                        'leasing' => array(
                            'opcja_leasingu' => $Adv->aux_leasing,
                            'kwota_odstepnego' => $Adv->aux_kwota_odstepnego,
                            'leasing_rata' => $Adv->aux_leasing_rata,
                            'pozostalo_rat' => $Adv->aux_pozostalo_rat,
                            'kwota_wykupu' => $Adv->aux_kwota_wykupu,
                        ),
                        'audio' => $this->Lang->get('aux_audio|' . $Adv->aux_audio),
                        'przeglad_wazny_do' => Common::ym_date_fix($Adv->aux_data_przeglad),
                        'ubezpieczenie_wazne_do' => Common::ym_date_fix($Adv->aux_data_ubezp),
                        'uszkodzony' => $this->Lang->get('no_yes|' . $Adv->aux_uszkodzony),
                        'naped' => $this->Lang->get('aum_naped|' . $Adv->aux_naped),
                        'liczba_miejsc' => $Adv->aux_miejsca,
                        'skrzynia_biegow' => $this->Lang->get('aum_skrzynia|' . $Adv->aux_skrzynia),
                        'pochodzenie' => $this->Lang->get('aux_pochodzenie|' . Common::pochodzenieKonwersja($Adv->aux_kraj_pochodzenia, $Adv->aux_status_sprowadzonego)),
                        'zarejestrowany' => $this->Lang->get('aux_rejestracja|' . Common::rejestracja_konwersja($Adv->aux_kraj_rejestracji, $Adv->aux_status_sprowadzonego)),
                        'nr_vin' => $Adv->aux_nr_vin,
                        'liczba_wlascicieli' => $Adv->aux_liczba_wlascicieli,
                        'kraj_pochodzenia' => $Adv->aux_kraj_pochodzenia,
                        'kraj_rejestracji' => $Adv->aux_kraj_rejestracji,
                        'status_sprowadzonego' => $Adv->aux_status_sprowadzonego,
                        'pierwsza_rejestracja' => Common::ym_date_fix($Adv->aux_data_rejestracja),
                        'data_wprowadzenia' => $Adv->aux_data_wpr,
                        'data_modyfikacji' => $Adv->aux_data_mod,
                        'data_modyfikacji_zdjec' => $Adv->aux_data_mod_zdjec,
                    );
                    if(!in_array($Adv->aux_leasing, array('auto_w_leasingu','mozliwy_leasing')))
                    {
                        unset($aXmlField['leasing']);
                    }
                    if('Y' != $Adv->aux_kredyt)
                    {
                        unset($aXmlField['kredyt']);
                    }
                    break;
                case 's':
                    
                    $aXmlFieldCdata = array(
                        'kategoria_pojazdu' => $this->Lang->get('aus_kategoria|' . $Adv->kts_nazwa),
                        'podkategoria_pojazdu' => $this->Lang->get('aus_podkategoria|' . $Adv->pks_nazwa),
                        'marka' => $Adv->mrx_nazwa,
                        'model' => $Adv->aux_model,
                        'nr_vin' => $Adv->aux_nr_vin,
                    );
                    
                    $aXmlField = array(
                        'ogloszenie_id' => $Adv->rd() .'_' . $Adv->id_aux,
                        'nowy' => $Adv->aux_nowy,
                        'pojemnosc_w_cm3' => $Adv->aux_pojemnosc,
                        'paliwo' => $this->Lang->get('aux_paliwo|' . $Adv->aux_paliwo),
                        'moc_silnika_w_KM' => $Adv->aux_moc_sil,
                        'data_wprowadzenia' => $Adv->aux_data_wpr,
                        'data_modyfikacji' => $Adv->aux_data_mod,
                        'data_modyfikacji_zdjec' => $Adv->aux_data_mod_zdjec,
                        'audio' => $this->Lang->get('aux_audio|' . $Adv->aux_audio),
                        'faktura' => $this->Lang->get('aux_faktura|' . $Adv->aux_faktura),
                        'kredyt' => array(
                            'mozliwy_kredyt' => $this->Lang->get('no_yes|' . $Adv->aux_kredyt),
                            'rata' => $Adv->aux_rata,
                        ),
                        'leasing' => array(
                            'opcja_leasingu' => $Adv->aux_leasing,
                            'kwota_odstepnego' => $Adv->aux_kwota_odstepnego,
                            'leasing_rata' => $Adv->aux_leasing_rata,
                            'pozostalo_rat' => $Adv->aux_pozostalo_rat,
                            'kwota_wykupu' => $Adv->aux_kwota_wykupu,
                        ),
                        'uszkodzony' => $this->Lang->get('no_yes|' . $Adv->aux_uszkodzony),
                        'naped' => $this->Lang->get('aus_naped|' . $Adv->aux_naped),
                        'rok_produkcji' => $Adv->aux_rok_prod,
                        'przebieg_w_km' => $Adv->aux_przebieg,
                        'waluta' => $Adv->wal_nazwa,
                        'cena' => $Adv->aux_cena,
                        'cena_typ' => $Adv->aux_cena_typ,
                        'cena_do_negocjacji' => $this->Lang->get('no_yes|' . $Adv->aux_cena_neg),
                        'pochodzenie' => $this->Lang->get('aux_pochodzenie|' . Common::pochodzenieKonwersja($Adv->aux_kraj_pochodzenia, $Adv->aux_status_sprowadzonego)),
                        'zarejestrowany' => $this->Lang->get('aux_rejestracja|' . Common::rejestracja_konwersja($Adv->aux_kraj_rejestracji, $Adv->aux_status_sprowadzonego)),
                        'kraj_pochodzenia' => $Adv->aux_kraj_pochodzenia,
                        'kraj_rejestracji' => $Adv->aux_kraj_rejestracji,
                        'status_sprowadzonego' => $Adv->aux_status_sprowadzonego,
                        'liczba_miejsc' => $Adv->aux_miejsca,
                        'skrzynia_biegow' => $this->Lang->get('aus_skrzynia|' . $Adv->aux_skrzynia),
                        'liczba_silnikow' => $Adv->aux_liczba_silnikow,
                        'motogodziny' => $Adv->aux_motogodziny,
                        'dlugosc_w_cm' => $Adv->aux_dlugosc,
                        'szerokosc_w_cm' => $Adv->aux_szerokosc,
                        'liczba_osi' => $Adv->aux_liczba_osi,
                        'liczba_lopat_silnika' => $Adv->aux_liczba_lopat_sil,
                        'wysokosc_podnoszenia_w_cm' => $Adv->aux_wysokosc_podn,
                        'waga_w_tonach' => $Adv->aux_waga,
                        'udzwig_w_kg' => $Adv->aux_udzwig,
                        'motogodziny_do_przegladu' => $Adv->aux_motog_do_przeg,
                        'pojemnosc_lyzki_w_m3' => $Adv->aux_poj_lyz,
                        'masa_eksploatacyjna_w_tonach' => $Adv->aux_masa_eks,
                        'moc_uzyteczna_w_KM' => $Adv->aux_moc_uzy,
                        'szerokosc_gasienic_w_mm' => $Adv->aux_szerok_gas,
                        'glebokosc_kopania_w_m' => $Adv->aux_glebok_kop,
                        'wysiegnik' => $this->Lang->get('aus_wysiegnik|'.$Adv->aus_wysiegnik),
                        'lemiesz' => $this->Lang->get('aus_lemiesz|'.$Adv->aus_lemiesz),
                        'szrokosc_lemiesza_w_m' => $Adv->aux_szer_lem,
                        'moc_agregatu_w_kVA' => $Adv->moc_agregatu,
                    );
                    if(!in_array($Adv->aux_leasing, array('auto_w_leasingu','mozliwy_leasing')))
                    {
                        unset($aXmlField['leasing']);
                    }
                    if('Y' != $Adv->aux_kredyt)
                    {
                        unset($aXmlField['kredyt']);
                    }
                    break;
                case 'p':
                    
                    $aXmlFieldCdata = array(
                        'kategoria_pojazdu' => $this->Lang->get('aup_kategoria|' . $Adv->id_ktp),
                        'podkategoria_pojazdu' => $this->Lang->get('aup_podkategoria|' . $Adv->id_pkp),
                        'marka' => $Adv->mrx_nazwa,
                        'model' => $Adv->mdx_nazwa,
                        'nr_vin' => $Adv->aux_nr_vin,
                        'numer_oryginalny' => $Adv->aux_numer_oryginalny,
                        'numer_firma' => $Adv->aux_numer_firma,
                        'numer_katalogowy' => $Adv->aux_numer_katalog,
                        
                    );      
                    
                    $aXmlField = array(
                        'ogloszenie_id' => $Adv->rd() .'_' . $Adv->id_aux,
                        'nazwa_czesci' => $Adv->aux_nazwa_tytul,
                        'pojemnosc_w_cm3' => $Adv->aux_pojemnosc,
                        'nadwozie' => $Adv->aux_nadwozie,
                        'paliwo' => $this->Lang->get('aux_paliwo|' . $Adv->aux_paliwo),
                        'wersja_silnika' => $Adv->aux_wer_sil,
                        'moc_silnika_w_KM' => $Adv->aux_moc_sil,
                        'cena' => $Adv->aux_cena,
                        'waluta' => $Adv->wal_nazwa,
                        'cena_typ' => $Adv->aux_cena_typ,
                        'cena_do_negocjacji' => $this->Lang->get('no_yes|' . $Adv->aux_cena_neg),
                        'faktura' => $this->Lang->get('aux_faktura|' . $Adv->aux_faktura),
                        'rok_produkcji' => $Adv->aux_rok_prod,
                        'czy_czesc_nowa' => $this->Lang->get('no_yes|' . $Adv->aux_nowa),
                        'skrzynia_biegow' => $this->Lang->get('aux_skrzynia|' . $Adv->aux_skrzynia),
                        'liczba_drzwi' => $Adv->aux_drzwi,
                        'data_wprowadzenia' => $Adv->aux_data_wpr,
                        'data_modyfikacji' => $Adv->aux_data_mod,
                        'data_modyfikacji_zdjec' => $Adv->aux_data_mod_zdjec,
                        'uszkodzony' => $this->Lang->get('no_yes|' . $Adv->aux_uszkodzony),
                        'rocznik_auta_od' => $Adv->aux_rocznikod,
                        'rocznik_auta_do' => $Adv->aux_rocznikdo,
                        'rodzaj_czesci' => $Adv->aux_oryginal,
                        'rodzaj_pojazdu' => $Adv->aux_rodzaj,
                    );
                    break;
                default:
                    break;
            }

            if ('d' == $Adv->rd())
            {
                $aXmlField['ladownosc_w_kg'] = $Adv->aux_ladownosc;
                $aXmlField['wymiary_ladunkowe_w_metrach'] = $Adv->aux_wymiary_ladunkowe;
            }

            if ('c' == $Adv->rd())
            {
                $aXmlField['ladownosc_w_kg'] = $Adv->aux_ladownosc;
                $aXmlField['masa_calkowita_w_kg'] = $Adv->aux_masa_calkowita;
                $aXmlField['liczba_osi'] = $Adv->aux_liczba_osi;
                $aXmlField['wymiary_ladunkowe_w_metrach'] = $Adv->aux_wymiary_ladunkowe;
                $aXmlField['naped'] = $this->Lang->get('auc_naped|' . $Adv->aux_naped);
                $aXmlFieldCdata['nadwozie'] = $Adv->ndx_nazwa;
            }

            if ('a' == $Adv->rd())
            {
                $aXmlField['typ_autobusu'] = $Adv->aux_typ;
                $aXmlField['zawieszenie'] = $Adv->aux_zawieszenie;
            }

            if ($Adv->rd() != 'p')
            {
                $aXmlField['paliwo_e10'] = $this->Lang->get('no_yes|N');
                $aXmlField['biodiesel'] = $this->Lang->get('no_yes|N');
                $aXmlField['olej_roslinny'] = $this->Lang->get('no_yes|N');

                if ($Adv->aux_paliwo_atr & 1)
                {
                    $aXmlField['paliwo_e10'] = $this->Lang->get('no_yes|Y');
                }

                if ($Adv->aux_paliwo_atr & 2)
                {
                    $aXmlField['biodiesel'] = $this->Lang->get('no_yes|Y');
                }

                if ($Adv->aux_paliwo_atr & 4)
                {
                    $aXmlField['olej_roslinny'] = $this->Lang->get('no_yes|Y');
                }
            }

            $aXmlFieldCdata['opis'] = '';

            if ('N' == $Adv->aux_ignoruj_opis_dod)
            {
                $aXmlFieldCdata['opis'] =
                        $Adv->opx_opis . '
                ' . $Adv->opx_dodatkowy;
            }
            

            $sProgram = '';

            if (isset($this->aProgram[$Adv->id_pde]))
            {
                $sProgram = "\n" . $this->aProgram[$Adv->id_pde]['pde_tekst'] . "\n";
                $aXmlField['program_dealerski'] = $this->aProgram[$Adv->id_pde]['pde_typ'];
            }
            
            if($Adv->aux_video_link)
            {
                $sVideoLink = "\n".'Zobacz film dla tego ogłoszenia: http://'.$Adv->aux_video_link."\n";
            }
            else
            {
                $sVideoLink = '';
            }
            
            $sZnacznik_ogloszenia_do_wyszukiwania = "\n" . 'i'.$Adv->id_aui.'i' . "\n";

            $aXmlFieldCdata['opis'] = $this->aEksport['eks_opis_naglowek'] . "\n" .
                    $aXmlFieldCdata['opis'] . "\n" .
                    $sProgram .
                    $this->pobierzCertyfikatPojazdu($Adv->id_aux, array('html' => true)) .
                    $sZnacznik_ogloszenia_do_wyszukiwania .
                    $this->aEksport['eks_opis_stopka'];

            if($this->aEksport['prt_nazwa'] == 'anonse' && '' == trim($aXmlFieldCdata['opis']))
            {
                $aXmlFieldCdata['opis'] = '.';
            }
            
            $aXmlFieldCdata['video_link'] = $Adv->aux_video_link;
            
            $this->_array2xml($xmlOgloszenie, $aXmlField, 'createTextNode');
            $this->_array2xml($xmlOgloszenie, $aXmlFieldCdata, 'createCDATASection');

            if ('p' != $Adv->rd())
            {
                $xmlSpalanie = $xmlOgloszenie->appendChild(new DOMElement('spalanie'));

                $aSpalanie = array(
                    'dokladnosc' => $Adv->aux_spal_dokladnosc,
                    'cykl_mieszany' => $Adv->aux_spal_cykl_mieszany,
                    'cykl_miejski' => $Adv->aux_spal_cykl_miejski,
                    'cykl_pozamiejski' => $Adv->aux_spal_cykl_pozamiejski,
                    'spalanie_srednie' => $Adv->aux_spal_zuzycie_paliwa,
                    'emisja_co2' => $Adv->aux_emisja_co2,
                );

                $this->_array2xml($xmlSpalanie, $aSpalanie, 'createTextNode');
            }

            $aTmpWypo = array();
            
            switch ($Adv->rd())
            {
                case 'o':
                case 'd':
                case 'c':
                case 'm':
                case 'a':
                    
                    $aTmpWypo[] = Common::dec2array($this->Lang->get('auo_wypo1_dv'), $Adv->aux_wypo1);
                    $aTmpWypo[] = Common::dec2array($this->Lang->get('auo_wypo3_dv'), $Adv->aux_wypo3);
                    
                    break;
                case 's':
                    
                    switch ($Adv->id_kts)
                    {
                        case 1:
                            $aTmpWypo[] = Common::dec2array($this->Lang->get('aus_wypo_specjalne_dv'), $Adv->aus_wypo_specjalne);
                            $aTmpWypo[] = array();
                            break;
                        case 2:
                            $aTmpWypo[] = Common::dec2array($this->Lang->get('aus_wypo_naczepa_dv'), $Adv->aus_wypo_naczepa);
                            $aTmpWypo[] = array();
                            break;
                        case 3:
                            $aTmpWypo[] = Common::dec2array($this->Lang->get('aus_wypo_budowlane_dv'), $Adv->aus_wypo_budowlane);
                            $aTmpWypo[] = array();
                            break;
                        case 4:
                            $aTmpWypo[] = Common::dec2array($this->Lang->get('aus_wypo_rolnicze_dv'), $Adv->aus_wypo_rolnicze);
                            $aTmpWypo[] = array();
                            break;
                        case 5:
                            $aTmpWypo[] = Common::dec2array($this->Lang->get('aus_wypo_kemping_dv'), $Adv->aus_wypo_kemping);
                            $aTmpWypo[] = array();
                            break;
                        case 6:
                            $aTmpWypo[] = Common::dec2array($this->Lang->get('aus_wypo_widlak_dv'), $Adv->aus_wypo_widlak);
                            $aTmpWypo[] = array();
                            break;
                        case 7:
                            $aTmpWypo[] = Common::dec2array($this->Lang->get('aus_wypo_wodne_dv'), $Adv->aus_wypo_wodne);
                            $aTmpWypo[] = array();
                            break;
                        case 8:
                            $aTmpWypo[] = Common::dec2array($this->Lang->get('aus_wypo_latajace_dv'), $Adv->aus_wypo_latajace);
                            $aTmpWypo[] = array();
                            break;
                        case 9:
                            $aTmpWypo[] = Common::dec2array($this->Lang->get('aus_wypo_naczepa_dv'), $Adv->aus_wypo_naczepa);
                            $aTmpWypo[] = array();
                            break;
                        case 10:
                        case 11:
                        case 12:
                        case 13:
                        case 14:
                        case 15:
                        case 16:
                        case 17:
                        case 18:
                        case 19:
                        case 20:
                        case 21:
                        case 22:
                            $aTmpWypo[] = Common::dec2array($this->Lang->get('aus_wypo_budowlane_dv'), $Adv->aus_wypo_budowlane);
                            $aTmpWypo[] = array();
                            break;
                    }                    
            }
            
            if('p' != $Adv->rd())
            {
                $aWypoAll = $aTmpWypo[0] + $aTmpWypo[1];

                $xmlWypo = $xmlOgloszenie->appendChild(new DOMElement('wyposazenie'));

                foreach ($aWypoAll as $k => $v)
                {
                    $xmlPole = $xmlWypo->appendChild(new DOMElement('pozycja'));
                    $xmlPole->appendChild($this->XML->createCDATASection($v));
                }
            }

            $aTmpInfo = Common::dec2array($this->Lang->get('au'.$Adv->rd().'_wypo2_dv'), $Adv->aux_wypo2);

            $xmlInfo = $xmlOgloszenie->appendChild(new DOMElement('informacje_dodatkowe'));

            foreach ($aTmpInfo as $k => $v)
            {
                $xmlPole = $xmlInfo->appendChild(new DOMElement('pozycja'));
                $xmlPole->appendChild($this->XML->createCDATASection($v));
            }

            $xmlZdjecia = $xmlOgloszenie->appendChild(new DOMElement('zdjecia'));

            $aFoto = array();

            if ('Y' == $this->aEksport['eks_xml_only'])
            {
                $aFoto = Grafika::imgHttpNameAsArray($Adv->id_aux, $Adv->rd(), $Adv->aux_zdjecia_wektor, 'o');
            }
            else
            {
                $aFoto = Grafika::imgRootNameAsArray($Adv->id_aux, $Adv->rd(), $Adv->aux_zdjecia_wektor, 'o');
            }
            
            foreach ($aFoto as $v)
            {
                $xmlZdjecie = $xmlZdjecia->appendChild(new DOMElement('zdjecie'));

                if ('Y' == $this->aEksport['eks_xml_only'])
                {
                    $xmlZdjecie->appendChild($this->XML->createCDATASection($v['o']));
                    $this->aFiles[$this->iPaczka][] = array(
                        'sourceName' => $v['o'],
                        'destinationName' => $Adv->rd().'_'.basename($v['o'])
                    );
                }
                else
                {
                    $sImgXmlName = $Adv->rd().'_'.strtotime($Adv->aux_data_mod_zdjec).'_'.basename($v['o']);
                    $this->aFiles[$this->iPaczka][] = array(
                        'sourceName' => $v['o'],
                        'destinationName' => $sImgXmlName
                    );
                    $xmlZdjecie->appendChild($this->XML->createCDATASection($sImgXmlName));
                }
                
            }
                
            if ('Y' == $this->aEksport['eks_dane_kontaktowe'])
            {

                $xmlKontakt = $xmlOgloszenie->appendChild(new DOMElement('dane_kontaktowe'));

                $aKontakt = array(
                    'kraj' => $Adv->kra_nazwa,
                    'region' => $this->Lang->get('komis_region|' . $Adv->reg_nazwa),
                    'miasto' => $Adv->aad_miasto,
                    'kod_pocztowy' => $Adv->aad_kod_pocztowy,
                    'telefon' => $Adv->aad_telefony,
                    'email' => $Adv->aad_email,
                    'osoba' => $Adv->aad_imie,
                    'gg' => $Adv->aad_gg,
                    'tlen' => $Adv->aad_tlen,
                    'skype' => $Adv->aad_skype,
                );

                $this->_array2xml($xmlKontakt, $aKontakt, 'createCDATASection');
            }            
            
            $this->aAuiAdded[$paczka][$Adv->id_aui] = array(
                'aui_eksport' => 'N',
                'aui_data_ost_exp' => Common::now()
            );
        }

        /*
         * Usuwanie ogloszen
         */
        
        $xmlOgloszeniaUsun = $XML->appendChild(new DOMElement('usun'));

        $aXmlRdUsun = array(
            'o' => $xmlOgloszeniaUsun->appendChild(new DOMElement('osobowe')),
            'd' => $xmlOgloszeniaUsun->appendChild(new DOMElement('dostawcze')),
            'c' => $xmlOgloszeniaUsun->appendChild(new DOMElement('ciezarowe')),
            'a' => $xmlOgloszeniaUsun->appendChild(new DOMElement('autobusy')),
            'm' => $xmlOgloszeniaUsun->appendChild(new DOMElement('motocykle')),
            's' => $xmlOgloszeniaUsun->appendChild(new DOMElement('specjalne')),
            'p' => $xmlOgloszeniaUsun->appendChild(new DOMElement('czesci')),
        );
        
        foreach ($this->aListaUsun as $k => $Adv)
        {
            $xmlOgloszenieUsun = $aXmlRdUsun[$Adv->rd()]->appendChild(new DOMElement('ogloszenie_id'));
            $xmlOgloszenieUsun->appendChild($this->XML->createTextNode($Adv->rd(). '_' . $Adv->id()));
            
            $this->aAuiRemoved[$paczka][$Adv->id_aui] = array(
                'id_aui' => $Adv->id_aui,
                'aui_s' => 'Y',
            );
            
            unset($this->aListaUsun[$k]);
        }
        
        $this->XML->formatOutput = true;
        return $this->XML->saveXML();
    }

    protected function _komis()
    {
        return array(
            'komis_id' => 'AKoL_' . $this->aEksport['id_kli'],
            'komis_klucz' => $this->aEksport['eks_klucz_komisu'],
            'kraj' => $this->aEksport['kra_nazwa'],
            'region' => $this->Lang->get('komis_region|' . $this->aEksport['reg_nazwa']),
            'email' => $this->aEksport['kli_email'],
            'firma' => $this->aEksport['kli_firma'],
            'kod_pocztowy' => $this->aEksport['kli_kod_pocztowy'],
            'miasto' => $this->aEksport['kli_miasto'],
            'ulica' => $this->aEksport['kli_ulica'],
            'telefon' => $this->aEksport['kli_telefon_kom'] . ', ' . $this->aEksport['kli_telefon_stac'],
        );
    }

    protected function _array2xml(&$node, $aXmlFields, $function)
    {
        foreach ($aXmlFields as $k => $v)
        {
            if(is_array($v))
            {
                $xmlPole = $node->appendChild(new DOMElement($k));
                $this->_array2xml($xmlPole, $v, 'createTextNode');
            }
            else
            {
                $xmlPole = $node->appendChild(new DOMElement($k));
                $xmlPole->appendChild($this->XML->$function($v));
            }  
        }
    }
    
}
