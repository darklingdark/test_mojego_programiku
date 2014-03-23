<?php
/**
 * Obsluga polaczenia z baza danych oraz zapytan SQL
 *
 * @package Database
 */
abstract class DB_Database  implements DB_Interface
{
    /**
     * @var  array  Kody bledow dla wyjatkow
     */
    protected $aErrorCode = array();

    // Query types
    const SELECT =  1;
    const INSERT =  2;
    const UPDATE =  3;
    const DELETE =  4;
    
    
    /**
     * @var  string  default instance name
     */
    public static $default = 'default';

    /**
     * @var  array  DB_Database instances
     */
    public static $instances = array();

        /**
         * Zwraca kod bledu wyjatku na podstawie nazwy symbolicznej wyjatku
         * 
         * @param type $sErrorCode
         * @return int kod bledu wyjatku
         */
        public function getErrorCode($sErrorCode)
        {
            if(isset($this->aErrorCode[$sErrorCode]))
            {
                return $this->aErrorCode[$sErrorCode];
            }
            else 
            {
                return null;
            }
        }    
    
    /**
     * Get a singleton DB_Database instance. If configuration is not specified,
     * it will be loaded from the DB_Database configuration file using the same
     * group as the name.
     *
     *     // Load the default DB_Database
     *     $db = DB_Database::instance();
     *
     *     // Create a custom configured instance
     *     $db = DB_Database::instance('custom', $config);
     *
     * @param   string   instance name
     * @param   array    configuration parameters
     * @return  DB_Database
     */
    public static function instance($name = NULL, array $config = NULL)
    {
        if ($name === NULL)
        {
            // Use the default instance name
            $name = DB_Database::$default;
        }

        if (!isset(DB_Database::$instances[$name]))
        {
            if ($config === NULL)
            {
                $config = array
                    (
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
                        'database' => CFG_DB_BASE,
                        'username' => CFG_DB_USER,
                        'password' => CFG_DB_PASS,
                        'persistent' => FALSE,
                    ),
                    'table_prefix' => '',
                    'charset' => 'utf8',
                    'caching' => FALSE,
                    'profiling' => TRUE,
                );
            }

            if (!isset($config['type']))
            {
                throw new Exception('DB_Database type not defined in ' . $name . ' configuration');
            }

            // Set the driver class name
            $driver = 'DB_Database_' . ucfirst($config['type']);
            
            //echo Debug::vars($driver);

            // Create the DB_Database connection instance
            new $driver($name, $config);
        }

