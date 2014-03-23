<?php
class Query_Result
{
    protected $DB;
    
    public function __construct($DB)
    {
        $this->DB = $DB;
    }
    
    /**
     * lista placowek z mapowania
     * 
     * @return string
     * @throws Exception
     */
    public function placowkiList()
    {
        $QueryBuilderPlacowla = DB_Query_Builder::factory(
                DB_Query_Builder::type_mysql, 
                 Query_Sql::placowkiList()
        );

        try
        {
            $rResult = $this->DB->query(DB_Database::SELECT, $QueryBuilderPlacowla->query());
        }
        catch (Exception $ex)
        {
            throw New Exception('L:' . basename(__FILE__) . '(' 
                    . __LINE__ . '): Błąd dodawania paczki sprawozdan do bazy danych: ' 
                    . $ex->getMessage(), $ex->getCode());
        }

        $aPlacowki = $this->DB->fetchAll($rResult, ['key' => 'id_slj', 'value' => 'slj_nazwa']);
        return $aPlacowki;
    }
    
    /**
     * lista placowek z mapowania
     * 
     * @return string
     * @throws Exception
     */
    public function typSprawozdaniaList()
    {
        $aQuery = [
            'select' => [
                'DISTINCT spr.spr_nazwa'
            ],
            'from' => [
                ['paczka_sprawozdan', 'psp'],
                ['placowka', 'plc'],
                ['sprawozdanie', 'spr'],
            ],
            'join' => [
                ['plc.id_psp', 'psp.id_psp'],
                ['spr.id_plc', 'plc.id_plc'],
            ],
            'where' => [
                ['psp_s', '=', 'N'],
            ],
        ];
        $QueryBuilder = DB_Query_Builder::factory(
                DB_Query_Builder::type_mysql, 
                 $aQuery
        );

        try
        {
            $rResult = $this->DB->query(DB_Database::SELECT, $QueryBuilder->query());
        }
        catch (Exception $ex)
        {
            throw New Exception('L:' . basename(__FILE__) . '(' 
                    . __LINE__ . '): Błąd pobrania listy sprawozdan z bazy danych: ' 
                    . $ex->getMessage(), $ex->getCode());
        }

        return $this->DB->fetchAll($rResult, ['key' => 'spr_nazwa', 'value' => 'spr_nazwa']);
    }
    
    /**
     * lista placowek z mapowania
     * 
     * @return string
     * @throws Exception
     */
    public function rokList()
    {
        $aQuery = [
            'select' => [
                'DISTINCT psp.psp_rok'
            ],
            'from' => [
                ['paczka_sprawozdan', 'psp'],
            ],
            'where' => [
                ['psp_s', '=', 'N'],
            ],
        ];
        $QueryBuilder = DB_Query_Builder::factory(
                DB_Query_Builder::type_mysql, 
                 $aQuery
        );

        try
        {
            $rResult = $this->DB->query(DB_Database::SELECT, $QueryBuilder->query());
        }
        catch (Exception $ex)
        {
            throw New Exception('L:' . basename(__FILE__) . '(' 
                    . __LINE__ . '): Błąd pobrania listy rrocznikow z bazy danych: ' 
                    . $ex->getMessage(), $ex->getCode());
        }

        return $this->DB->fetchAll($rResult, ['key' => 'psp_rok', 'value' => 'psp_rok']);
    }

}