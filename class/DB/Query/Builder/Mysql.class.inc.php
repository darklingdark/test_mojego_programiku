<?php
/**
 * Klasa wspomagajaca tworzenie zapytan SQL
 * 
 * @package Database
 */

class DB_Query_Builder_Mysql extends DB_Query_Builder
{
    
    /**
     * @var  array  DB_Database instance
     */
    private $DB = NULL;
    
    
    /**
     * Konstruktor
     * 
     * @param type $sql
     * @param type $DB
     * @return \DB_Query_Builder_Mysql
     */
    function __construct($sql = NULL, $DB = NULL)
    {
        if(is_null($DB))
        {
            try
            {
                $this->DB = DB_Database::instance();
            }
            catch (Exception $e)
            {
                throw new Exception('DB_Query_Builder_Mysql could not get DB_Database instance.');
            }
        }

        if (!$sql)
        {
            return $this;
        }
        if (isset($sql['select']))
        {
            $this->_select = $sql['select'];
        }
        if (isset($sql['from']))
        {
            $this->_from = $sql['from'];
        }
        if (isset($sql['join']))
        {
            $this->_join = $sql['join'];
        }
        if (isset($sql['where']))
        {
            $this->_where = $sql['where'];
        }
        if (isset($sql['whereOr']))
        {
            $this->_whereOr = $sql['whereOr'];
        }
        if (isset($sql['limit']))
        {
            $this->_limit = $sql['limit'];
        }

        if (isset($sql['group']))
        {
            $this->_group = $sql['group'];
        }

        if (isset($sql['order']))
        {
            $this->_order = $sql['order'];
        }

        if (isset($sql['having']))
        {
            $this->_having = $sql['having'];
        }
           
        return $this;
    }

    /**
     * Stworzenie zapytania SQL na podstawie przygotowanej tablicy
     * 
     * @param bool $bAddCalcFoundROws - czy do zapytania dodajemy SQL_CALC_FOUND_ROWS
     * @return string
     */
    function query($bAddCalcFoundROws = true)
    {
        $query = 'SELECT ';
        
        if($bAddCalcFoundROws)
        {
            $query .= 'SQL_CALC_FOUND_ROWS ';
        }
        
        $query .= $this->_select() .
                ' FROM ' . $this->_from();

        $where = array();

        if (count($this->_join))
        {
            $where[] = $this->_join();
        }

        if (count($this->_where))
        {
            $where[] = $this->_where();
        }

        if (count($where))
        {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }
        
        if (count($this->_group))
        {
            $query .= $this->_group();
        
            if (count($this->_having))
            {
                $query .= $this->_having();
            }
        }
        
        if (count($this->_order))
        {
            $query .= $this->_order();
        }
        
        if(count($this->_limit))
        {
            $query .= ' LIMIT '.implode(', ', $this->_limit);
        }
        
        return $query;
    }

    function from($param)
    {
        return $this;
    }

    function where($param)
    {
        
    }
    
    /**
     * Przygotowanie sekcji SELECT zapytania SQL
     * 
     * @return string
     */
    protected function _select()
    {

        $part = array();

        foreach ($this->_select as $k => $v)
        {

            if (is_array($v))
            {
                foreach ($v as $n => $col)
                {
                    $part[] = $k . '.' . $col;
                }
            }
            else
            {
                $part[] = $v;
            }
        }

        if(0 < count($part))
        {
            return implode(', ', $part);
        }
        else
        {
            return ' * ';
        }
        
    }

    /**
     * Przygotowanie sekcji FROM zapytania SQL
     * 
     * @return string
     */
    
    protected function _from()
    {
        $part = array();
        
        foreach ($this->_from as $tab)
        {
            if (isset($tab[1]))
            {
                $part[] = $tab[0] . ' ' . $tab[1];
            }
            else
            {
                $part[] = $tab[0];
            }
        }

        return implode(', ', $part);
    }

    /**
     * Przygotowanie sekcji WHERE zapytania SQL
     * 
     * @return string
     */
    
