<?php
/**
 * Klasa do wyswietlania informacji na ekranie
 *
 * @author Dariusz Daniec
 */
class Debug
{
    protected static $aLog = array();
    /**
     * Wysietlanie tekstu tylko jezeli jest to srodowisko testowe
     * (stala CFG_TEST_ENV = true)
     *
     * @param mixed $mDane - dane do wyswietlenia
     */
    public static function echo_in_comment($mDane)
    {
        self::echo_ln('<!--'.$mDane.'-->');
    }
    
    /**
     * Wysietlanie tekstu tylko jezeli jest to srodowisko testowe
     * (stala CFG_TEST_ENV = true)
     *
     * @param mixed $mDane - dane do wyswietlenia
     */
    public static function echo_lt($mDane)
    {
        if (CFG_TEST_ENV)
        {
            self::echo_ln($mDane);
        }
    }

    /**
     * Wysietlanie tekstu
     *
     * @param mixed $mDane - dane do wyswietlenia
     */
    public static function echo_ln($mDane)
    {
        if (is_string($mDane))
        {
            echo($mDane);
        }
    }

    /**
     * Wysietlanie przekazanych danych tylko jezeli jest to srodowisko testowe
     * (stala CFG_TEST_ENV = true)
     *
     * @param mixed $mDane - dane do wyswietlenia
     */
    public static function printlt($mDane)
    {
        if (CFG_TEST_ENV)
        {
            echo self::vars($mDane);
        }
    }

    /**
     * Wysietlanie przekazanych danych tylko jezeli jest to srodowisko testowe
     * (stala CFG_TEST_ENV = true)
     *
     * @param mixed $mDane - dane do wyswietlenia
     */
    public static function printlndebug($mDane, $sGetName)
    {
        if (isset($_GET[$sGetName]))
        {
            echo self::vars($mDane);
        }
    }

    /**
     * Wysietlanie przekazanych danych niezaleznie czy jezeli
     * jest to srodowisko testowe czy nie
     *
     * @param mixed $mDane - dane do wyswietlenia
     */
    public static function println($mDane)
    {
        if (func_num_args() === 0)
            return;

        // Get all passed variables
        $variables = func_get_args();
        echo self::vars($variables);
    }

    /**
     * Returns an HTML string of debugging information about any number of
     * variables, each wrapped in a "pre" tag:
     *
     *     // Displays the type and value of each variable
     *     echo Debug::vars($foo, $bar, $baz);
     *
     * @param   mixed   variable to debug
     * @param   ...
     * @return  string
     */
    public static function vars()
    {
        if (func_num_args() === 0)
            return;

        // Get all passed variables
        $variables = func_get_args();

        $output = array();
        foreach ($variables as $var)
        {
            $output[] = Debug::_dump($var, 1024);
        }

        return '<pre class="debug">' . implode("\n", $output) . '</pre>';
    }

    /**
     * Returns an string of debugging information about any number of
     * variables, each wrapped in a "pre" tag:
     *
     *     // Displays the type and value of each variable
     *     echo Debug::vars($foo, $bar, $baz);
     *
     * @param   mixed   variable to debug
     * @param   ...
     * @return  string
     */
    public static function svars()
    {
        if (func_num_args() === 0)
            return;

        // Get all passed variables
        $variables = func_get_args();

        $output = array();
        foreach ($variables as $var_name => $var)
        {
            $output[] = ' ';
            if(is_array($var))
            {
                $output[] = $var_name.':';
                foreach ($var as $key => $value)
                {
                    $output[] = $key.' => '.$value;
                }
            }
            elseif(is_string($var))
            {
                $output[] = $var_name.' => '.$var;
            }
            else
            {
                $output[] = Debug::_dump($var, 1024);
            }
        }

        return '<pre class="debug">' . implode("\n", $output) . '</pre>';
    }

    /**
     * Returns an HTML string of information about a single variable.
     *
     * Borrows heavily on concepts from the Debug class of [Nette](http://nettephp.com/).
     *
     * @param   mixed    variable to dump
     * @param   integer  maximum length of strings
     * @return  string
     */
    public static function dump($value, $length = 128)
    {
        return Debug::_dump($value, $length);
    }

