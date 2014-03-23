<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/*
 * stala okreslajaca liste adresow ip akola
 * nalezy je expldowac do tablicy i weryfikowac
 */
define('CFG_TRUSTED_IP', implode(',', array(
    '127.0.0.1', //localhost
    '94.246.191.43' //akol
)));

define('CFG_TEST_ENV', true);
 
/**
 *  Limit o ile nie moze byc starsze auto od aktualnego roku.
 */
define('CFG_NOWE_LIMIT_LAT_POJAZDU', 2);

/**
 *  Adres internetowy.
 */

define('CFG_WWW', 'http://localparser.pl');

/**
 *  Adres internetowy do obrazkow.
 */

define('CFG_WWW_STYLE', 'http://style.localparser.pl');

/**
 *  Adres internetowy do obrazkow.
 */

define('CFG_WWW_IMG', 'http://img.localparser.pl');

/**
 *  Adres internetowy do obrazkow.
 */

define('CFG_WWW_JS', 'http://js.localparser.pl');

/**
 *  Sciezka w strukturze katalogow.
 */

define('CFG_ROOT', 'C:\xampp\htdocs\parser_xml');

/**
 *  Sciezka w strukturze katalogow do katalogu gdzie zapisywane sa pliki XML.
 */
define('CFG_ROOT_XML', 'C:\xampp\htdocs\parser_xml\generated');

/**
 *  Sciezka w strukturze katalogow.
 */

define('CFG_ROOT_IMG', 'C:\xampp\htdocs\parser_xml\images');

/**
 * Serwer pocztowy
 * na lokalu podajemy 
 * na serwerze odpowiedni url
 */
define('CFG_MAILGATE_HOST', 'ssl://smtp.gmail.com');

/**
 * parametry do laczenia z serwerem pocztowym za pomoca pear::mail::factory:<br />
 * $Mailing = Mail::factory( 'smtp', $CFG_MAILGATE_HOST_PARAMS );<br />
 * <br />
 * na lokalu podajemy <br />
 * na serwerze odpowiedni url<br />
 */
$CFG_MAILGATE_HOST_PARAMS = array(
    'host' => CFG_MAILGATE_HOST,
    'auth' => true, //na lokalu = true na serwerze = false
    'port' => '465', //na lokalu = 764 (gmail.com) na serwerze = '' lub nie podane
    'username' => 'dariusz.daniec@gmail.com', //na lokalu jest podane na serwerze = '' lub nie podane
    'password' => 'wqctmldgwiikhkxl', //na lokalu jest podane na serwerze = '' lub nie podane
);

/**
 *  Adres w strukturze katalogow.
 */

$TEMPLATE_NAME = 'default';

/**
 *  Nazwa bazy danych.
 */

define('CFG_DB_BASE', 'parser_xml');

/**
 *  Host bazy danych.
 */
define('CFG_DB_HOST', '192.168.0.4');

/**
 *  Uzytkownik bazy danych.
 */
define('CFG_DB_USER', 'pxml');

/**
 *  hasło do bazy danych.
 */
define('CFG_DB_PASS', 'htum30x_29d');

/**
 *  Nazwa projektu. (tekst uzywany w naglowkach dokumentacyjnych dla phpdoc-a )
 */

define('CFG_PROJECT', 'Parser');

/*
 * Zewnetrzne biblioteki javascriptowe
 */
define('CFG_JQUERY_MIN_JS', CFG_WWW_JS . '/jquery/jquery-1.8.2.min.js');
define('CFG_JQUERYUI_MIN_JS', CFG_WWW_JS . '/jquery/jquery-ui-1.9.1.custom.min.js');
define('CFG_BOOTSTRAP_MIN_JS', CFG_WWW_JS . '/bootstrap/bootstrap.min.js');
define('CFG_JQUERYUI_CSS', CFG_WWW_STYLE . '/css/jquery-ui-1.9.1.custom.min.css');

/*
 * zabezpieczenie plikow css przy keszowaniu
 */
define('CFG_CSS_REF_V', '1');

/*
 * zabezpieczenie plikow js przy keszowaniu
 */
define('CFG_JS_REF_V', '1');


$aConfigDB = array(
    'parser_xml' => array(
        'type' => 'mysql',
        'connection' => array(
            /**
             * The following options are available for MySQL:
             *
             * string   hostname     server hostname, or socket
             * string   database     database name
             * string   username     database username
             * string   password     database password
             * boolean  persistent   use persistent connections?
             * array    variables    system variables as "key => value" pairs
             *
             * Ports and sockets may be appended to the hostname.
             */
            'hostname' => CFG_DB_HOST,
            'database' => 'gielda_biuro',
            'username' => CFG_DB_USER,
            'password' => CFG_DB_PASS,
            'persistent' => FALSE,
        ),
        'table_prefix' => '',
        'charset' => 'utf8',
        'caching' => FALSE,
        'profiling' => TRUE,
    )
);