    protected function _where()
    {

        $part = array();

        foreach ($this->_where as $v)
        {
            $part[] = $this->prepareWhere($v);
        }
        
        $partOr = array();
        
        if(count($this->_whereOr))
        {
            foreach ($this->_whereOr as $aOrParams)
            {
                $aOr = array();
                foreach ($aOrParams as $aOrPar)
                {
                    if(is_array($aOrPar))
                    {
                        $aOrExt = array();
                        foreach ($aOrPar as $aParOrExt)
                        {
                            $aOrExt[] = $this->prepareWhere($aParOrExt);
                        }
                        $aOr[] = '('.implode(' AND ', $aOrExt).')';
                    }
                    else
                    {
                        $aOr[] = $this->prepareWhere($aOrPar);
                    }
                }
                $partOr[] = '('.implode(' OR ', $aOr).')';
            }
        }
        
        $sWhere['and'] = implode(' AND ', $part);
        
        if(count($partOr) > 0)
        {
            $sWhere['or'] = implode(' AND ', $partOr);
        }
        
        return implode(' AND ', $sWhere);
    }

    /**
     * Funkcja do przygotowania parametrow przekazanych w where.
     * 
     * @param array $aParams w postacj:
     * <pre>
     * array(
     *      'kolumna', 'operacja', 'wartosc'
     * );
     * gdzie:
     * jezeli operacja = IN, jako wartosci przekazujemy tablice wartosci
     * jezeli operacja = LIKE%, jako wartosci przekazujemy string
     * </pre>
     * @return string
     */
    protected function prepareWhere($aParams)
    {
        
        switch ($aParams[1])
        {
            case self::LIKE_:
                $aParams[1] = 'LIKE';
                $aParams[2] = $aParams[2].'%';
                break;
            case self::_LIKE_:
                $aParams[1] = 'LIKE';
                $aParams[2] = '%'.$aParams[2].'%';
                break;
            case self::_LIKE:
                $aParams[1] = 'LIKE';
                $aParams[2] = '%'.$aParams[2];
                break;
            case 'NOT LIKE%':
                $aParams[1] = 'NOT LIKE';
                $aParams[2] = $aParams[2].'%';
                break;
            case '%NOT LIKE%':
                $aParams[1] = 'NOT LIKE';
                $aParams[2] = '%'.$aParams[2].'%';
                break;
            case '%NOT LIKE':
                $aParams[1] = 'NOT LIKE';
                $aParams[2] = '%'.$aParams[2];
                break;
            
        }        
        // dla zapytan typu 'IN'
        if(is_array($aParams[2]) && sizeof($aParams[2])>0)
        {
            //(( zabezpieczenie przed sql injection ((--
            $aParams[2] = $this->quote($aParams[2]);
            //)) zabezpieczenie przed sql injection ))--
        }
        elseif($aParams[2] instanceof DB_Database_Expression)
        {
            $aParams[2] = $aParams[2]->value();
        }
        else
        {

            if(isset($aParams[3]) && ('NO_APOSTROPHE_ENCLOED' == $aParams[3]))
            {
                //jesli uzyty zostal modyfikator 'NO_APOSTROPHE_ENCLOED',
                //oznacza to ze warunek budowany jest na wartosci klumny o zadanej nazwie,
                //a nie wartosci podanej bezposrednio - wtedy nie zamyka sie w apostrofach
                //i nie stosuje sie metody quote.
                $aParams[2] = $aParams[2];
                unset($aParams[3]); //usuwamy modyfikator, bo juz nie bedzie potrzebny.
            }
            else
            {
                $aParams[2] = $this->quote($aParams[2]);
            }
        }
        
        return implode(' ', $aParams);
    }

    /**
     * Przygotowanie sekcji zlaczen zapytania SQL
     * 
     * @return string
     */
    
    protected function _join()
    {
        $part = array();

        foreach ($this->_join as $v)
        {
            $part[] = $v[0] . ' = ' . $v[1];
        }

        return implode(' AND ', $part);
    }
    
    /**
     * Przygotowanie sekcji ORDER BY zapytania SQL
     * 
     * @return string
     */
    
    protected function _order()
    {
        return ' ORDER BY '.implode(', ', $this->_order);
    }
    
    /**
     * Przygotowanie sekcji GROUP BY zapytania SQL
     * 
     * @return string
     */
    
