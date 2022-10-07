<?php

class MysqlAdapter
{
    protected $_config=array();
    protected $_link;
    protected $_result;
    /*
     * constructor
     *  */
    public function __construct(array $config){
        if(count($config)!==4){
            throw new InvalidArgumentException('invalid number of connection parameters.');
        }
        $this->_config=$config;
    }

    /*
     * connect to MYSQL
     * */
    public function connect(){
        /*
         * connect only one
         * single tone design pattern
         * */
        if ($this->_link===null){
            //echo ($this->_config)? "malyana" : "fadya";
            list($host,$user,$password,$database)=$this->_config;
            if (!$this->_link=@mysqli_connect($host,$user,$password,$database)){
                throw new RuntimeException('error connecting to the server : '. mysqli_connect_error());

            }
            unset($host,$user,$password,$database);
        }
        return $this->_link;
    }
    /*
     * Execute the specified query
     * */
    public function query($query){
        if (!is_string($query) || empty($query)){
            throw new InvalidArgumentException('The specified query is not valid.');
        }
        //lazy connect to MYSQL
        $this->connect();
        if (!$this->_result=mysqli_query($this->_link,$query)){
            throw new RuntimeException('Error executing the specified query '. $query. mysqli_error($this->_link));
        }

        return $this->_result;
    }
    /*
     * perform a select statement
     * */
    public function select($table,$where='',$fields='*',$order='',$limit=null,$offset=null){
        $query ='SELECT '. $fields . ' FROM ' . $table
        . (($where) ? ' WHERE ' . $where:'')
            . (($limit) ? ' LIMIT ' . $limit:'')
            .(($offset && $limit) ? ' OFFSET ' . $offset:'')
            .(($order) ? ' ORDER BY ' . $order:'');
        $this->query($query);
        return $this->countRows();
    }
    /*
     * perform an insert statement
     * */
    public function insert($table,array $data){
        $fields=implode(',',array_keys($data));
        $values=implode(',',array_map(array($this,'qouteValue'), array_values($data)));
        $query = 'INSERT INTO ' . $table . ' ('. $fields .') '.' VALUES ('. $values .')';
        $this->query($query);
        return $this->getInsertId();
    }
    /*
     * perform an update statement
     * */
    public function update($table,array $data,$where=''){
        $set=array();
        foreach ($data as $field => $values){
            $set[]=$field . '=' . $this->qouteValue($values);
        }
        $set=implode(',',$set);
        $query='UPDATE ' . $table . ' SET '. $set
            . (($where)? ' WHERE ' . $where:'');
        $this->query($query);
        return $this->getAffectedRows();
    }
    /*
     * perform an delete statement
     * */
    public function delete($table,$where=''){
        $quary='DELETE FROM '. $table
            . (($where)? ' WHERE ' . $where:'');
        $this->query($quary);
        return $this->getAffectedRows();
    }
    /*
     * fetch a single row from the current result set (as an associative array)
     * */
    public function fetch(){
        if($this->_result!== null) {
            if (($row = mysqli_fetch_array($this->_result, MYSQLI_ASSOC)) === false) {
                $this->freeResults();
            }
            return $row;
        }
        return false;
    }
    /*
    * fetch a single row from the current result set (as an associative array)
    * */
    public function fetchAll(){
        if($this->_result!== null) {
            if (($all = mysqli_fetch_all($this->_result, MYSQLI_ASSOC)) === false) {
                $this->freeResults();
            }
            return $all;
        }
        return false;
    }
    /*
     * get the insertion id
     * */
    public function getInsertId(){
        return $this->_link !== null
            ?mysqli_insert_id($this->_link):null;
    }
    /*
     * free results
     * */
    public function freeResults()
    {
        mysqli_free_result($this->_result);
    }
    /*
     * get the number of rows returned by the current result set
     * */
    public function countRows(){
        return $this->_result!== null
            ? mysqli_num_rows($this->_result) : 0;
    }
    /*
     * get number of affected rows */
    public function getAffectedRows(){
        return $this->_link !== null
            ?mysqli_affected_rows($this->_link):0;
    }
    /*
     * escape the specified value
     * */
    public function qouteValue($value){
        $this->connect();
        if ($value===null){
            $value='Null';
        }elseif (!is_numeric($value)){
            $value="'" . mysqli_real_escape_string($this->_link,$value) ."'";
        }
        return $value;
    }
    public function getResult(){
        return $this->_result;
    }
    /*
     * close explicitly the database connection
     * */
    public function disconnect(){
        if($this->_link===null){
            return false;
        }
        mysqli_close($this->_link);
        $this->_link=null;
        return true;
    }

    public function __destruct(){
        $this->disconnect();
    }

}
