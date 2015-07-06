<?php
/**
 * Created by PhpStorm.
 * User: Meshaq
 * Date: 3/3/14
 * Time: 4:00 PM
 */

namespace System;


class Database {
    protected $dbHost;
    protected $dbName;
    protected $dbUser;
    protected $dbPassword;
    protected $dbConnection;
    protected $dbTable;
    protected $dbQuery;
    protected $dbConnect;
    public $status;
    protected $rows;

    public function __construct()
    {
        $this->status = $this->open();
    }
    
    public function set(){
        $this->dbHost = (DB_HOST);
        $this->dbName = (DB_NAME);
        $this->dbUser = (DB_USER);
        $this->dbPassword = (DB_PASSWORD);
    }

    public function open(){
        $this->set();
        $conn = mysql_connect($this->dbHost,$this->dbUser,$this->dbPassword);
        $con = mysql_select_db($this->dbName,$conn);
        if ($con){
            $this->dbConnect = true;
            $this->dbConnection = $con;
        }else{
           return mysql_error()."<br>";
        }
    }


    //close database connection
    public function close(){
        $closed = mysql_close();
        if ($closed){
            $this->dbConnect = false;
        }else{
            $this->dbConnect = true;
            echo mysql_error($this->dbConnection);
        }

    }

    public function query($query){
        $this->dbQuery = $query;

        return $this;
    }

    /*
     * Set the table of operation
     * @param String $table
     *
     * @return Object
     */
    public function table($table)
    {
        $this->dbTable = $table;

        return $this;
    }

    /*
     * Insert into table
     * @param array $columns
     */
    public function insert($columns)
    {
        $db = new Database();
        $keys = array_keys($columns);

        $values = array_values($columns);
        $string = 'INSERT INTO '.$this->dbTable.' ('.join(',',$keys).') VALUES ("' . implode('", "', $values) . '")';

        //die($string);

        $this->dbQuery = $string;
        $this->persist();


        return $db->table($this->dbTable)->find(['id'=>mysql_insert_id()]);
    }

    public function find($columns,$condition='AND',$limit=1)
    {
        $where = null;
        foreach($columns as $key => $value)
        {
            if (is_null($where)){
                $where .= "WHERE $key = '$value'";
            }else{
                $where .= " $condition $key = '$value'";
            }
        }

        $this->dbQuery = 'SELECT * FROM '.$this->dbTable.' '.$where.' LIMIT '.$limit;

        $result = $this->persist();

        $record =  $this->fetch($result);

        if (isset($record->scalar)){
            return false;
        }

        return $record;

    }


    /**
     * @param string $primaryKey
     * @param int|string $condition
     * @param array $updates
     * return boolean
     */
    public function update($primaryKey,$condition,$updates=array())
    {
        $string = null;
        foreach($updates as $key => $value)
        {
            if (is_null($string)){
                $string = "$key = '$value'";
            }else{
                $string .= ", $key = '$value'";
            }
        }

        $this->dbQuery = 'UPDATE '.$this->dbTable.' SET '.$string.' WHERE '.$primaryKey.' = "'.$condition.'"';
        //dump($this->dbQuery);
        $result = $this->persist();

        return $this->find([$primaryKey=>$condition]);
    }

    /**
     * @param string $column
     * @param int|string|array $values
     */

    public function delete($column,$values)
    {
        $string = null;
        if (is_array($values)) {
            $string = 'DELETE FROM ' . $this->dbTable . ' WHERE ' . $column . ' IN (' . join(',', $values).')';
        }else{
            $string = 'DELETE FROM ' . $this->dbTable . ' WHERE ' . $column . ' =  "'.$values.'"';
        }

        $this->dbQuery = $string;

        //dump($string);

        try{
            $this->persist();

            return true;
        } catch (\Exception $e){
            return $e->getMessage();
        }

    }

    public function get($limit = 30)
    {
        $this->dbQuery = 'SELECT * FROM '.$this->dbTable.' LIMIT '.$limit;

        $result = $this->persist();

        return $this->fetch($result);
    }

    public function persist(){
        $this->open();
        if($this->dbQuery != NULL || $this->dbQuery ==""){
            // $this->table($this->dbName);
            $doQuery = mysql_query($this->dbQuery) or die(mysql_error());
                if($doQuery){
                    return $doQuery;
                }else{
                    return "Error".mysql_errno()."".mysql_error();
                }

        }
    }

    public function fetch($arr){
        $rows = array();
        if ($this->rows($arr) > 1){
            while($row = mysql_fetch_assoc($arr)){
                $rows[] =  (object)$row;
            }
            return $rows;
        }else{
           $row = mysql_fetch_assoc($arr);
            return $this->rows =  (object)$row;
        }

        //return false;

    }

    public function count()
    {
        return count($this->rows);
    }

    public function flush(){
        unset($this);
    }

    public function getEntity($entity){
        $this->query("SELECT * FROM ".$entity."");
        $result = $this->persist();
        $entity = $this->fetch($result);

        return $entity;
    }
    public function getRawEntity($entity){
        $this->query("SELECT * FROM ".$entity."");
        $entity = $this->persist();

        return $entity;
    }

    public function searchEntity($entity,$term){
        $searchphrase = $term;
        $table = $entity;
        $sql_search = "select * from ".$table." where ";
        $sql_search_fields = Array();
        $sql = "SHOW COLUMNS FROM ".$table;
        $rs = mysql_query($sql);
        while($r = mysql_fetch_array($rs)){
            $colum = $r[0];
            $sql_search_fields[] = $colum." like('%".$searchphrase."%')";
        }

        $sql_search .= implode(" OR ", $sql_search_fields);
        $rs2 = $this->query($sql_search);
        $result = $this->persist();
        if ($this->rows($result)>0){
            return $result;
        }else{
            return false;
        }
    }
    public function getEntityById($entity,$id){
        $this->query("SELECT * FROM ".$entity." WHERE id='$id'");
        $result = $this->persist();
        $entity = $this->fetch($result);

        return $entity;
    }

    public function getEntityBy($entity,$col,$term){
        $this->query("SELECT * FROM ".$entity." WHERE ".$col."='$term'");
        $result = $this->persist();
        $entity = $this->fetch($result);

        return $entity;
    }
    public function getRawEntityBy($entity,$col,$term){
        $this->query("SELECT * FROM ".$entity." WHERE ".$col."='$term'");
        $result = $this->persist();

        return $result;
    }

    public function getEntityDetail($entity,$id,$col){
        $this->query("SELECT * FROM ".$entity." WHERE id='$id'");
        $result = $this->persist();

        while($entity = $this->fetch($result)){
            return $entity[$col];
        }
    }
    public function getId(){
        return mysql_insert_id();
    }

    public function rows($entities){
        return mysql_num_rows($entities);
    }

    
}