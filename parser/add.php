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

$sDZIAL = 'dodaj';




include CFG_ROOT . '/tpl/parser/add.inc.php';