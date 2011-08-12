<?php

require_once 'auth/Profile.php';
class User extends Model{
	const ADMIN = 3;
	const MODER = 2;
	const BUYER = 1;
	const ANONYM = 0;
	const REGISTERED = -1;
	
	public $sid;
	public $login;
	public $password;
	public $email;
	public $level_access;
	public $profile_id;
	public $repeatpassword;
	public $is_auth;
	public $profile;
	
	public function init(){
		$this->is_auth = false;
		$this->fields = array('sid', 'login', 'password', 'level_access', 'email', 'profile_id');
		$this->name_tb = 'users';
	}
	
	public function is_valid_login(){
		if(strlen($this->login) == 0)
			$this->errors['login'] = 'Поле не должно быть пусто.';
		elseif(!preg_match('/^[A-Za-z0-9_.-]+$/', $this->login))
			$this->errors['login'] = 'Допускаются только латинские буквы (a-z), цифры(0-9), точка(.), минус(-) и знак подчеркивания(_).';
		elseif(strlen($this->login) > 30 || strlen($this->login) < 4)
			$this->errors['login'] = 'Логин должен быть от 4 до 30 символов.';
		else{
			$user = self::getByLogin($this->login);
			if($user != null)
				$this->errors['login'] = 'Пользователь с таким логином уже существует.';
		}
		
		return array_key_exists('login', $this->errors) == false;
	}

	public function is_valid_password(){
		if(strlen($this->password) == 0)
			$this->errors['password'] = 'Поле не должно быть пусто.';
		elseif(strlen($this->password) < 4)
			$this->errors ['password'] = 'Пароль должен быть не менее 4 символов.';
		elseif(strlen($this->password) > 256)
			$this->errors['password'] = 'Пароль не может быть длиннее 256 символов.';
		elseif(strcmp($this->password, $this->repeatpassword))
			$this->errors ['password'] = 'Введенные пароли не совпадают';
		
		return array_key_exists('password', $this->errors) == false;
	}
	
	public function is_valid_email(){
		if(strlen($this->email) > 0)
			if(strlen($this->email) > 256){
				$this->errors['email'] = 'Поле не может быть длиннее 256 символов.';
				return false;
			}elseif(!filter_var($this->email, FILTER_VALIDATE_EMAIL)){
				$this->errors['email'] = 'Неверный e-mail.';
				return false;
			}
		return true;
	}
	
	/*public function create(){
		$res = $this->dbh->insert(
			'INSERT INTO users(sid, login, password, level_access, email, profile_id) VALUES (?, ?, ?, ?, ?, ?)',
			array($this->sid, $this->login, $this->password, $this->level_access, $this->email, $this->profile_id)
		);
		
		$res = $this->dbh->fetchRow('SELECT * FROM users WHERE id=LAST_INSERT_ID()');
		
		return $res == null? null: new User($res);
	}*/
	
	public function save(){
		$q = 'UPDATE users SET ';
		$param = array();
		foreach(array('sid', 'login', 'password', 'level_access', 'email', 'profile_id') as $key)
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
	
	public static function getByLogin($login){
		GLOBAL $DB_HEADER;
		$res = $DB_HEADER->fetchRow('SELECT * FROM users WHERE login=?', array($login));
		
		return $res == null? null: new User($res);
	}
	
	public static function getById($id){
		GLOBAL $DB_HEADER;
		$res = $DB_HEADER->fetchRow('SELECT * FROM users WHERE id=?', array($id));
		if($res != null){
			$profile = Profile::getById($res['profile_id']);
			$user = new User($res);
			$user->profile = $profile;
			return $user;
		}
		else
			return null;
	}
	
	public function delete(){
		$this->dbh->delete('DELETE FROM users, profiles USING users INNER JOIN profiles ON
		 	profiles.id=users.id WHERE users.id=?',
			array($this->id));
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











