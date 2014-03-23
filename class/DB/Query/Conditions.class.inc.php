<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Conditions
 *
 * @author selas
 */
class DB_Query_Conditions
{
    /**
     * 
     * @param type $aOtrzymane - parametry otrzymane - kluczem jest nazwa kolumny bazy danych
     * @param type $aModele - tablica tablic modeli
     * @param type $aAliasy - tablica aliasów (odwzorowujaca ktorej kluczami sa nazwy tablic bazy danych, a wartosciami aliasy tablic)
     */
    static public function getSearchConditions($aOtrzymane, $aModele, $aAliasy)
    {
        $aSearch_cn_tmp = array();
//        echo Debug::vars($aOtrzymane, 'otrzymane');
//        echo Debug::vars($aModele,'modele');
//        echo Debug::vars($aAliasy, 'aliasy');
        
        foreach ($aAliasy as $sNazwa_tablicy => $sAlias_tablicy)
        {
            $aTmp = self::search_conditions($aOtrzymane, $aModele[$sNazwa_tablicy], $sAlias_tablicy);
//            echo Debug::vars($aTmp, '$aTmp');
            $aSearch_conditions = array_merge($aSearch_cn_tmp,$aTmp);
            $aSearch_cn_tmp = $aSearch_conditions;
                    
        }
        return($aSearch_conditions);
            
    }
    
    
/**
 * Weryfikacja zmiennych.
 *
 *
 * @param array  $o - parametry otrzymane - kluczem jest nazwa kolumny bazy danych
 * @param array  $s - parametry spodziewane (tablica w formacie jak w plikach modelu, 
 *  np: array( 'par_kol' => 'n_woj', 'Type' => 'char','Values' => '30');)
 * @param string - alias tablicy bazy danych
 *  
 * @return array - tablica zawieraj�ca warunki wyszukiwania.
 */
 static public function search_conditions($o, $s, $tab_alias) {

    $aWarunki_wyszukiwania = array();
    if(is_array($s) && (sizeof($s)>0)) {
        foreach ( $s as $k =>$v ) {

            if( isset( $o[ $v['par_kol'] ]  ) && ( $o[ $v['par_kol'] ] != ''  ) ) {
                $aWarunki_wyszukiwania[] = array($tab_alias.'.'.$v['par_kol'],"=",$o[ $v['par_kol'] ]);
            }

            if( isset( $o[ $v['par_kol'].'_od' ]  ) && ( $o[ $v['par_kol'].'_od' ] != ''  ) ) {
                $aWarunki_wyszukiwania[] = array($tab_alias.'.'.$v['par_kol'],">=", $o[ $v['par_kol'].'_od']);
            }

            if( isset( $o[ $v['par_kol'].'_do' ]  ) && ( $o[ $v['par_kol'].'_do' ] != ''  ) ) {
                $aWarunki_wyszukiwania[] = array($tab_alias.'.'.$v['par_kol'],"<=", $o[ $v['par_kol'].'_do' ]);
            }

            if( isset( $o[ $v['par_kol'].'_in' ]  ) && ( $o[ $v['par_kol'].'_in' ] != ''  ) ) { //szukane wartosci oddzielone przecinkami lub lec�cych w postaci tablicy!
                $aTmp = explode(',',$o[ $v['par_kol'].'_in']);
                $aWarunki_wyszukiwania[] = array($tab_alias.'.'.$v['par_kol'],"IN", $aTmp);
            }

            if( isset( $o[ $v['par_kol'].'_li' ]  ) && ( $o[ $v['par_kol'].'_li' ] != ''  ) ) {
                $aWarunki_wyszukiwania[] = array($tab_alias.'.'.$v['par_kol'],"LIKE", $o[ $v['par_kol'].'_li' ]);
            }

            //dla kolumn typu SET (np. dla wyposazenia samochodow na www.auto.pl )
            if( isset( $o[ $v['par_kol'].'_bl' ]  ) &&
                ( $o[ $v['par_kol'].'_bl' ] != ''  ) &&
                ( $o[ $v['par_kol'].'_bl' ] != 0  )
            ) {
                //$aWarunki_wyszukiwania[] = array('('.$tab_alias.'.'.$v['par_kol'].' & '.$o[ $v['par_kol'].'_bl' ].')','=',$o[ $v['par_kol'].'_bl' ]);
                $aWarunki_wyszukiwania[] = array($tab_alias.'.'.$v['par_kol'],'BINARY_LIKE',$o[ $v['par_kol'].'_bl' ]);
            }

            //dla kolumn typu SET (np. zeby moc znalezc ogloszenia ze zdjeciem)
            if( isset( $o[ $v['par_kol'].'_sod' ]  ) && ( $o[ $v['par_kol'].'_sod' ] != ''  ) ) {
                //$aWarunki_wyszukiwania[] = "(".$tab_alias.'.'.$v['par_kol']." +0) >= '". $o[ $v['par_kol'].'_sod']."'";
                $aWarunki_wyszukiwania[] = array($tab_alias.'.'.$v['par_kol'],"SOD",$o[ $v['par_kol'].'_sod']);
            }

            if( isset( $o[ $v['par_kol'].'_sdo' ]  ) && ( $o[ $v['par_kol'].'_sdo' ] != ''  ) ) {
                //$aWarunki_wyszukiwania[] = "(".$tab_alias.'.'.$v['par_kol']." +0) <= '". $o[ $v['par_kol'].'_sdo' ]."'";
                $aWarunki_wyszukiwania[] = array($tab_alias.'.'.$v['par_kol'],"SDO",$o[ $v['par_kol'].'_sdo']);
            }
        }
    }


    return $aWarunki_wyszukiwania;
}

}
