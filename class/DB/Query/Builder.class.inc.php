<?php
/**
 * Klasa wspomagajaca tworzenie zapytan SQL
 * 
 * @package Database
 */

abstract class DB_Query_Builder
{
    const type_mysql = 'Mysql';
    const NOT_IN = 'NOT IN';
    const IN = 'IN';
    const LIKE_ = 'LIKE%';
    const _LIKE = '%LIKE';
    const _LIKE_ = '%LIKE%';
    
    /**
     * Dane dla sekcji SELECT
     * 
     * @var array 
     */
    protected $_select = array();
    
    /**
     * Dane dla sekcji FROM
     * 
     * @var array 
     */

    protected $_from = array();
    
    /**
     * Dane dla zlaczen
     * 
     * @var array 
     */
    
    protected $_join = array();
    
    /**
     * Dane dla sekcji WHERE zawierajace wszystkie sekcje AND
     * 
     * @var array 
     */
    
    protected $_where = array();
    
    /**
     * Dane dla sekcji WHERE zawierajace wszystkie sekcje OR
     * 
     * @var array 
     */
    
    protected $_whereOr = array();
    
    /**
     * Dane dla sekcji GROUP
     * 
     * @var array 
     */
    
    protected $_group = array();
    
    /**
     * Dane dla sekcji ORDER
     * 
     * @var array 
     */
    
    protected $_order = array();
    
    /**
     * Dane dla sekcji LIMIT
     * 
     * @var array 
     */
    
    protected $_limit = array();
    
    /**
     * Dane dla sekcji HAVING
     * 
     * @var array 
     */
    
    protected $_having = array();
    
    /** 
     * Dane dla sekcji SET
     * 
     * @var array 
     */
    
    protected $_set = array();
    
    /**
     * Zwraca obiect klasy odpowiedzialnej za budowe zapytan np dla mysql
     *
     * @param string $type rodzaj bazy danych np "mysql"
     * @param string $sql zapytanie do wykonania
     * @return \DB_Query_Builder_Mysql 
     */
    static function factory($type, $sql = NULL)
    {
        $class = 'DB_Query_Builder_'.ucfirst($type);
        
        return new $class($sql);
    }
    
    /**
     * Pobranie zapytania w postaci tablicy
     * 
     * @return array
     */
    function getAsArray()
    {
        return array(
            'select' => $this->_select,
            'from' => $this->_from,
            'join' => $this->_join,
            'where' => $this->_where,
            'group' => $this->_group,
            'order' => $this->_order,
            'limit' => $this->_limit,
            'having' => $this->_having,
            'set' => $this->_set,
        );
    }
    
    /**
     * Ustawienie warunkow WHERE
     * 
     * $where = array(
     *      array('eks.eks_wykonaj', '=', 'N'),
     *      array('eks.eks_aktywny', '=', 'Y'),
     * );
     * 
     * $Builder->setWhere($where)
     * 
     * @param array $where
     * @return \DB_Query_Builder
     */
    function setWhere($where = array())
    {
        $this->_where = $where;
        
        return $this;
    }
    
    /**
     * Ustawienie listy select dla zapytania
     * 
     * $aSelect = array(
     *      array('eks' => array(
     *          'eks_wykonaj'
     *      ),
     * );
     * 
     * $Builder->setSelect($aSelect)
     * 
     * @param array $aSelect
     * @return \DB_Query_Builder
     */
    function setSelect(array $aSelect = array())
    {
        $this->_select = $aSelect;
        
        return $this;
    }
    
    /**
     * Ustawienie listy from dla zapytania
     * 
     * $aFrom = array(
     *      array('eksport', 'eks'),
     * );
     * 
     * $Builder->setFrom($aFrom)
     * 
     * @param array $aFrom
     * @return \DB_Query_Builder
     */
    function setFrom(array $aFrom = array())
    {
        $this->_from = $aFrom;
        
        return $this;
    }
    
