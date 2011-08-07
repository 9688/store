<?php
class Model{
	public $errors;
	public $dbh;
	protected $primary_keys;
	protected $name_tb;
	public $id;
	
	public function __construct($args = null){
		$vals = $args;
		if(get_class($args) == 'Request')
			$vals = array_merge($args->getParams(), $args->getCookie(), $args->getSession());
		
		if($args != null)
			foreach(array_keys(get_object_vars($this)) as $key)
				$this->$key = $vals[$key];
				
		GLOBAL $DB_HEADER;
		$this->dbh = $DB_HEADER;
		$this->errors = array();
		$this->init();
	}
	
	public function init(){
	}
	
	public function query($param = null){
		$q = 'SELECT * FROM '.$this->name_tb.' WHERE ';
		if($param == null)
			$args = array_keys(get_object_vars($this));
		else
			$args = $param;
			
		$param = array();
			
		foreach($this->primary_keys as $i => $key)
			if($args[$key] != null){
				$param[] = $args[$key];
				$q .= ' '.$key.' = ? OR';
			}
		$q = substr($q, 0, strlen($q) - 3);
			
		if(count($param) == 0)
			return array();
			
		return $this->dbh->fetchAll($q, $param);
	}
	
	public function get($args = null){
		$res = $this->query($args);
		$name = get_class($this);
		if(count($res) > 1)
			throw new Exception("method returned Get() in $name more than one object");
		elseif(count($res) == 0)
			return null;
			
		return new User($res[0]);
	}
	
	public function is_valid(){
		$valid = true;
		
		$methods = get_class_methods(get_class($this));
		
		foreach(array_keys(get_object_vars($this)) as $key){
			$method_validate = 'is_valid_'.$key; 
			if(array_search($method_validate, $methods) !== false && !$this->$method_validate())
				$valid = false;
		}
				
		return $valid;
	}
}