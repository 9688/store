<?php

require_once 'auth/Profile.php';
require_once 'category/Comments.php';

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
	public $rating = 0;
	public $profile_id;
	public $repeatpassword;
	public $is_auth;
	public $profile;
	
	public function init(){
		$this->is_auth = false;
		$this->fields = array('sid', 'login', 'password', 'level_access', 'email', 'profile_id', 'rating');
		$this->name_tb = 'users';
	}
	
	public function is_valid_login(){
		if(strlen($this->login) == 0)
			return 'Поле не должно быть пусто.';
		elseif(!preg_match('/^[A-Za-z0-9_.-]+$/', $this->login))
			return 'Допускаются только латинские буквы (a-z), цифры(0-9), точка(.), минус(-) и знак подчеркивания(_).';
		elseif(strlen($this->login) > 30 || strlen($this->login) < 4)
			return 'Логин должен быть от 4 до 30 символов.';
		else{
			$user = self::getByLogin($this->login);
			if($user != null)
				return 'Пользователь с таким логином уже существует.';
		}
	}

	public function is_valid_password(){
		if(strlen($this->password) == 0)
			return 'Поле не должно быть пусто.';
		elseif(strlen($this->password) < 4)
			return 'Пароль должен быть не менее 4 символов.';
		elseif(strlen($this->password) > 256)
			return 'Пароль не может быть длиннее 256 символов.';
		elseif(strcmp($this->password, $this->repeatpassword))
			return 'Введенные пароли не совпадают';
	}
	
	public function is_valid_email(){
		if(strlen($this->email) > 0)
			if(strlen($this->email) > 256)
				return 'Поле не может быть длиннее 256 символов.';
			elseif(!filter_var($this->email, FILTER_VALIDATE_EMAIL))
				return 'Неверный e-mail.';
	}
	/*
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
	}*/
	
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
		$comments = new Comments();
		$comments->deleteCommensByUserId($this->id);
		$this->dbh->delete('DELETE FROM users, profiles, carts USING users, profiles, carts WHERE
			users.profile_id=profiles.id AND carts.user_id=users.id AND users.id=?',
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
	
	public static function getUsersByIds($ids){
		GLOBAL $DB_HEADER;
		
		return $DB_HEADER->fetchAll('SELECT u.*, p.*, u.id FROM users as u, profiles as p WHERE u.profile_id = p.id AND u.id IN ('.implode(',',$ids).')');
	}
	
	public static function getCount(){
		GLOBAL $DB_HEADER;
		$res = $DB_HEADER->fetchRow('SELECT COUNT(*) FROM users');
		return $res['COUNT(*)'];
	}
	
	public function addRating($product_id, $comment_id, $mark){
		$comments = new Comments();
		
		if(!$comments->isVotedCommentId($product_id, $comment_id, $this->id)){
			$user_id = $comments->getUserIdByCommentId($product_id, $comment_id);
			$comments->addMark($product_id, $comment_id, $this->id, $mark);
			$this->dbh->update('UPDATE users as u SET u.rating = u.rating + ('.$mark.') WHERE u.id=?', array($user_id));
			
			return true;
		}
		else
			return false;
	}
	
	public static function getMaxRating(){
		
	}
}