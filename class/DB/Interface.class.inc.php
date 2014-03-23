<?php
/**
 * Obsluga polaczenia z baza danych oraz zapytan SQL
 *
 * @package Database
 */
interface DB_Interface
{
    /**
     * Perform an SQL query of the given type.
     *
     *     // Make a SELECT query and use objects for results
     *     $db->query(1, 'SELECT * FROM groups', TRUE);
     *
     *     // Make a SELECT query and use "Model_User" for the results
     *     $db->query(1, 'SELECT * FROM users LIMIT 1', 'Model_User');
     *
     * @param   integer  SELECT: 1, INSERT: 2, UPDATE: 3,DELETE: 4
     * @param   string   SQL gotowe query
     * @param   mixed    result object class string, TRUE for stdClass, FALSE for assoc array
     * @param   array    object construct parameters for result class
     * @return  object   DB_Database_Result for SELECT queries
     * @return  array    list (insert id, row count) for INSERT queries
     * @return  integer  number of affected rows for all other queries
     */
    public function db_query($type, $sql, $as_object = FALSE, array $params = NULL);


    /**
     * Zwraca tresc ostatnio wykonanego zapytania sql
     */
    public function get_last_query();

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
    public function insert($table, $arFieldValues);

    /**
     * mysql_insert_id
     *
     * Zwraca identyfikator ostatnio wstawionego wiersza.
     *
     * @return int identyfikator ostatnio wstawionego wiersza
     */
    function insert_id();

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
    function delete($table, $tableField, $arConditions);

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
    public function update($table, $arFieldValues, $arConditions);
    
    public function fetch(&$result, $result_type);
    
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
    public function fetchAll(&$result, array $aParams = array(), $result_type);
 
    /**
     * Funkcja dopisuje znaki slasch "\" do znaków specjalnych
     * wywołując funkcję mysql_real_escape_string.
     *
     * @param <string> $str treść zapytania do modyfikacji.
     * @param <string> $db_link link do połączenia z bazą danych.
     * @return <string> zmodyfikowana treść zapytania.
     */
    public function DB_Database_real_escape_string($str);   
}