    protected function _group()
    {
        return ' GROUP BY '.implode(', ', $this->_group);
    }
    
    protected function _set()
    {
        $sql = array();
        
        foreach($this->_set as $v)
        {
            $sql[] = key($v)." = '".current($v)."'";
        }
        
        return implode(', ', $sql);
    }

    /**
     * Stara funkcja tworzaca zapytanie SQL
     * 
     * @deprecated 
     * @param type $select
     * @return int
     */
    public function select($select = array())
    {
        if (sizeof($aQuery_where) > 0)
        {
            $query_where = ' WHERE ';
            /** zmiany z dnia 11-25-2009: darek */
            foreach($aQuery_where as $query_where_key => $query_where_val)
            {
                if (!is_numeric($query_where_val))
                {
                    /** zmiany z dnia 11-25-2009: darek, zmiana z :
                     *  $query_where_val = "'" . mysql_real_escape_string($query_where_val,$this->db_link) . "'";   */
                    $query_where_val = "'" . $this->database_real_escape_string($query_where_val) . "'";
                }
            }
            /** koniec zmian */
            $query_where .= implode(' AND ', $aQuery_where);
        }
        else
        {
            $query_where = ' WHERE ';
            $query_where .= ' 1 ';
        }

        if (sizeof($aQuery_group) > 0)
        {
            $query_group = ' GROUP BY ';
            $query_group .= implode(',', $aQuery_group);
        }
        else
        {
            $query_group = '';
        }

        if (sizeof($aQuery_ord) > 0)
        {
            $query_ord = ' ORDER BY ';
            $query_ord .= implode(',', $aQuery_ord);
        }
        else
        {
            $query_ord = '';
        }

        if (sizeof($aQuery_lim) > 0)
        {
            $query_lim = ' LIMIT ';
            $query_lim .= implode(',', $aQuery_lim);
        }
        else
        {
            $query_lim = '';
        }

        $query = $query_sel . $query_from . $query_where . $query_group . $query_ord . $query_lim;

        //print('<br />'.$query.'<br />');

        $this->last_query = $query;

        return 0;
    }

    /**
     * Quote a value for an SQL query.
     *
     *     $db->quote(NULL);   // 'NULL'
     *     $db->quote(10);     // 10
     *     $db->quote('fred'); // 'fred'
     *
     * Objects passed to this function will be converted to strings.
     * [Database_Expression] objects will be compiled.
     * [Database_Query] objects will be compiled and converted to a sub-query.
     * All other objects will be converted using the `__toString` method.
     *
     * @param   mixed   any value to quote
     * @return  string
     * @uses    Database::escape
     */
    public function quote($value)
    {
        if ($value === NULL)
        {
            return 'NULL';
        }
        elseif ($value === TRUE)
        {
            return "'1'";
        }
        elseif ($value === FALSE)
        {
            return "'0'";
        }
        elseif (is_object($value))
        {
            if ($value instanceof Database_Query)
            {
                // Create a sub-query
                return '(' . $value->compile($this) . ')';
            }
            elseif ($value instanceof Database_Expression)
            {
                // Compile the expression
                return $value->compile($this);
            }
            else
            {
                // Convert the object to a string
                return $this->quote((string) $value);
            }
        }
        elseif (is_array($value))
        {
            return '(' . implode(', ', array_map(array($this, __FUNCTION__), $value)) . ')';
        }
        elseif (is_int($value))
        {
            return (int) $value;
        }
        elseif (is_float($value))
        {
            // Convert to non-locale aware float to prevent possible commas
            return sprintf('%F', $value);
        }

        return $this->escape($value);
    }

    /**
     * Sanitize a string by escaping characters that could cause an SQL
     * injection attack.
     *
     *     $value = $db->escape('any string');
     *
     * @param   string   value to quote
     * @return  string
     */
    public function escape($value)
    {
        return $this->DB->escape($value);
    }
    
    public function whereFromWeb()
    {
        
    }
    
