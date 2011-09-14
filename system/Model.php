<?php
class Model{
	public $errors;
	public $dbh = null;
	public $id = null;
	
	protected $name_tb;
	protected $fields;
	
	public function __construct($args = null){
		$vals = $args;
		if(get_class($args) == 'Request')
			$vals = array_merge($args->getParams(), $args->getCookie(), $args->getSession());
		
		if($args != null)
			foreach(array_keys(get_object_vars($this)) as $key)
				if($vals[$key] !== null)
					$this->$key = $vals[$key];
				
		GLOBAL $DB_HEADER;
		$this->dbh = clone $DB_HEADER;
		$this->errors = array();
		$this->init();
	}
	
	protected function init(){
	}
	
	public function create(){
		$q = 'INSERT INTO '.$this->name_tb.'(';
		$qe = ' VALUES(';	
		$param = array();
		foreach($this->fields as $key){
			if($this->$key === null){
				continue;
				$param[] = '';
			}
			else 
				$param[] = $this->$key; 
			
			$q .= $key.', ';
			$qe .= '?, ';
		}
		
		$q = substr($q, 0, strlen($q) - 2).')'.substr($qe, 0, strlen($qe) - 2).')';
		$this->dbh->insert($q, $param);
		
		$res = $this->dbh->fetchRow('SELECT * FROM '.$this->name_tb.' WHERE id = (SELECT MAX(id) FROM '.$this->name_tb.')');
		
		if($res === null)
			errorController::addError('INSER INTO '.$this->name_tb.' FAIL');
		else{
			$class = get_class($this);
			return new $class($res);
		}
	}
	
	public function save(){
		$q = "UPDATE $this->name_tb SET ";
		$param = array();
		
		foreach($this->fields as $key)
			if($this->$key !== null){
				$q .= $key."=?, ";
				$param[] = $this->$key;
			}
			
		$param[] = $this->id;
		$q = substr($q, 0, strlen($q) - 2)." WHERE id=?";
		$this->dbh->update($q, $param);
	}
	
	public function delete(){
		$this->dbh->delete('DELETE FROM '.$this->name_tb.' WHERE id = ?', array($this->id));
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
	/*
	public function get($args = null){
		$res = $this->query($args);
		$name = get_class($this);
		if(count($res) > 1)
			throw new Exception("method returned Get() in $name more than one object");
		elseif(count($res) == 0)
			return null;
			
		return new User($res[0]);
	}*/
	
	public function is_valid(){
		$valid = true;
		
		$methods = get_class_methods(get_class($this));
		$global_validate = array_search('is_valid_FIELD', $methods);
		
		foreach($this->fields as $name){
			$method_validate = 'is_valid_'.$name;
			$e = null;
			if(array_search($method_validate, $methods) !== false)
				$e = $this->$method_validate();
				
			if($e !== null){
				$this->errors[$name] = $e;
				$e = null;
			}
			if($global_validate !== false)
				$e = $this->is_valid_FIELD($name, $this->$name);
			
			if($e !==null)
				$this->errors[$name] = $e;
		}
				
		return count($this->errors) == 0;
	}
}