<?php

class Verify_List
{
    public static function filtruj($QueryBuilder)
    {
        $aFiltered = [
            'idr' => '',
            'rok' => '',
            'mies' => '',
            'plc' => '',
            'dspr' => '',
            'dwpr' => '',
            'spra' => '',
        ];

        foreach ($aFiltered as $sGet => $aParams)
        {
            $aFiltered[$sGet] = self::$sGet($QueryBuilder);
        }
        
        $aFiltered['sort'] = !empty($_GET['sort'])?$_GET['sort']:'dwm';
        $aFiltered['limit'] = !empty($_GET['limit'])?$_GET['limit']:0;
        $aFiltered['nas'] = !empty($_GET['nas'])?$_GET['nas']:2;
        
        return $aFiltered;
    }

    protected static function idr(&$QueryBuilder)
    {
        if(isset($_GET['idr']) && is_numeric($_GET['idr']))
        {
            $aWhere = ['psp.id_psp', '=', (int)$_GET['idr']];
            $QueryBuilder->addWhere($aWhere);
            return $_GET['idr'];
        }
        elseif(isset($_GET['idr']) && '' != $_GET['idr'] && false !== strstr($_GET['idr'], ','))
        {
            $aWhere = ['psp.id_psp', 'IN', explode(',', $_GET['idr'])];
            $QueryBuilder->addWhere($aWhere);
            return $_GET['idr'];
        }
        return '';
    }
    
    protected static function rok(&$QueryBuilder)
    {
        if(isset($_GET['rok']) && is_numeric($_GET['rok']))
        {
            $aWhere = ['psp.psp_rok', '=', (int)$_GET['rok']];
            $QueryBuilder->addWhere($aWhere);
            return $_GET['rok'];
        }
        return '';
    }
    
    protected static function mies(&$QueryBuilder)
    {
        if(isset($_GET['mies']) && is_numeric($_GET['mies']))
        {
            $aWhere = ['psp.psp_okres', '=', (int)$_GET['mies']];
            $QueryBuilder->addWhere($aWhere);
            return $_GET['mies'];
        }
        elseif(isset($_GET['mies']) && '' != $_GET['mies'] && false !== strstr($_GET['mies'], ','))
        {
            $aWhere = ['psp.psp_okres', 'IN', explode(',', $_GET['mies'])];
            $QueryBuilder->addWhere($aWhere);
            return $_GET['mies'];
        }
        return '';
    }
    
    protected static function plc(&$QueryBuilder)
    {
        if(isset($_GET['plc']) && is_numeric($_GET['plc']))
        {
            $aWhere = ['slj.id_slj', '=', (int)$_GET['plc']];
            $QueryBuilder->addWhere($aWhere);
            return $_GET['plc'];
        }
        return '';
    }
    
    protected static function dspr(&$QueryBuilder)
    {
        if(isset($_GET['dspr']) &&  '' != $_GET['dspr'])
        {
            $aWhere = ['spr.spr_data_sprawozdania', '=', $_GET['dspr']];
            $QueryBuilder->addWhere($aWhere);
            return $_GET['dspr'];
        }
        return '';
    }
    
    protected static function dwpr(&$QueryBuilder)
    {
        if(isset($_GET['dwpr']) &&  '' != $_GET['dwpr'])
        {
            $aWhere1 = ['psp.psp_data_wpr', '>=', date('Y-m-d', strtotime($_GET['dwpr'])) . ' 0:00:01' ];
            $QueryBuilder->addWhere($aWhere1);
            
            $aWhere2 = ['psp.psp_data_wpr', '<=', date('Y-m-d', strtotime($_GET['dwpr'])) . ' 23:59:59'];
            $QueryBuilder->addWhere($aWhere2);
            return $_GET['dwpr'];
        }
        return '';
    }
    
    protected static function spra(&$QueryBuilder)
    {
        if(isset($_GET['spra']) && '' != $_GET['spra'])
        {
            $aWhere = ['spr.spr_nazwa', 'LIKE', $_GET['spra']];
            $QueryBuilder->addWhere($aWhere);
            return $_GET['spra'];
        }
        return '';
    }
    
    public static function sortowanieList()
    {
        return [
            'dwr' => 'Data Wprowadzenia Rosnąco',
            'dwm' => 'Data Wprowadzenia Malejąco',
            'plr' => 'Placówki Rosnąco',
            'plm' => 'Placówki Malejąco',
            'plr' => 'Okres Rosnąco',
            'plm' => 'Okres Malejąco',
        ];
    }
    
    public static function setOrderFromSortowanieList($sOrder)
    {
        $aOrders =  [
            'dwr' => ['psp.psp_data_wpr ASC'],
            'dwm' => ['psp.psp_data_wpr DESC'],
            'plr' => ['slj.slj_nazwa ASC'],
            'plm' => ['slj.slj_nazwa DESC'],
            'plr' => ['psp.psp_rok ASC', 'psp.psp_okres ASC'],
            'plm' => ['psp.psp_rok DESC', 'psp.psp_okres DESC'],
        ];
        
        return isset($aOrders[$sOrder])?$aOrders[$sOrder]:$aOrders['dwm'];
    }
}