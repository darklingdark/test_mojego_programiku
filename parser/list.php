<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if(!defined('CFG_ROOT'))
{
    include '../config.inc.php';
}
try
{
    $DB = DB_Database::instance();
} 
catch (Exception $Exc)
{
    include CFG_ROOT . '/tpl/parser/error.inc.php';
    exit;
}

$sDZIAL = 'lista';

$QueryBuilder = DB_Query_Builder::factory(
        DB_Query_Builder::type_mysql, 
        Query_Sql::paczkaList()
);

$aFiltered = Verify_List::filtruj($QueryBuilder);

$bSearch = isset($_GET['search']);

$QueryBuilder->setLimit([$aFiltered['limit'], $aFiltered['nas']]);
$QueryBuilder->setOrder(Verify_List::setOrderFromSortowanieList($aFiltered['sort']));

try
{
    $rResult = $DB->query(DB_Database::SELECT, $QueryBuilder->query());
}
catch (Exception $ex)
{
    throw New Exception('L:' . basename(__FILE__) . '(' 
            . __LINE__ . '): Błąd dodawania paczki sprawozdan do bazy danych: ' 
            . $ex->getMessage(), $ex->getCode());
}

$iCountAll = $DB->count_found_rows();
$aPaczki = $DB->fetchAll($rResult, ['key' => 'id']);

$QueryResults = new Query_Result($DB);

$aPlacowki = $QueryResults->placowkiList();
$aSprawozdania = $QueryResults->typSprawozdaniaList();
$aRok = $QueryResults->rokList();

$aUrlParams = [];
foreach($aFiltered as $sGet => $sValue)
{
    $aUrlParams[] = $sGet . '=' . urlencode($sValue);
}
$sUrlParams = implode('&amp;', $aUrlParams);

/*
 * Pobranie listy placowek
 */

include CFG_ROOT . '/tpl/parser/list.inc.php';