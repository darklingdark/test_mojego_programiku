<?php
/**
 * Mockup bazy danych dla testow PHPUnit
 */
class DB_Database_PhpUnit extends DB_Database
{
    /**
     * Tablica symulujaca $rResult
     * @var array
     */
    protected $result = null;
    
    /**
     * Wskaznik dla self::result
     * 
     * @var int
     */
    
    protected $resultIndex = 0;

    /**
     * Ustawienie tablicy symulujacej wyniki zapytania
     * @param array $aResult
     */
    public function setResult($aResult)
    {
        $this->result = $aResult;
        $this->resultIndex = 0;
    }
    
    /**
     * 
     * @inheritdoc
     */
    public function fetchAll(&$result, $result_type, array $aParams = array())
    {
        $this->result;
    }
    
    /**
     * 
     * @inheritdoc
     */
    public function fetch(&$result, $result_type)
    {
        if(isset($this->result[$this->resultIndex]))
        {
            $this->resultIndex ++;
            return $this->result[$this->resultIndex -1];
        }
        else
        {
            return null;
        }
    }

    public function count_affected_rows()
    {
        
    }

    public function connect()
    {
        
    }

    public function set_charset($param)
    {
        
    }

    public function list_tables($like = null)
    {
        
    }

    public function list_columns($table, $like = NULL, $add_prefix = TRUE)
    {
        
    }

    public function query($type, $sql, $as_object = FALSE, array $params = NULL)
    {
        
    }

    public function count_found_rows()
    {
        return count($this->result);
    }

    public function db_query($type, $sql, $as_object = FALSE, array $params = NULL)
    {
        
    }

}