        return DB_Database::$instances[$name];
    }
    /**
     * @var  string  the last query executed
     */
    public $last_query;
    // Character that is used to quote identifiers
    protected $_identifier = '"';
    // Instance name
    protected $_instance;
    // Raw server connection
    protected $_connection;
    // Configuration array
    protected $_config;

    /**
     * Stores the DB_Database configuration locally and name the instance.
     *
     * [!!] This method cannot be accessed directly, you must use [DB_Database::instance].
     *
     * @return  void
     */
    protected function __construct($name, array $config)
    {
        // Set the instance name
        $this->_instance = $name;

        // Store the config locally
        $this->_config = $config;

        // Store the DB_Database instance
        DB_Database::$instances[$name] = $this;
    }

    /**
     * Disconnect from the DB_Database when the object is destroyed.
     *
     *     // Destroy the DB_Database instance
     *     unset(DB_Database::instances[(string) $db], $db);
     *
     * [!!] Calling `unset($db)` is not enough to destroy the DB_Database, as it
     * will still be stored in `DB_Database::$instances`.
     *
     * @return  void
     */
    final public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Returns the DB_Database instance name.
     *
     *     echo (string) $db;
     *
     * @return  string
     */
    final public function __toString()
    {
        return $this->_instance;
    }

    /**
     * Connect to the DB_Database. This is called automatically when the first
     * query is executed.
     *
     *     $db->connect();
     *
     * @throws  DB_Database_Exception
     * @return  void
     */
    abstract public function connect();

    /**
     * Disconnect from the DB_Database. This is called automatically by [DB_Database::__destruct].
     * Clears the DB_Database instance from [DB_Database::$instances].
     *
     *     $db->disconnect();
     *
     * @return  boolean
     */
    public function disconnect()
    {
        unset(DB_Database::$instances[$this->_instance]);

        return TRUE;
    }

    /**
     * Set the connection character set. This is called automatically by [DB_Database::connect].
     *
     *     $db->set_charset('utf8');
     *
     * @throws  DB_Database_Exception
     * @param   string   character set name
     * @return  void
     */
    abstract public function set_charset($charset);

    /**
     * Perform an SQL query of the given type.
     *
     *     // Make a SELECT query and use objects for results
     *     $db->query(DB_Database::SELECT, 'SELECT * FROM groups', TRUE);
     *
     *     // Make a SELECT query and use "Model_User" for the results
     *     $db->query(DB_Database::SELECT, 'SELECT * FROM users LIMIT 1', 'Model_User');
     *
     * @param   integer  DB_Database::SELECT, DB_Database::INSERT, etc
     * @param   string   SQL query
     * @param   mixed    result object class string, TRUE for stdClass, FALSE for assoc array
     * @param   array    object construct parameters for result class
     * @return  object   DB_Database_Result for SELECT queries
     * @return  array    list (insert id, row count) for INSERT queries
     * @return  integer  number of affected rows for all other queries
     */
    public function query($type, $sql, $as_object = FALSE, array $params = NULL)
    {
    }

    /**
     * Count the number of records in a table.
     *
     *     // Get the total number of records in the "users" table
     *     $count = $db->count_records('users');
     *
     * @param   mixed    table name string or array(query, alias)
     * @return  integer
     */
    public function count_records($table)
    {
        // Quote the table name
        $table = $this->quote_table($table);

        return $this->query(DB_Database::SELECT, 'SELECT COUNT(*) AS total_row_count FROM ' . $table, FALSE)
                        ->get('total_row_count');
    }

    /**
     * List all of the tables in the DB_Database. Optionally, a LIKE string can
     * be used to search for specific tables.
     *
     *     // Get all tables in the current DB_Database
     *     $tables = $db->list_tables();
     *
     *     // Get all user-related tables
     *     $tables = $db->list_tables('user%');
     *
     * @param   sfetring   table to search for
     * @return  array
     */
    abstract public function list_tables($like = NULL);

    /**
     * Lists all of the columns in a table. Optionally, a LIKE string can be
     * used to search for specific fields.
     *
     *     // Get all columns from the "users" table
     *     $columns = $db->list_columns('users');
     *
     *     // Get all name-related columns
     *     $columns = $db->list_columns('users', '%name%');
     *
     *     // Get the columns from a table that doesn't use the table prefix
     *     $columns = $db->list_columns('users', NULL, FALSE);
     *
     * @param   string  table to get columns from
     * @param   string  column to search for
     * @param   boolean whether to add the table prefix automatically or not
     * @return  array
     */
    abstract public function list_columns($table, $like = NULL, $add_prefix = TRUE);
    /**
     * @var boolean informacja czy obiekt jest polaczony z baza danych
     * @protected
     */
    protected $bConnected;

    /**
     * @var string Metoda kodowania znaków pol±czenia.
     * @private
     */
    private $sDb_character_set;

    /**
     *
     * @var string Host bazodanowy
     * @private
     */
    private $sDb_host;

    /**
     *
     * @var string Uzytkownik bazy danych
     * @private
     */
    private $sDb_user;

    /**
     *
     * @var string Haslo dostepu do bazy danych
     */
    private $sDb_pass;

    /**
     * wykonuje polaczenie z baza danych.
     *
     */
