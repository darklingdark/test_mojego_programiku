<?php
/**
 * Interface obslugi klasy zajmujacej sie przekazywaniem slownikow.
 *
 * @author dadan
 */
interface Interface_Lang
{
    /**
     * Pobieranie danych z langu w wersji statycznej
     *
     *    __('Welcome back, :user', array(':user' => $username));
     *
     * [!!] The target language is defined by [Lang::$lang].
     * 
     * @uses    Lang::get
     * @param   string $sString - text to translate
     * @param   bool $mDefault - co ma zwrocic jak nie znajdize wyniku w langach. Przyjmuje postac:
     * <pre>
     * <b>true</b>: tekt przekazan jako pierwszy parametr
     * <b>false</b>: null
     * <b>string</b>: string przekazany jako parametr
     * </pre>
     * @param   array $sValues -  values to replace in the translated text
     * @param   string $sLang - source language default Lang::sLang_PL
     * @return  string
     */
    public static function __($sString, $mDefault = false, array $sValues = NULL, $sLang = Null);
}