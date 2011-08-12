<?php
class Db_adapter {
	const FETCH_ASSOC = PDO::FETCH_ASSOC;
	const FECTH_NUM = PDO::FETCH_NUM;
	const FETCH_BOTH = PDO::FETCH_BOTH;
	const FETCH_COLUMN = PDO::FETCH_COLUMN;
	const FETCH_OBJ = PDO::FETCH_OBJ;
	
	private $host;
	private $username;
	private $password;
	private $dbname;
	private $db;
	private $fetchmode;
	private $connection;
	
	function __construct($param = array()){
		if(count($param) == 0)
			$param = array(
				'host' => DB_HOST,
				'username' => DB_USERNAME, 
				'password' => DB_PASSWORD, 
				'dbname' => DB_NAME, 
				'db' => DB_TYPE
			);
					
		$this->host = $param ['host'];
		$this->username = $param ['username'];
		$this->password = $param ['password'];
		$this->dbname = $param ['dbname'];
		$this->db = $param ['db'];
		$this->fetchmode = Db_adapter::FETCH_ASSOC;
		$this->connection = null;
	}
	
	private function init() {
		if ($this->connection != null)
			return;
		
		$dsn = $this->db . ':' . 'dbname=' . $this->dbname . ';host=' . $this->host;
		
		try {
			$this->connection = new PDO ( $dsn, $this->username, $this->password );
		} catch ( Exception $e ) {
			throw $e;
		}
	}
	
	function setFetchMode($fetchmode) {
		$this->fetchmode = $fetchmode;
	}
	
	function getConnection() {
		$this->init ();
		return $this->connection;
	}
	
	function fetchAll($query, $param=null) {
		return $this->fetch ( $query, $param, $this->fetchmode );
	}
	
	function fetchAssoc($query, $param=null) {
		return $this->fetch ( $query, $param, Db_adapter::FETCH_ASSOC );
	
	}
	
	function fetchCol($query, $param=null) {
		$res = $this->fetch ( $query, $param, Db_adapter::FETCH_COLUMN );
		$keys = array_keys ( $res );
		
		return $res [$keys [0]];
	}
	
	function fetchRow($query, $param=null) {
		$res = $this->fetch ( $query, $param, $this->fetchmode );
		$keys = array_keys ( $res );
		
		return $res [$keys [0]];
	}
	
	private function query($query, $param){
		$this->init();
		if($param == null)
			$param = array();
		
		$sth = $this->connection->prepare($query);
		$sth->execute($param);
		
		return $sth;
	}
	
	public function fetch($query, $param=null, $fetchmode) {
		$sth = $this->query ( $query, $param );
		$res = $sth->fetchAll ( $this->fetchmode );
		return $res;
	}
	
	function insert($query, $param) {
		$this->query ( $query, $param );
	}
	
	function update($query, $param) {
		$this->query ( $query, $param );
	}
	
	function delete($query, $param){
		$this->query($query, $param);
	}
}