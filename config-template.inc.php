<?php
/**
 * Zestaw wszystkich zmiennych konfiguracyjnych.
 */

/**
 * Wybor odpowiedniego zestawu zmiennych.
 * 
 * [!!] Na serwerze pliki konfiguracyjne sa umieszczane 
 *      w katalogu /config
 * 
 * dla ulatwienia dodany zostal switch z nazwami plikow (nie trzeba
 * za kazdym razem sprawdzac jakie sa nazwy plikow konfiguracyjnych). 
 */

$sAktualnie_wybrany_plik_konfiguracyjny = 'default';
switch ($sAktualnie_wybrany_plik_konfiguracyjny)
{
    case 'default':
        include_once '../config\default.inc.php';
        break;
    case 'local':
        include('../config/local.inc.php');
        break;
}

$start_time = microtime();

//UWAGA!:
//Tu wstawiac ewentualny nowy kod konfiga.

$aHead = array();
$aHead['title'] = 'Parser XML onLine';
$aHead['keywords'] = 'Parser XML onLine';
$aHead['description'] = 'Parser XML onLine';

/**
 * Autoloader dla klas
 *
 * @param string $sClass
 */
function __autoload($sClass)
{
    /*
     * przeksztalcenie nazwy klasy na sciezke katalogow.
     */

    $sClass_zmienione = str_ireplace('_', '/', $sClass);

    $link = CFG_ROOT . '/class/';

    if (file_exists($link . $sClass_zmienione . '.class.inc.php'))
    {
        include_once($link . $sClass_zmienione . '.class.inc.php');
    }
    elseif(file_exists($link . strtolower($sClass_zmienione) . '.class.inc.php'))
    {
        include_once($link . strtolower($sClass_zmienione) . '.class.inc.php');
    }
    elseif(file_exists($link . $sClass_zmienione . '.php'))
    {
        include_once($link . $sClass_zmienione . '.php');
    }
}
spl_autoload_register('__autoload');