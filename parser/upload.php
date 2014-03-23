<?php

/* 
 * Skrypt wywoływany przy wysłaniu XML-a do analizy.
 */

if(!defined('CFG_ROOT'))
{
    include '../config.inc.php';
}

$aWynik = array(
    'status' => false,
);

if (isset($_FILES["file"]))
{
    if ($_FILES["file"]["error"] > 0)
    {
        $aWynik = array(
            'status' => false,
            'error' => $_FILES["file"]["error"],
        );
        
        echo json_encode($aWynik);
        exit;
    } 
    else
    {
        
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
        
        $ParserXml = new ParserXml($DB);
        
        try
        {
            $iIdPaczki = $ParserXml->analizujXml($_FILES["file"]['tmp_name']);
        }
        catch (Exception $ex)
        {
            $aWynik = array(
                'status' => false,
                'info' => 'Nie udało się przeanalizowac XMLa. Błędy: ' . $ex->getMessage(),
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

        $aWynik = array(
            'status' => true,
            'info' => 'Udało się pobrać plik ' . $_FILES["file"]['name'],
            'id' => $iIdPaczki,
        );
        
        echo json_encode($aWynik);
        exit;
    }
}

$aWynik = array(
    'status' => false,
    'error' => 'Nie przekazano plikow.',
);

echo json_encode($aWynik);
exit;