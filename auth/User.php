<?php
class User extends Model{
	const ADMIN = 3;
	const MODER = 2;
	const BUYER = 1;
	const ANONYM = 0;
	const REGISTERED = -1;
	
	public $id;
	public $sid;
	public $login;
	public $password;
	public $level_access;
	public $profile;
	public $repeatpassword;
	public $is_auth;
	
	public function init(){
		$this->primary_keys = array('id', 'sid', 'login');
		$this->name_tb = 'users';
		$this->is_auth = false;
	}
	
	public function is_valid(){
		$this->errors = array();
		
		if(strlen($this->login) == 0)
			$this->errors['login'] = 'Поле не должно быть пусто.';
		elseif(!preg_match('/^[A-Za-z0-9_.-]+$/', $this->login))
			$this->errors['login'] = 'Допускаются только латинские буквы (a-z), цифры(0-9), точка(.), минус(-) и знак подчеркивания(_).';
		elseif(strlen($this->login) > 30 || strlen($this->login) < 4)
			$this->errors['login'] = 'Логин должен быть от 4 до 30 символов.';
		else{
			$user = $this->get(array('login' => $this->login));
			if($user != null)
				$this->errors['login'] = 'Пользователь с таким логином уже существует.';
		}
		
		if(strlen($this->password) == 0)
			$this->errors['password'] = 'Поле не должно быть пусто.';
		elseif(strlen($this->password) < 4)
			$this->errors ['password'] = 'Пароль должен быть не менее 4 символов.';
		elseif(strcmp($this->password, $this->repeatpassword))
			$this->errors ['password'] = 'Введенные пароли не совпадают';
			
		return count($this->errors) == 0;
	}
	
	public function create(){
		$param = array($this->sid, $this->login, sha1($this->password), $this->level_access);
		$this->dbh->insert('INSERT INTO users(sid, login, password, level_access) VALUES (?, ?, ?, ?)', $param);
	}
	
	public function save(){
		$q = 'UPDATE users SET ';
		$param = array();
		foreach(array('sid', 'login', 'password', 'level_access', 'data') as $key)
			if($this->$key != null){
				$q .= $key."=?, ";
				$param[] = $this->$key;
			}
			
		$param[] = $this->id;
		$q = substr($q, 0, strlen($q) - 2)." WHERE id=?";
		$this->dbh->update($q, $param);
	}
	
	public function is_authorized(){
		return $this->is_auth;
	}
	
	public function get_authorized($param){
		if(strlen($param['sid']) == 0)
			return new User($param);
		
		$res = $this->dbh->fetchRow(
			'SELECT * FROM users WHERE sid=?',
			array($param['sid'])
		);
		
		if($res == null)
			return new User($param);
		else{
			$u = new User($res);
			$u->is_auth = true;
			return $u;
		}
	}
	
	public function getByLogin(){
		$res = $this->dbh->fetchRow(
			'SELECT * FROM users WHERE login=?',
			array($this->login)
		);
		
		if($res == null)
			return null;
		else
			return new User($res);
	}
	
	public static function getListFromAccess($level_access){
		GLOBAL $DB_HEADER;
		$q = 'SELECT * FROM users ';
		$param = array();
		
		if($level_access != User::REGISTERED){
			$q .= 'WHERE level_access = ?';
			$param = array($level_access);
		}
		
		$q .= ' ORDER BY login ASC';
		
		return $DB_HEADER->fetchAll($q, $param);
	}
}