    /**
     * Dodawanie warunku WHERE
     * 
     * $where = array('eks.eks_wykonaj', '=', 'N');
     * 
     * $Builder->addWhere($where)
     * 
     * @param array $where
     * @return \DB_Query_Builder
     */
    function addWhere($where)
    {
        //$this->_where = array_merge($this->_where, $where);
        if(count($where))
        {
            $this->_where[] =  $where;
        }
        
        return $this;
    }

    
    /**
     * Dodawanie tablicy warunku WHERE
     * (przydatne do dopisania warunkow okreslonych
     *  w fullSearchConditions, gdy w zapytaniu byly
     *  juz wczesiniej okreslone warunki WHERE i nie
     *  da sie uzyc metody setWhere (bo by nadpisala
     *  wczesniej zdefiniowane warunki)
     * 
     * $where = array(
     *   array('eks.eks_wykonaj', '=', 'N'),
     *   array('auo.auo_data_wpr' '>=', '2000-01-01')
     * )
     * 
     * $Builder->addWhere($aWhere)
     * 
     * @param array $aWhere
     * @return \DB_Query_Builder
     */
    function addWhereArray($aWhere)
    {
        if(sizeof($aWhere) > 0)
        {
            foreach($aWhere as $where)
            {
                if(count($where))
                {
                    $this->_where[] =  $where;
                }
            }
        }
        
        return $this;
    }    
    
    /**
     * Usuniecie z warunku WHERE
     *
     * $where = array('eks.eks_wykonaj', '=', 'N');
     *
     * $Builder->removeWhere($where)
     *
     * @param array $where
     * @return \DB_Query_Builder
     */
    function remoweWhere($where)
    {
        foreach ($this->_where as $k => $v)
        {
            if($v == $where)
            {
                unset($this->_where[$k]);
            }
        }

        return $this;
    }
    
    /**
     * Dodawanie warunku LIMIT
     * 
     * $where = array(100);
     * 
     * $Builder->setLimit($limit)
     * 
     * @param mixed $limit
     * @return \DB_Query_Builder
     */
    function setLimit($limit)
    {

        //$this->_where = array_merge($this->_where, $where);
        if(is_numeric($limit))
        {
            $this->_limit = array($limit);
        }
        elseif((is_array($limit) && count($limit) > 0))
        {
            $this->_limit = $limit;
        }
        
        return $this;
    }
    
    /**
     * Ustawienie listy join dla zapytania
     * 
     * $aJoin = array(
     *      array('eks.id_kli', 'kli.id_kli'),
     * );
     * 
     * $Builder->setJoin($aJoin)
     * 
     * @param array $aJoin
     * @return \DB_Query_Builder
     */
    function setJoin(array $aJoin = array())
    {
        $this->_join = $aJoin;
        
        return $this;
    }
    
    /**
     * Dodawanie prostego warunku typu JOIN
     * 
     * $join = array('eks.id_eks', 'aui.id_eks');
     * 
     * $Builder->addJoin($join)
     * 
     * @param array $join
     * @return \DB_Query_Builder
     */

    function addJoin($join)
    {
        $this->_join[] = $join;
        return $this;
    }

    /**
     * Dodanie tablicy do warunku FROM
     * 
     * $from = array('auto_o', 'auo');
     * 
     * $Builder->addFrom($from)
     * 
     * @param array $from
     * @return \DB_Query_Builder
     */
    
    function addFrom($from)
    {
        $this->_from[] = $from;
        return $this;
    }
    
    /**
     * Dodanie pol do warunku SET
     * 
     * $set = array('kolumna' => 'value');
     * 
     * $Builder->addSet($set)
     * 
     * @param array $set
     * @return \DB_Query_Builder
     */
    
    function addSet($set)
    {
        $this->_set[] = $set;
        return $this;
    }
    
    /**
     * Przebudowanie sekcji order by
     * 
     * $aOrder1 = array('eks_wykonaj','id_eks');
     * $aOrder2 = array('eks_wykonaj ASC','id_eks DESC');
     * 
     * $Builder->setOrder($aOrder2)
     * 
     * @param mixed $order
     * @return \DB_Query_Builder
     */
    function setOrder(array $order = array())
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Dodanie warunku ORDER BY
     * 
     * $Builder->addOrder('id_auo ASC');
     * 
     * @param string $order
     * @return \DB_Query_Builder 
     */
    function addOrder($order)
    {
        $this->_order[] = $order;
        return $this;
    }
    
