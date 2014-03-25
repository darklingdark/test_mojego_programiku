<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @author dariusz
 */
// TODO: check include path
//ini_set('include_path', ini_get('include_path'));


define('CFG_ROOT', 'C:\xampp\htdocs\calkulator');
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

    $link = CFG_ROOT . '/src/class/' . $sClass_zmienione . '.php';

    if (file_exists($link))
    {
        include_once($link);
    }
    else
    {
        echo ('Brak pliku z klasa: ' . $sClass.' link: ' . $link);
    }
}
spl_autoload_register('__autoload');