    public function _having()
    {
        $part = array();

        foreach ($this->_having as $v)
        {
            switch ($v[1])
            {
                case self::NOT_IN:
                    $part[] = $v[0] . ' ' . $v[1] . " (" . $this->quote($v[2]) . ")";

                    break;
                case self::IN:
                    $part[] = $v[0] . ' ' . $v[1] . " (" . $this->quote($v[2]) . ")";

                    break;
                
                case self::LIKE_:
                    
                    $part[] = $v[0] . " LIKE " . $this->quote($v[2].'%') ;

                    break;
                case self::_LIKE_:

                    $part[] = $v[0] . " LIKE " . $this->quote('%'.$v[2].'%');

                    break;

                case self::_LIKE:
                    
                    $part[] = $v[0] . " LIKE " . $this->quote('%'.$v[2]);

                    break;

                default:
                    $part[] = $v[0] . ' ' . $v[1] . " " . $this->quote($v[2]) ;
                    break;
            }
        }
        return ' HAVING ' . implode(' AND ', $part);   
    }
    
    public function _limit()
    {
        
    }

    /**
     * Zapytanie DELETE
     * 
     * @return string
     */
    public function delete()
    {
        $sql = array();
        $sql[] = 'DELETE FROM';
        $sql[] = $this->_from();
        $sql[] = 'WHERE';
        $sql[] = '';

        if (count($this->_where))
        {
            $where[] = $this->_where();
        }

        if (count($where))
        {
            $sql[] = implode(' AND ', $where);
        }
        
        if (count($this->_order))
        {
            $sql[] = $this->_order();
        }
        
        if(count($this->_limit))
        {
            $sql[] = ' LIMIT '.implode(', ', $this->_limit);
        }
        
        return implode(' ', $sql);
        
    }
    
    /**
     * Zapytanie UPDATE
     * 
     * @return string
     */

    public function update()
    {
        $sql = array();
        $sql[] = 'UPDATE';
        $sql[] = $this->_from();
        $sql[] = 'SET';
        
        $sql[] = $this->_set();
        
        $sql[] = 'WHERE';
        $sql[] = '';

        if (count($this->_where))
        {
            $where[] = $this->_where();
        }

        if (count($where))
        {
            $sql[] = implode(' AND ', $where);
        }
        
        if (count($this->_order))
        {
            $sql[] = $this->_order();
        }
        
        if(count($this->_limit))
        {
            $sql[] = ' LIMIT '.implode(', ', $this->_limit);
        }
        
        return implode(' ', $sql);
    }
    
    /**
     * funkcja tworzaca zapytanie SQL update na podstawie przekazanych danych
     * 
     * @param array $aUpdate tablica z danymi do generowania zapytania update w postaci:<br />
     * <pre>
     * array(
     *      'table' => 'tablica do modyfikacji',
     *      'set' => array(
     *          'klucz' => 'wartosc',
     *          ...
     *      ),
     *      'where' => array(
     *          'klucz' => 'wartosc',
     *          ...
     *      )
     * )
     * </pre>
     * @param DB_Database $objDB polaczenie z baza danych do weryfikacji escape stringow
     * @return string gotowe zapytanie sql
     * 
     * @throws Exception
     */
    public function createUpdateQuery($aUpdate, DB_Database $objDB)
    {
        if (isset($aUpdate['where']) && is_array($aUpdate['where']) && sizeof($aUpdate['where']) > 0)
        {
            $aUpdate_where = $aUpdate['where'];
        }
        elseif (isset($aUpdate['WHERE']) && is_array($aUpdate['WHERE']) && sizeof($aUpdate['WHERE']) > 0)
        {
            $aUpdate_where = $aUpdate['WHERE'];
        }
        else
        {
            throw new Exception('brak parametrow where');
        }
        $query_where = ' WHERE ';
        /** zmiany z dnia 11-25-2009: darek */
        foreach ($aUpdate_where as $query_where_key => $query_where_val)
        {
            if (!is_numeric($query_where_val))
            {
                /** zmiany z dnia 11-25-2009: darek, zmiana z :
                 *  $query_where_val = "'" . mysql_real_escape_string($query_where_val,$this->db_link) . "'";   */
                $query_where_val = "'" . $objDB->DB_Database_real_escape_string($query_where_val) . "'";
            }
            $aUpdate_where[$query_where_key] = $query_where_key .'='.$query_where_val;
        }
        /** koniec zmian */
        $query_where .= implode(' AND ', $aUpdate_where);
        
        if (isset($aUpdate['set']) && is_array($aUpdate['set']) && sizeof($aUpdate['set']) > 0)
        {
            $aUpdate_set = $aUpdate['set'];
        }
        elseif (isset($aUpdate['SET']) && is_array($aUpdate['SET']) && sizeof($aUpdate['SET']) > 0)
        {
            $aUpdate_set = $aUpdate['SET'];
        }
        else
        {
            throw new Exception('brak parametrow set');
        }
        $query_set = ' SET ';
        /** zmiany z dnia 11-25-2009: darek */
        foreach ($aUpdate_set as $query_set_key => $query_set_val)
        {
            if (!is_numeric($query_set_val))
            {

                /** zmiany z dnia 11-25-2009: darek, zmiana z :
                 *  $query_where_val = "'" . mysql_real_escape_string($query_where_val,$this->db_link) . "'";   */
                $query_set_val = "'" . $objDB->DB_Database_real_escape_string($query_set_val) . "'";
            }
            $aUpdate_set[$query_set_key] = $query_set_key.'='.$query_set_val;
        }
        /** koniec zmian */
        $query_set .= implode(', ', $aUpdate_set);
        if (isset($aUpdate['table']) && '' != $aUpdate['table'])
        {
            $query_table = 'UPDATE ' . $aUpdate['table'];
        }
        elseif (isset($aUpdate['TABLE']) && '' != $aUpdate['TABLE'])
        {
            $query_table = 'UPDATE ' . $aUpdate['TABLE'];
        }
        else
        {
            throw new Exception('brak parametrow table');
        }

        $query = $query_table . $query_set . $query_where;

        return $query;
    }
    