    /**
     * Dodanie kolumny do zbioru danych do pobrania (SELECT)
     *
     * $select = array(
     *     'auo' => array(
     *         'auo_wer_mod',
     *         'auo_wer_wyp' 
     *     )
     * );
     * 
     * $select = array("DATE_FORMAT(auo.auo_data_przeglad,'%Y-%m-%d') as 'auo_data_przeglad'");
     * 
     * $Builder->addSelect($select)
     * 
     * @param array $select tablica kolumn
     * @return \DB_Query_Builder 
     */
    
    function addSelect($select)
    {
        foreach($select as $prefix => $columns)
        {
            if(is_array($columns))
            {
                if(!isset($this->_select[$prefix]))
                {
                    $this->_select[$prefix] = array();
                }
                $this->_select[$prefix] = array_merge($this->_select[$prefix], $columns);
            }
            else
            {
                $this->_select[] = $columns;

            }
        }
        return $this;
    }
    
    /**
     * 
     * @param array $param w postaci tablicy (w tablicy pokazano mozliwe 
     * wywolania ktore mozna laczyc):
     * <pre>
     *&emsp; array(
     *&emsp;    array(
     *&emsp;      array('kolumn1', 'operation', 'value1'),
     *&emsp;    ),
     *&emsp;    array(
     *&emsp;      array(
     *&emsp;          array('kolumn2', 'operation', 'value2'),
     *&emsp;          array('kolumn3', 'operation', 'value3'),
     *&emsp;      ),
     *&emsp;    ),
     *&emsp; )
     *&emsp; <b>np.: przekazujac ponizsza tablice jako parametr</b>
     *&emsp; array(
     *&emsp;    array(
     *&emsp;        array('id_kli', '=', '10'),
     *&emsp;    ),
     *&emsp;    array(
     *&emsp;        array(
     *&emsp;            array('id_adr', '>', '10'),
     *&emsp;            array('id_adr', '<', '6'),
     *&emsp;        ),
     *&emsp;    ),
     *&emsp; )
     *&emsp; <b>Jako wynik otrzymamy w query():</b>
     *&emsp; where
     *&emsp;    (id_kli = 10) 
     *&emsp; or ( 
     *&emsp;         (id_adr > 10) 
     *&emsp;     and (id_adr < 6) 
     *&emsp; )
     * </pre>
     *
     * @return \DB_Query_Builder_Mysql
     */
    function addOr($param)
    {
        $this->_whereOr[] = $param;
        return $this;
    }
    
    /**
     * Przebudowanie sekcji group by
     * 
     * $aGroup = array('eks_wykonaj','id_eks);
     * 
     * $Builder->setGroup($aGroup)
     * 
     * @param mixed $group
     * @return \DB_Query_Builder
     */
    function setGroup(array $group = array())
    {
        $this->_group = $group;
        return $this;
    }
    
    /**
     * Dodawanie do sekcji group by
     * 
     * $Builder->addGroup('eks_wykonaj')
     * 
     * @param mixed $group
     * @return \DB_Query_Builder
     */
    function addGroup($group)
    {
        $this->_group[] = $group;
        return $this;
    }
    
    /**
     * Przebudowanie sekcji having
     * 
     * $aHaving = array('eks_wykonaj','=','N');
     * 
     * $Builder->setHaving($aHaving)
     * 
     * @param mixed $having
     * @return \DB_Query_Builder
     */
    function setHaving(array $having = array())
    {
        $this->_having = $having;
        return $this;
    }
    
    /**
     * Dodawanie do sekcji having
     * 
     * $Builder->addHaving(array('eks_wykonaj','=','N'))
     * 
     * @param mixed $having
     * @return \DB_Query_Builder
     */
    function addHaving($having)
    {
        $this->_having[] = $having;
        return $this;
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
    abstract public function createUpdateQuery($aUpdate, DB_Database $objDB);
    
    
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
    abstract public function createInsertQuery($aInsert, DB_Database $objDB);

    /**
     * Stworzenie zapytania SQL na podstawie przygotowanej tablicy
     * 
     * @return string
     */
    
    abstract function query();

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
        
    }
    
    public function whereFromWeb()
    {
        
    }
    
    abstract protected function _select();

    abstract protected function _from();

    abstract protected function _where();

    abstract protected function _join();
    
    abstract protected function _order();
    
    abstract protected function _group();
    
    abstract protected function _limit();
    
    abstract protected function _having();
    
    abstract protected function _set();

    abstract public function delete();

    abstract public function update();
}