    /**
     * Helper for Debug::dump(), handles recursion in arrays and objects.
     *
     * @param   mixed    variable to dump
     * @param   integer  maximum length of strings
     * @param   integer  recursion level (internal)
     * @return  string
     */
    protected static function _dump(& $var, $length = 128, $level = 0)
    {
        if ($var === NULL)
        {
            return '<small>NULL</small>';
        }
        elseif (is_bool($var))
        {
            return '<small>bool</small> ' . ($var ? 'TRUE' : 'FALSE');
        }
        elseif (is_float($var))
        {
            return '<small>float</small> ' . $var;
        }
        elseif (is_resource($var))
        {
            if (($type = get_resource_type($var)) === 'stream' AND $meta = stream_get_meta_data($var))
            {
                $meta = stream_get_meta_data($var);

                if (isset($meta['uri']))
                {
                    $file = $meta['uri'];

                    if (function_exists('stream_is_local'))
                    {
                        // Only exists on PHP >= 5.2.4
                        if (stream_is_local($file))
                        {
                            $file = Debug::path($file);
                        }
                    }

                    return '<small>resource</small><span>(' . $type . ')</span> ' . htmlspecialchars($file, ENT_NOQUOTES, 'utf-8');
                }
            }
            else
            {
                return '<small>resource</small><span>(' . $type . ')</span>';
            }
        }
        elseif (is_string($var))
        {
            // Clean invalid multibyte characters. iconv is only invoked
            // if there are non ASCII characters in the string, so this
            // isn't too much of a hit.
            //$var = UTF8::clean($var, 'utf-8');
//			if (UTF8::strlen($var) > $length)
//			{
//				// Encode the truncated string
//				$str = htmlspecialchars(UTF8::substr($var, 0, $length), ENT_NOQUOTES, 'utf-8').'&nbsp;&hellip;';
//			}
//			else
//			{
            // Encode the string
            $str = htmlspecialchars($var, ENT_NOQUOTES, 'utf-8');
//			}

            return '<small>string</small><span>(' . strlen($var) . ')</span> "' . $str . '"';
        }
        elseif (is_array($var))
        {
            $output = array();

            // Indentation for this variable
            $space = str_repeat($s = '    ', $level);

            static $marker;

            if ($marker === NULL)
            {
                // Make a unique marker
                $marker = uniqid("\x00");
            }

            if (empty($var))
            {
                // Do nothing
            }
            elseif (isset($var[$marker]))
            {
                $output[] = "(\n$space$s*RECURSION*\n$space)";
            }
            elseif ($level < 17)
            {
                $output[] = "<span>(";

                $var[$marker] = TRUE;
                foreach ($var as $key => & $val)
                {
                    if ($key === $marker)
                        continue;
                    if (!is_int($key))
                    {
                        $key = '"' . htmlspecialchars($key, ENT_NOQUOTES, 'utf-8') . '"';
                    }

                    $output[] = "$space$s$key => " . Debug::_dump($val, $length, $level + 1);
                }
                unset($var[$marker]);

                $output[] = "$space)</span>";
            }
            else
            {
                // Depth too great
                $output[] = "(\n$space$s...\n$space)";
            }

            return '<small>array</small><span>(' . count($var) . ')</span> ' . implode("\n", $output);
        }
        elseif (is_object($var))
        {
            // Copy the object as an array
            $array = (array) $var;

            $output = array();

            // Indentation for this variable
            $space = str_repeat($s = '    ', $level);

            $hash = spl_object_hash($var);

            // Objects that are being dumped
            static $objects = array();

            if (empty($var))
            {
                // Do nothing
            }
            elseif (isset($objects[$hash]))
            {
                $output[] = "{\n$space$s*RECURSION*\n$space}";
            }
            elseif ($level < 10)
            {
                $output[] = "<code>{";

                $objects[$hash] = TRUE;
                foreach ($array as $key => & $val)
                {
                    if ($key[0] === "\x00")
                    {
                        // Determine if the access is protected or protected
                        $access = '<small>' . (($key[1] === '*') ? 'protected' : 'private') . '</small>';

                        // Remove the access level from the variable name
                        $key = substr($key, strrpos($key, "\x00") + 1);
                    }
                    else
                    {
                        $access = '<small>public</small>';
                    }

                    $output[] = "$space$s$access $key => " . Debug::_dump($val, $length, $level + 1);
                }
                unset($objects[$hash]);

                $output[] = "$space}</code>";
            }
            else
            {
                // Depth too great
                $output[] = "{\n$space$s...\n$space}";
            }

            return '<small>object</small> <span>' . get_class($var) . '(' . count($array) . ')</span> ' . implode("\n", $output);
        }
        else
        {
            return '<small>' . gettype($var) . '</small> ' . htmlspecialchars(print_r($var, TRUE), ENT_NOQUOTES, 'utf-8'); //Kohana::$charset);
        }
    }
    
    protected static function _microtime_diff($a, $b)
    {
        list($a_dec, $a_sec) = explode(" ", $a);
        list($b_dec, $b_sec) = explode(" ", $b);
        return $b_sec - $a_sec + $b_dec - $a_dec;
    }
    
    public static function microtime_diff($sStartTime)
    {
        $duration = self::_microtime_diff($sStartTime, microtime());
        return $duration = sprintf("%0.3f sek.", $duration);
    }
    
    public static function microtime_diff_time($sStartTime)
    {
        $duration = self::_microtime_diff($sStartTime, microtime());
        return $duration;
    }
    
    public static function echo_microtime_diff($sStartTime, $sKomentarz)
    {
        $duration = self::_microtime_diff($sStartTime, microtime());
        Debug::echo_lt( $sKomentarz . sprintf("%0.3f sek.", $duration));
    }
    
    /**
     * Dodawanie wpisu do logu
     *
     * @param stringtype $sMessage - tresc wpisu do logu
     */
    public static function addLogMessage($sMessage, $sHeader = '')
    {
        $aDeb = debug_backtrace();
        $aDebug = array(
            'file' => $aDeb[0]['file'],
            'line' => $aDeb[0]['line'],
            'date' => date('Y-m-d H:i:s'),
        );
        if('' != $sHeader)
        {
            $aDebug['head'] = $sHeader;
        }
        $aDebug['message'] = $sMessage;
        self::$aLog[] = $aDebug;
    }
    
    /**
     * Pobieranie infomracji z loga
     *
     * @return array
     */
    public static function getLog()
    {
        return self::$aLog;
    }
    
    /**
     * Resetowanie loga
     * 
     * @return void 
     */
    public static function resetLog()
    {
        $aDeb = debug_backtrace();
        $aDebug = array(
            'file' => $aDeb[0]['file'],
            'line' => $aDeb[0]['line'],
            'date' => date('Y-m-d H:i:s'),
        );
        $aDebug['head'] = 'clear log';
        $aDebug['message'] = 'Wyczyszczono log';
        self::$sLog = array($aDebug);
    }
    
    public static function _exit($sMessage)
    {
        self::println($sMessage);
        exit;
    }
}