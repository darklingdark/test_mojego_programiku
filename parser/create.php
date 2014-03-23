<?php


if(!defined('CFG_ROOT'))
{
    include '../config.inc.php';
}

$aWynik = array(
    'status' => false,
);

if (!isset($_GET["id_paczki"]))
{
    $aWynik = array(
        'status' => false,
        'error' => 'brak wymaganych danych',
    );

    echo json_encode($aWynik);
    exit;
}

$iPaczkaId = $_GET["id_paczki"];

try
{
    $DB = DB_Database::instance();
} 
catch (Exception $Exc)
{
    $aWynik = array(
        'status' => false,
        'error' => $Exc->getMessage(),
    );

    echo json_encode($aWynik);
    exit;
}


$CreateZSP = new createZSP($DB);

$sXmlContent = $CreateZSP->przygotujXml($iPaczkaId);

$handle = fopen(CFG_ROOT.'/generated/xml_test_'.  date('Ymd_His').'.xml', 'w+');
fwrite($handle, $sXmlContent);
fclose($handle);