//    private function connect()
//    {
//        if ($this->_connection = @mysql_connect($this->sDb_host, $this->sDb_user, $this->sDb_pass))
//        {
//            
//        }
//        else
//        {
//            throw new Exception("Nie można nawiązać połączenia z bazą danych.", E_USER_ERROR);
//        }
//
//        if (mysql_select_db(CFG_DB_BASE, $this->_connection))
//        {
//            
//        }
//        else
//        {
//            throw new Exception("Nie można wybrać bazy danych.", E_USER_ERROR);
//        }
//
//        $query = 'SET CHARACTER SET ' . $this->sDb_character_set;
//        mysql_query($query, $this->_connection);
//        $query = 'SET NAMES ' . $this->sDb_character_set;
//        mysql_query($query, $this->_connection);
//
//        $this->bDb_connected = true;
//    }
//    public function __destruct()
//    {
//        if (is_resource($this->_connection))
//        {
//            mysql_close($this->_connection);
//        }
//    }

    /**
     * Zwraca tresc ostatnio wykonanego zapytania sql
     */
    public function get_last_query()
    {
        return $this->last_query;
    }

    /**
     * Funkcja dopisuje znaki slasch "\" do znaków specjalnych
     * wywołując funkcję mysql_real_escape_string.
     *
     * @param <string> $str treść zapytania do modyfikacji.
     * @param <string> $db_link link do połączenia z bazą danych.
     * @return <string> zmodyfikowana treść zapytania.
     */
    public function DB_Database_real_escape_string($str)
    {
        if (false == $this->_connection)
        {
            $this->connect();
        }
        $wynik = mysql_real_escape_string($str, $this->_connection);
        return $wynik;
    }
    /**
     * Wykonanie dowolnego zapytania SQL
     * 
     * Wykorzystywane np. do select-�w i update-�w na kilku tablicach        
     *
     * @param string
     *
     * @return mixed
     *
     */
