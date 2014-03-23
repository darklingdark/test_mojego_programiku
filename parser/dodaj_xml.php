<?php

/* 
 * Skrypt wywoływany przy przesłaniu XML-a z formularza przez kontrolkę file
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
        $aAddErrors[] = $_FILES["file"]["error"];
        
        include 'add.php';
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
            $aAddErrors[] = 'Wystąpił problem z konunikacją z bazą danych.' . $Exc->getMessage();

            include 'add.php';
            exit;
        }
        
        $ParserXml = new ParserXml($DB);
        
        try
        {
            $iIdPaczki = $ParserXml->analizujXml($_FILES["file"]['tmp_name']);
        }
        catch (Exception $ex)
        {
            $aAddErrors[] = 'Nie udało się przeanalizowac XMLa. Błędy: ' . $ex->getMessage();

            include 'add.php';
            exit;
        }
        
        $aAddSuccess[] = 'Udało się pobrać plik ' . $_FILES["file"]['name'];
        $aAddSuccess[] = 'XML mozna pobrac poniższym tym <a href="'. CFG_WWW . '/pobierz.php?id=' . $iIdPaczki . '">Linkiem</a>.';
        $aAddSuccess[] = 'Można także pobrać w zakładce "Pobierz przygotowane XML-e". Paczka ma id='.$iIdPaczki;

        include 'add.php';
        exit;   
    }
}

$aAddErrors[] = 'Nie przekazano plikow.';

include 'add.php';
exit;