    /**
     * funkcja tworzaca zapytanie SQL update na podstawie przekazanych danych
     * 
     * @param array $aDelete tablica z danymi do generowania zapytania update w postaci:<br />
     * <pre>
     * array(
     *      'table' => 'tablica z ktorej kasujemy',
     *      'where' => array(
     *          'klucz' => 'wartosc',
     *          ...
     *      )
     * )
     * </pre>
     * @param DB_Database $objDB polaczenie z baza danych do weryfikacji escape stringow
     * @return string gotowe zapytanie sql
     * 
     * @throws Exception
     */
    public function createDeleteQuery($aDelete, DB_Database $objDB)
    {
        if (isset($aDelete['where']) && is_array($aDelete['where']) && sizeof($aDelete['where']) > 0)
        {
            $aDeleteWhere = $aDelete['where'];
        }
        elseif (isset($aDelete['WHERE']) && is_array($aDelete['WHERE']) && sizeof($aDelete['WHERE']) > 0)
        {
            $aDeleteWhere = $aDelete['WHERE'];
        }
        else
        {
            throw new Exception('brak parametrow where');
        }
        
        $sqlQueryWhere = ' WHERE ';
        /** zmiany z dnia 11-25-2009: darek */
        foreach ($aDeleteWhere as $query_where_key => $query_where_val)
        {
            if (!is_numeric($query_where_val))
            {
                /** zmiany z dnia 11-25-2009: darek, zmiana z :
                 *  $query_where_val = "'" . mysql_real_escape_string($query_where_val,$this->db_link) . "'";   */
                $query_where_val = "'" . $objDB->DB_Database_real_escape_string($query_where_val) . "'";
            }
            $aDeleteWhere[$query_where_key] = $query_where_key .'='.$query_where_val;
        }
        /** koniec zmian */
        $sqlQueryWhere .= implode(' AND ', $aDeleteWhere);
        
        if (isset($aDelete['table']) && '' != $aDelete['table'])
        {
            $sqlDelete = 'DELETE FROM ' . $aDelete['table'];
        }
        elseif (isset($aDelete['TABLE']) && '' != $aDelete['TABLE'])
        {
            $sqlDelete = 'DELETE FROM ' . $aDelete['TABLE'];
        }
        else
        {
            throw new Exception('brak parametrow table');
        }

        $query = $sqlDelete . $sqlQueryWhere;

        return $query;
    }

 
    /**
     * funkcja tworzaca zapytanie SQL update na podstawie przekazanych danych
     * 
     * @param array $aInsert tablica z danymi do generowania zapytania update w postaci:<br />
     * <pre>
     * array(
     *      'table' => 'tablica lub tablice np.:"auto_o", lub "auto_o auo, klient kli"',
     *      'values' => array(
     *          'klucz (pamietac od przedrostkach!!!)' => 'wartosc',
     *          ...
     *      ),
     * )
     * </pre>
     * @param DB_Database $objDB polaczenie z baza danych do weryfikacji escape stringow
     * @return string gotowe zapytanie sql
     * 
     * @throws Exception
     */
    public function createInsertQuery($aInsert, DB_Database $objDB)
    {
        if (isset($aInsert['values']) && is_array($aInsert['values']) && sizeof($aInsert['values']) > 0)
        {
            $aValues = $aInsert['values'];
        }
        elseif (isset($aInsert['VALUES']) && is_array($aInsert['VALUES']) && sizeof($aInsert['VALUES']) > 0)
        {
            $aValues = $aInsert['VALUES'];
        }
        else
        {
            throw new Exception('brak parametrow values');
        }
        $query_Columns = array();
        $query_Values = array();
        $iLicznik = 0;
        foreach ($aValues as $query_col_key => $query_val)
        {
            $query_Columns[$iLicznik] = $query_col_key;
            if (!is_numeric($query_val))
            {
                $query_val = "'" . $objDB->DB_Database_real_escape_string($query_val) . "'";
            }
            $query_Values[$iLicznik] = $query_val;
            $iLicznik++;
        }
        $query_values = ' (' . implode(', ', $query_Columns) . ') VALUES (' . implode(', ', $query_Values) . ')'; ;
        
        if (isset($aInsert['table']) && '' != $aInsert['table'])
        {
            $query_table = 'INSERT INTO ' . $aInsert['table'] . ' ';
        }
        elseif (isset($aInsert['TABLE']) && '' != $aInsert['TABLE'])
        {
            $query_table = 'INSERT INTO ' . $aInsert['TABLE'] . ' ';
        }
        else
        {
            throw new Exception('brak parametrow table');
        }

        $query = $query_table . $query_values;

        return $query;
    }
 
