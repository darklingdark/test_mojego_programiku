<?php

/**
 * Language Class
 */
class Lang implements Interface_Lang
{
    /**
     * Sciezka do katalogu z langami
     * @var string
     */
    protected static $_aLang_dir = false;

    /**
     * tablica zawierajaca wgrane langi
     * @var array
     */
    protected static $_aLang = array();

    /**
     * tablica nazw nalgow wgranych
     * @var array 
     */
    protected static $_aIs_loaded = array();

    /**
     * stala okreslajaca oznaczenie jezyka polskiego
     */
    const sLang_PL = 'pl_PL';
    /**
     * stala okreslajaca onzacznie jezyka angielskiego
     */
    const sLang_EN = 'en_EN';

    // --------------------------------------------------------------------

    /**
     * Tworzenie obiektu klasy Land
     * @return \Lang
     */
    public static function instance()
    {
        static $objLang;

        if (!isset($objLang))
        {
            $objLang = new Lang();
        }
        return $objLang;
        ;
    }

    private function __construct()
    {
        
    }

    /**
     * konfiguracja klasy przed wywolaniem
     *
     * @param string $sSciezka_do_langow - sciezka do katalogu z langami np.: CFG_ROOT . '/lang'
     */
    public static function create($sSciezka_do_langow)
    {
        self::$_aLang_dir = $sSciezka_do_langow;

        if (!isset(self::$_aLang[self::sLang_PL]))
        {
            self::$_aLang[self::sLang_PL] = array();
        }
    }

    /**
     * Czyszczenie langu
     */
    public static function clear()
    {
        self::$_aIs_loaded = array();
        self::$_aLang = array();
        self::$_aLang[self::sLang_PL] = array();
    }

    /**
     * Load a language file
     *
     * @access	public
     * @param	mixed	the name of the language file to be loaded. Can be an array
     * @param	string	the language (Lang::sLang_PL, Lang::sLang_EN, etc.) default is Lang::sLang_PL
     * @param	bool	if true lang will be returned else only storage.
     * @return	mixed
     * @uses Common Uzywa funkcji merge() z klasy Common
     */
    public static function load($mLangfile = '', $sLanguage = Lang::sLang_PL, $bReturn = FALSE)
    {
        if (false === self::$_aLang_dir)
        {
            throw new Exception('First use create function');
        }
        isset(self::$_aIs_loaded[$sLanguage]) ? false : self::$_aIs_loaded[$sLanguage] = array();
        isset(self::$_aLang[$sLanguage]) ? false : self::$_aLang[$sLanguage] = array();

        $aLangfile = $mLangfile;
        if (!is_array($mLangfile))
        {
            $aLangfile = array($mLangfile);
        }
        $aLang_table = array();
        foreach ($aLangfile as $sLangfile)
        {
            if (!in_array($sLangfile, self::$_aIs_loaded[$sLanguage], TRUE))
            {
                // Determine where the language file is and load it
                if (file_exists(CFG_ROOT . '/lang/' . $sLanguage . '/' . $sLangfile))
                {
                    include(CFG_ROOT . '/lang/' . $sLanguage . '/' . $sLangfile);
                    if (isset($aLang))
                    {
                        $aLang_table = Common::merge($aLang_table, $aLang);
                        self::$_aIs_loaded[$sLanguage][] = $sLangfile;
                        unset($aLang);
                    }
                }
                else
                {
                    //print ('Unable to load the requested language file: language/' . $sLanguage . '/' . $sLangfile);
                }
            }
        }
        self::$_aLang[$sLanguage] = $aLang_table = Common::merge(self::$_aLang[$sLanguage], $aLang_table);
        if ($bReturn == TRUE)
        {
            return $aLang_table;
        }
        return TRUE;
    }

