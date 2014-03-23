<?php
/**
 * Zapytania do tablicy budynek_na_planecie
 * @uses
 * m_BudynekNaPlanecie,
 * m_Planeta
 */
class DB_SQL_BudynekNaPlanecie
{
    static function budynki($iid_plt)
    {
        $objBNP = m_BudynekNaPlanecie::factory();        
        $objm_Planeta = m_Planeta::factory();
        return array(
            'select' => array(
                $objBNP->_prefix => array(
                    $objBNP->id_bnp['name'],
                    $objBNP->id_plt['name'],
                    $objBNP->id_bnk['name'],
                    $objBNP->bnp_proc_bud['name'],
                    $objBNP->bnp_data_wpr['name'],
                    $objBNP->bnp_s['name'],
                    $objBNP->bnp_cell_x['name'],
                    $objBNP->bnp_cell_y['name'],
                ),
            ),
            'from' => array(
                array(
                    $objBNP->_table,
                    $objBNP->_prefix,
                ),
                array(
                    $objm_Planeta->_table,
                    $objm_Planeta->_prefix,                    
                ),
            ),
            'join' => array(
                array(
                    $objBNP->_prefix.'.'.$objBNP->id_plt['name'],
                    $objm_Planeta->_prefix.'.'.$objm_Planeta->id_plt['name'],
                    
                ),
            ),
            'where' => array(
                array(
                    $objm_Planeta->_prefix.'.'.$objm_Planeta->id_plt['name'],
                    '=',
                    $iid_plt,
                ),
                array(
                    $objBNP->_prefix.'.'.$objBNP->bnp_s['name'],
                    '=',
                    'N',
                ),
            )
        );
    }
}