    /**
     * funkcja tworzaca zapytanie SQL update na podstawie przekazanych danych
     * 
     * @param array $aReplace tablica z danymi do generowania zapytania update w postaci:<br />
     * <pre>
     * array(
     *      'table' => 'tablica lub tablice np.:"auto_o", lub "auto_o auo, klient kli"',
     *      'values' => array(
     *          'klucz (pamietac od przedrostkach!!!)' => 'wartosc',
     *          ...
     *      ),
     * )
     * </pre>
     * @param DB_Database $objDB polaczenie z baza danych do weryfikacji escape stringow
     * @return string gotowe zapytanie sql
     * 
     * @throws Exception
     */
    public function createReplaceQuery($aReplace, DB_Database $objDB)
    {
        if (isset($aReplace['values']) && is_array($aReplace['values']) && sizeof($aReplace['values']) > 0)
        {
            $query_Columns = array();
            $query_Values = array();
            $iLicznik = 0;
            foreach ($aReplace['values'] as $query_col_key => $query_val)
            {
                $query_Columns[$iLicznik] = $query_col_key;
                if (!is_numeric($query_val))
                {
                    $query_val = "'" . $objDB->DB_Database_real_escape_string($query_val) . "'";
                }
                $query_Values[$iLicznik] = $query_val;
                $iLicznik++;
            }
            $query_values = ' (' . implode(', ', $query_Columns) . ') VALUES (' . implode(', ', $query_Values) . ')'; ;
        }
        else
        {
            throw new Exception('brak parametrow values');
        }
        if (isset($aReplace['table']) && '' != $aReplace['table'])
        {
            $query_table = 'REPLACE ' . $aReplace['table'] . ' ';
        }
        else
        {
            throw new Exception('brak parametrow table');
        }

        $query = $query_table . $query_values;

        //print('<br />'.$query.'<br />');

        $this->last_query = $query;

        return $query;
    }
}
