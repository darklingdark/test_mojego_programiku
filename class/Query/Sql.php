<?php
class Query_Sql
{
    public static function paczkaFullInfo($iPaczkaId=null)
    {
        $aSelect = [
            'select' => [
                'psp' => [
                    'id_psp',
                    'psp_rok',
                    'psp_typ_okresu',
                    'psp_okres',
                    'psp_data_wpr',
                ],
                'plc' => [
                    'id_plc',
                    'id_slj',
                    'plc_nazwa',
                    'plc_typ',
                    'plc_regon',
                    'plc_wk',
                    'plc_pk',
                    'plc_gk',
                    'plc_gt',
                    'plc_pt',
                    'plc_data_dodania',
                ],
                'spr' => [
                    'id_spr',
                    'spr_data_wpr',
                    'spr_wersja',
                    'spr_nazwa',
                    'spr_data_sprawozdania',
                ],
                'slj' => [
                    'slj_nazwa',
                    'slj_miasto',
                    'slj_adres',
                    'slj_skrot',
                    'slj_nip',
                    'slj_urzad_skarb',
                    'slj_rodz_pod',
                ],
            ],
            'from' => [
                ['paczka_sprawozdan', 'psp'],
                ['placowka', 'plc'],
                ['sprawozdanie', 'spr'],
                ['slownik_jednostek', 'slj'],
            ],
            'join' => [
                ['plc.id_psp', 'psp.id_psp'],
                ['spr.id_plc', 'plc.id_plc'],
                ['slj.id_slj', 'plc.id_slj'],
            ],
            'where' => [
                ['psp.psp_s', '=', 'N'],
            ]
        ];
        
        if(!is_null($iPaczkaId))
        {
            $aSelect['where'][] = ['psp.id_psp', '=', $iPaczkaId];
        }
        
        return $aSelect;
    }
    
    /**
     * Zapytanie o liste placowek na liscie
     * 
     * @return string
     */
    public static function paczkaList()
    {
        $aSelect = [
            'select' => [
                'psp.id_psp as id',
                'concat(psp_rok, " ", psp.psp_typ_okresu, " ", psp.psp_okres) as "Okres"',
                'slj.slj_nazwa as "Placówka"',
                'plc.plc_regon "Regon"',
                'spr.spr_nazwa as  "Sprawozdanie"',
                'plc.plc_typ as "Typ"',
                'spr.spr_data_sprawozdania as "Data sprawozdania"',
                'psp.psp_data_wpr as  "Wprowadzone"',
            ],
            'from' => [
                ['paczka_sprawozdan', 'psp'],
                ['placowka', 'plc'],
                ['sprawozdanie', 'spr'],
                ['slownik_jednostek', 'slj'],
            ],
            'join' => [
                ['plc.id_psp', 'psp.id_psp'],
                ['spr.id_plc', 'plc.id_plc'],
                ['slj.id_slj', 'plc.id_slj'],
            ],
            'where' => [
                ['psp.psp_s', '=', 'N'],
            ]
        ];
        
        return $aSelect;
    }
    
    /**
     * lista placowek z mapowania
     * 
     * @return string
     */
    public static function placowkiList()
    {
        $aSelect = [
            'select' => [
                'id_slj',
                'slj_regon',
                'slj_miasto',
                'slj_nazwa',
                'slj_adres',
                'slj_skrot',
                'slj_nip',
                'slj_urzad_skarb',
                'slj_rodz_pod',
                'slj_data_dodania',
            ],
            'from' => [
                ['slownik_jednostek', 'slj'],
            ],
            'where' => [
                ['slj.slj_s', '=', 'N'],
            ]
        ];
        
        return $aSelect;
    }

}