    /**
     * Load a language from array 
     * !!!UWAGA fukcja moze nadpisac istniejace wpisy !!!
     *
     * @access	public
     * @param	array	tablica zawierajaca langi do wgrania w postaci:
     * <pre>
     * array(
     *      'nazwa_langu' => 'klucz_langu_w_langach' => array(
     *          [tablica z langami],
     *          ...
     *      ),
     *      ..
     * );
     * </pre>
     * @param	string	the language (Lang::sLang_PL, Lang::sLang_EN, etc.) default is Lang::sLang_PL
     * @param	bool	if true lang will be returned else only storage.
     * @return	mixed
     * @uses Common Uzywa funkcji merge() z klasy Common
     */
    public static function loadArray(array $aLang, $sLanguage = Lang::sLang_PL, $bReturn = FALSE)
    {
        if (false === self::$_aLang_dir)
        {
            throw new Exception('First use create function');
        }

        isset(self::$_aLang[$sLanguage]) ? false : self::$_aLang[$sLanguage] = array();

        self::$_aLang[$sLanguage] = Common::merge(self::$_aLang[$sLanguage], $aLang);

        if ($bReturn == TRUE)
        {
            return $aLang_table;
        }
        return TRUE;
    }
    // --------------------------------------------------------------------

    /**
     * Fetch a single line of text from the language array
     *
     * @access	public
     * @param	string	$line 	the language line
     * @param	string	the language (Lang::sLang_PL, Lang::sLang_EN, etc.) default is Lang::sLang_PL
     * @return	string
     */
    public static function get($sLine = '', $sDefault = '', $sLang = Lang::sLang_PL)
    {
        if (false === self::$_aLang_dir)
        {
            throw new Exception('First use create function');
        }
        
        $aLine = explode('|', $sLine);
        
        $aLang = self::$_aLang[$sLang];

        foreach ($aLine as $sKlucz)
        {
            if('' == $sKlucz)
            {
                return $sDefault;
            }
            
            if (!isset($aLang[$sKlucz]))
            {
                return $sLine;
            }
            
            $aLang = $aLang[$sKlucz];
        }
        
        return $aLang;
    }
    // --------------------------------------------------------------------

    /**
     * return all lines of text from the language array
     *
     * @access	public
     * @param	string	$line 	the language line
     * @param	string	the language (Lang::sLang_PL, Lang::sLang_EN, etc.) default is Lang::sLang_PL
     * @return	string
     */
    public static function get_all($sLanguage = Lang::sLang_PL)
    {
        if (false === self::$_aLang_dir)
        {
            throw new Exception('First use create function');
        }
        return self::$_aLang[$sLanguage];
    }

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
     * <b>true</b>: tekt przekazany jako pierwszy parametr
     * <b>false</b>: null
     * <b>string</b>: string przekazany jako parametr
     * </pre>
     * @param   array $sValues -  values to replace in the translated text
     * @param   string $sLang - source language default Lang::sLang_PL
     * @return  string
     */
    public static function __($sString, $mDefault = false, array $sValues = NULL, $sLang = Null)
    {
        $sWynik = '';
        
        if (is_null($sLang))
        {
            $sLang = Lang::sLang_PL;
        }
        if ($sLang !== self::sLang_EN)
        {
            $sDefault_lang_value = '';
            if (false === $mDefault)
            {
                $sDefault_lang_value = NULL;
            }
            elseif (true === $mDefault)
            {
                $sDefault_lang_value = $sString;
            }
            // The message and target languages are different
            // Get the translation for this message
            $sWynik = self::get($sString, $sDefault_lang_value, $sLang);
        }
        
        if (($sString == $sWynik) && empty($sValues))
        {
            if (is_bool($mDefault))
            {
                if ($mDefault)
                {
                    return $sString;
                }
                return NULL;
            }
            return $mDefault;
        }
//        Debug::println('$mDefault1',$mDefault,$sValues);
        return empty($sValues) ? $sWynik : strtr($sWynik, $sValues);
    }
}
if (!function_exists('__'))
{

    /**
     * Kohana translation/internationalization function. The PHP function
     * [strtr](http://php.net/strtr) is used for replacing parameters.
     *
     *    __('Welcome back, :user', array(':user' => $username));
     *
     * [!!] The target language is defined by [Lang::$lang].
     * 
     * @uses    Lang::get
     * @param   string  text to translate
     * @param   array   values to replace in the translated text
     * @param   string  source language default Lang::sLang_PL
     * @return  string
     */
    function __($sString, array $sValues = NULL, $sLang = Lang::sLang_PL)
    {
        return Lang::__($sString, true, $sValues, $sLang);
    }
}

/*
 * konfiguracja klasy lang
 */
Lang::create(CFG_ROOT . '/lang');