//    public function query($sql)
//    {
//        if (false == $this->_connection)
//        {
//            $this->connect();
//        }
//        $this->last_query = $sql;
//        $result = mysql_query($sql, $this->_connection);
//
//        if ($result === false)
//        {
//            $err = mysql_error($this->_connection);
//            throw new Exception($err);
//        }
//
//        return $result;
//    }

    /**
     * Wykonanie zapytania INSERT do bazy
     * 
     * Tablica $arFieldValues musi by� wcze�niej zweryfikowana,
     * lista kolumn musi by� ograniczona do listy kolum tablicy!!!                
     *
     * @param string, nazwa tablicy
     * @param array, tablica z danymi do wstawienia. Kluczem jest nazwa kolumny,
     *   warto�ci� wartola kolumy
     *
     * @return int, luczba wstawionych wierszy
     *
     */
    public function insert($table, $arFieldValues)
    {
//     println($arFieldValues);
        // Tworzy tablic� warto�ci, kt�ra zostanie
        // dolaczona do klauzuli VALUES.
        // Funkcja mysql_real_escape_string cytuje
        // te warto�ci, kt�re nie s� liczbowe.

        if (false == $this->_connection)
        {
            $this->connect();
        }
        $escVals = array();
        foreach ($arFieldValues as $nazwa_kolumny => $wartosc)
        {
            if (!is_numeric($wartosc))
            {
                /** zmiany z dnia 11-25-2009: darek, zmiana z :
                 *  $wartosc = "'" . mysql_real_escape_string($wartosc,$this->_connection) . "'";    */
                $wartosc = "'" . $this->DB_Database_real_escape_string($wartosc) . "'";
            }
            $escVals[] = $wartosc;
            $fields[] = $nazwa_kolumny;
        }

        //tworzy instrukcj� SQL
        $sql = " INSERT INTO $table (";
        $sql .= join(', ', $fields);
        $sql .= ') VALUES(';
        $sql .= join(', ', $escVals);
        $sql .= ')';
        //println($sql);

        $this->last_query = $sql;

        $result = mysql_query($sql);
        if ($result === false)
        {
            // $err = pg_last_error($this->hConn) . "\n" . $sql;
            $err = mysql_error($this->_connection);
            throw new Exception($err);
        }

        return mysql_affected_rows($this->_connection);
    }

    /**
     * mysql_insert_id
     *
     * Zwraca identyfikator ostatnio wstawionego wiersza.
     *
     * @return int identyfikator ostatnio wstawionego wiersza
     */
    function insert_id()
    {
        $result = mysql_query('SELECT LAST_INSERT_ID() as id;');   
        $aWynik = $this->fetch($result);
        return $aWynik['id'];
    }

    /**
     * Zapytanie typu DELETE
     *
     * @param string, nazwa tablicy
     * @param array, tablica kolumn w tablicy bazydanych
     * @param array, tablica warunkow dla kluzuli WHERE
     *
     * @return int, luczba skasowanych wierszy
     *
     */
    function delete($table, $tableField, $arConditions)
    {
        // tworzy tablice dla klauzuli WHERE
        $arWhere = array();

        if (false == $this->_connection)
        {
            $this->connect();
        }
        foreach ($tableField as $k => $v)
        {
            if (isset($arConditions[$v['par_kol']]))
            {
                if (!is_numeric($arConditions[$v['par_kol']]))
                {
                    //cytuje dane
                    /** zmiany z dnia 11-25-2009: darek, zmiana z :
                     *  $val = "'" . mysql_real_escape_string($arConditions[ $v['par_kol'] ],$this->_connection) . "'";    */
                    $val = "'" . $this->DB_Database_real_escape_string($arConditions[$v['par_kol']]) . "'";
                }
                else
                {
                    $val = $arConditions[$v['par_kol']];
                }
                $arWhere[] = $v['par_kol'] . " = $val";
            }
        }

        if(count($arWhere) == 0)
        {
            throw new Exception('F:'.__FILE__.'('.__LINE__.'): Nie podano prawidlowych warunkow usowania z tablicy');
        }
        
        $sql = "DELETE FROM $table WHERE " . join(' AND ', $arWhere);

        $this->last_query = $sql;

        $result = mysql_query($sql);
        if ($result === false)
        {
            $err = mysql_error($this->_connection);
            throw new Exception($err);
        }

        return mysql_affected_rows($this->_connection);
    }

    /**
     * Zapytanie typu UPDATE
     *
     * @param string nazwa tablicy
     * @param array wartosci do wstawienia
     * @param array tablica warunkow dla kluzuli WHERE  (tylko warunku rownosci!)
     *
     * @return int liczba uaktualnionych wierszy
     *
     */
    public function update($table, $arFieldValues, $arConditions)
    {
        // tworzy tablice dla klauzuli SET
        $arUpdates = array();

        if (false == $this->_connection)
        {
            $this->connect();
        }
        
        foreach ($arFieldValues as $nazwa_kolumny => $wartosc)
        {

            if (!is_null($wartosc))
            {

                if (!is_numeric($wartosc))
                {
                    //cytuje dane
                    /** zmiany z dnia 11-25-2009: darek, zmiana z :
                     *  $val = "'" . mysql_real_escape_string($wartosc,$this->_connection) . "'";    */
                    $val = "'" . $this->DB_Database_real_escape_string($wartosc) . "'";
                }
                else
                {
                    $val = $wartosc;
                }
                $arUpdates[] = $nazwa_kolumny . ' = ' . $val;
            }
        }


        // tworzy tablice dla klauzuli WHERE
        $arWhere = array();

        foreach ($arConditions as $nazwa_kolumny => $wartosc)
        {
            if (!is_numeric($wartosc))
            {
                //cytuje dane
                /* zmiany z dnia 11-25-2009: darek, zmiana z :
                 * $val = "'" . mysql_real_escape_string($wartosc,$this->_connection) . "'";
                 */
                $val = "'" . $this->DB_Database_real_escape_string($wartosc) . "'";
            }
            else
            {
                $val = $wartosc;
            }
            $arWhere[] = $nazwa_kolumny . ' = ' . $val;
        }


        $sql = 'UPDATE ' . $table . ' SET ';
        $sql .= join(', ', $arUpdates);
        $sql .= ' WHERE ' . join(' AND ', $arWhere);

        $this->last_query = $sql;

        $result = mysql_query($sql);
        if ($result === false)
        {
            $err = mysql_error($this->_connection);
            throw new Exception($err);
        }
        return mysql_affected_rows($this->_connection);
    }

    /**
     * Zapytanie typu UPDATE
     *
     * @param string nazwa tablicy
     * @param array wartosci do wstawienia
     * @param array tablica warunkow dla kluzuli WHERE wraz z operatorami
     *
     * @return int liczba uaktualnionych wierszy
     *
     */
    public function update2($table, $arFieldValues, $arConditions, $limit = null)
    {
        // tworzy tablice dla klauzuli SET
        $arUpdates = array();

        if (false == $this->_connection)
        {
            $this->connect();
        }
        
        foreach ($arFieldValues as $nazwa_kolumny => $wartosc)
        {

            if (!is_null($wartosc))
            {

                if (!is_numeric($wartosc))
                {
                    //cytuje dane
                    /** zmiany z dnia 11-25-2009: darek, zmiana z :
                     *  $val = "'" . mysql_real_escape_string($wartosc,$this->_connection) . "'";    */
                    $val = "'" . $this->DB_Database_real_escape_string($wartosc) . "'";
                }
                else
                {
                    $val = $wartosc;
                }
                $arUpdates[] = $nazwa_kolumny . ' = ' . $val;
            }
        }



        // tworzy tablice dla klauzuli WHERE
        $arWhere = array();

        foreach ($arConditions as $condition)
        {
            if (!is_numeric($condition[2]) && !is_array($condition[2]))
            {
                //cytuje dane
                /* zmiany z dnia 11-25-2009: darek, zmiana z :
                 * $val = "'" . mysql_real_escape_string($wartosc,$this->_connection) . "'";
                 */
                $val = "'" . $this->DB_Database_real_escape_string($condition[2]) . "'";
            }
            else
            {
                $val = $condition[2];
            }

            if ($condition[1] == 'IN')
            {
                $arWhere[] = $condition[0] . ' ' . $condition[1] . " ('" . implode("','", $val) . "')";
            }
            else
            {
                $arWhere[] = $condition[0] . ' ' . $condition[1] . ' ' . $val;
            }
        }

        $sql = 'UPDATE ' . $table . ' SET ';
        $sql .= join(', ', $arUpdates);
        $sql .= ' WHERE ' . join(' AND ', $arWhere);
        
        if( $limit )
        {
            $sql .= ' LIMIT '.$limit;
        }
        
        $this->last_query = $sql;

        $result = mysql_query($sql, $this->_connection);
        if ($result === false)
        {
            $err = mysql_error($this->_connection);
            throw new Exception($err);
        }
        return mysql_affected_rows($this->_connection);
    }

    /**
     * Wykonanie zapytania SELECT
     *
     * Wersja ze skrocona liczba parametrow oraz rozszerzona o opcje HAVING
     *
     *
     *
     * @param array tablica elementow zapytania
     * @param array boolean czy zablokowac wykonanie zapytania (zamiast wykonwywac tylko ustawia 'last_query')
     *
     * @return mixed wynik wykonania zapytania SQL
     *
     */
    public function select($aQuery, $do_not_execute = false)
    {
        if (false == self::$instances[$this->_instance])
        {
            $this->connect();
        }
        //println($aQuery);

        $aSkladowe = array(
            'select', 'from', 'where', 'limit', 'group', 'having', 'order'
        );

        foreach ($aSkladowe as $v)
        {
            if (!isset($aQuery[$v]) || !is_array($aQuery[$v]))
            {
                $aQuery[$v] = array();
            }
        }


        $query_sel = ' SELECT SQL_CALC_FOUND_ROWS ';
        $query_sel .= implode(',', $aQuery['select']);

        $query_from = ' FROM ';
        $query_from .= implode(',', $aQuery['from']);

        if (sizeof($aQuery['where']) > 0)
        {
            $query_where = ' WHERE ';
            /** zmiany z dnia 11-25-2009: darek */
            foreach ($aQuery['where'] as $query_where_key => $query_where_val)
            {
                if (!is_numeric($query_where_val))
                {
                    /** zmiany z dnia 11-25-2009: darek, zmiana z :
                     *  $query_where_val = "'" . mysql_real_escape_string($query_where_val,$this->_connection) . "'";    */
                    $query_where_val = "'" . $this->DB_Database_real_escape_string($query_where_val) . "'";
                }
            }
            /** koniec zmian */
            $query_where .= implode(' AND ', $aQuery['where']);
        }
        else
        {
            $query_where = ' WHERE ';
            $query_where .= ' 1 ';
        }

        if (sizeof($aQuery['group']) > 0)
        {
            $query_group = ' GROUP BY ';
            $query_group .= implode(',', $aQuery['group']);
        }
        else
        {
            $query_group = '';
        }

        if (sizeof($aQuery['having']) > 0)
        {
            $query_having = ' HAVING ' . implode(' AND HAVING ', $aQuery['having']);
        }
        else
        {
            $query_having = '';
        }

        if (sizeof($aQuery['order']) > 0)
        {
            $query_ord = ' ORDER BY ';
            $query_ord .= implode(',', $aQuery['order']);
        }
        else
        {
            $query_ord = '';
        }

        if (sizeof($aQuery['limit']) > 0)
        {
            $query_lim = ' LIMIT ';
            $query_lim .= implode(',', $aQuery['limit']);
        }
        else
        {
            $query_lim = '';
        }

        $query = $query_sel . $query_from . $query_where . $query_group . $query_having . $query_ord . $query_lim;

        $this->last_query = $query;

        if (false == $do_not_execute)
        {
            $result = mysql_query($query, $this->_connection);

            if ($result === false)
            {
                $err = mysql_error($this->_connection);
                throw new Exception($err);
            }

            return $result;
        }
        
        return 0;
    }

    /**
     * Zwraca liczbe wierszy, ktore zostaly zmodyfikowane w ostatnim
     * zapytaniu. (przydatne po insertach, update-ach i delete-ach)
     */
    abstract function count_affected_rows();
    
    /**
     * Zwraca liczbe wierszy, ktore zostaly zmodyfikowane w ostatnim
     * zapytaniu. (przydatne po insertach, update-ach i delete-ach)
     */
    abstract function count_found_rows();

    /**
     * 
     */
    function fetch(&$result, $result_type = MYSQL_ASSOC)
    {
        if(is_resource($result))
        {
            return mysql_fetch_array($result, $result_type);
        }
        else
        {
            throw new Exception('$result is not a resource.');
        }
    }
    
    /**
     * pobranie wynikow zapytania jezeli pobieramy tylko 2 wartosci i chcemy uzyskac
     * tablice w postaci klucz => wartosc.
     * 
     * @param resource $result - resource pobrany z bazy
     * @param int $result_type - (default MYSQL_ASSOC) jeden z predefiniowanych parametrow np MYSQL_ASSOC
     * @param array $aParams - tablica przyjmujaca parametr:
     * <b>key</b> kolumna, ktorej wartosci maja byc kluczami tablicy wynikowej
     * jezeli nie podamy, uzyty zostanie licznik.<br />
     * <b>value</b> kolumna, ktorej wartosci maja byc wartoscia tablicy wynikowej
     * jezeli nie podamy, dodana zostanie cala tablica.
     * @return type
     */
    function fetchAll(&$result, array $aParams = array(), $result_type = MYSQL_ASSOC)
    {
        $aWynik = array();
        if(isset($aParams['key']))
        {
            while($aRow = mysql_fetch_array($result, $result_type))
            {
                $aVal = $aRow;
                if(isset($aParams['value']))
                {
                    $aVal = $aRow[$aParams['value']];
                }
                $aWynik[$aRow[$aParams['key']]] = $aVal;
            }
            return $aWynik;
        }
        while($aRow = mysql_fetch_array($result, $result_type))
        {
            $aWynik[] = $aRow;
        }
        return $aWynik;
    }
    
}