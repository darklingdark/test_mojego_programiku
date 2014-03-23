<?php
/*
 * Skrypt przygotowuje XML-a i wysyła do przeglądarki
 */

if(!defined('CFG_ROOT'))
{
    include '../config.inc.php';
}


if (!isset($_GET["id"]))
{
    $aWynik = array(
        'status' => false,
        'error' => 'brak wymaganych danych',
    );

    echo json_encode($aWynik);
    exit;
}

$iPaczkaId = $_GET["id"];

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

if(FALSE === $sXmlContent)
{
    $aErrorList[] = 'Nie udało się przygotować XML-a.';
    $aErrorList[] = 'Prawdopodobnie Występują problemy z pobraniem danych z bazy.';
    
    include CFG_ROOT . '/parser/list.php';
    exit;
}

$sFileName = 'xml_ZSP_nr_paczki_'.$iPaczkaId.'.xml';
$sFilePath = CFG_ROOT_XML . '/'.$sFileName;

$handle = fopen($sFilePath, 'w+');
fwrite($handle, $sXmlContent);
fclose($handle);

header("Content-Type: application/xml");
header("Content-Disposition: attachment; filename=\"".$sFileName."\"");
header("Content-Length: " . filesize($sFilePath));
header("Pragma: no-cache");
header("Expires: 0");

readfile($sFilePath);

exit;