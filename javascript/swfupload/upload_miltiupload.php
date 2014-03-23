<?php
/*
 * zapisywanie zdjec przeslanych metoda multiuploadu w stukturze kalatlogow 
 * przeznaczonych tylko i wylacznie dla multiuploadu (images/multiupload_temp_files/)
 * i zapisanie informacji w bazie danych.
 * 
 * jako wynik zwracamy infomacje:
 * -id zdjecia w bazie danych
 * -sciezka do miniatury
 * -infomacje o bledach (true/false)
 * -ewentualny komunikat o bledzie (jezeli blad wystapil)
 * 
 * metoda przesylania (json).
 */

if (!defined('CFG_ROOT'))
{
    /**
     * Stale konfigracyjne
     */
    include_once('../../config.inc.php');
}

$iImg_id_containter = '';
if (!empty($_POST['iId_container']))
{
    $iImg_id_containter = $_POST['iId_container'];
}
$aWynik = array(
    'url' => '',
    'id' => '',
    'error' => true
);

try
{
    $objDB = Database::instance();
}
catch (Exception $e)
{
    $aWynik['message'] = 'nie udało się utworzyć obiektu bazy danych. '.$e->getMessage();
    echo json_encode($aWynik);
}
if(sizeof($_FILES) > 0)
{
    $aDodawane_zdjecie = current( $_FILES );
    $aOptions = array(
        'sImgDir' => CFG_IMG_MULTIUPLOAD,
    );
    $aResult = MultiUpload::copy_multiupload_images($aDodawane_zdjecie, $iImg_id_containter, $objDB, CFG_WWW_IMG, CFG_ROOT_IMG, $aOptions);
    $aDebug = array(
        'table' => 'debug',
        'insert' => array(
            'deb_host' => $_SERVER["SERVER_ADDR"],
            'deb_skrypt' => 'javascript\swfupload\upload_multiupload.php',
            'deb_data_wpr' => date('Y-m-d H:i:s'),
            'deb_czy_blad' => $aResult['error'] ? 'Y' : 'N',
            'deb_message' => Debug::svars($aResult, Grafika::get_log()),
        )
    );
    
    if($_SERVER["SERVER_ADDR"]=='10.0.1.11')
    {
        $aDebug['insert']['deb_host'] = 'web1';
    }
    elseif($_SERVER["SERVER_ADDR"]=='10.0.1.3')
    {
        $aDebug['insert']['deb_host'] = 'web2';
    }
    
    try
    {
        $objDB->insert(
                $aDebug['table'],
                $aDebug['insert']
        );
    }
    catch (Exception $objExc)
    {
        
    }
    /*
     * przygortowanie klasy wynikowej ktora bedzie przekazana jako wynik dzialania
     * format zgodny z obslugiwanym przez handlers.js->uploadSuccess().
     */
    if(!isset($aResult['url']) || true === $aResult['error'])
    {
        if(isset($aResult['message']))
        {
            $aWynik['message'] = $aResult['message'];
        }
        else
        {
            $aWynik['message'] = 'brak url, '.$aResult['message'];
        }
        $aWynik[ 'error' ] = true;
    }
    else
    {
        $aWynik[ 'url' ] = $aResult['url'];
        $aWynik[ 'id' ] = $aResult['id'];
        $aWynik[ 'error' ] = false;
    }
}
else
{
    $aWynik['message'] = 'brak brak danych w $_FILE';
    $aWynik[ 'error' ] = true;
}
/*
 * przekaznaie wyniku do multiuploadu
 */
echo json_encode($aWynik);
exit;