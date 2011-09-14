<?php

require_once 'auth/User.php';

class Cart extends Model{
	const PREPARE = '0';
	const READY = '2';
	
	public $shoping = '';
	public $user_id;
	public $state = self::PREPARE;
	public $date = '';
	public $count = 0;
	public $cost = 0;
	
	protected function init(){
		$this->fields = array('shoping', 'user_id', 'date', 'state', 'cost', 'count');
		$this->name_tb = 'carts';
	}
	
	public function getProducts(){
		$matches = array();
		preg_match_all('/<([0-9]+),([0-9]+)>/', $this->shoping, $matches);
		
		$res = $this->dbh->fetchAll('SELECT * FROM products WHERE id IN '.'('.implode(',',array_unique($matches[1])).')');
		$count = array_combine($matches[1], $matches[2]);
		foreach($res as $k => $v)
			$res[$k]['count'] = $count[$v['id']];
			
		return $res;
	}
	
	public static function getById($id){
		GLOBAL $DB_HEADER;
		$res = $DB_HEADER->fetchRow('SELECT t1.* FROM carts as t1 WHERE id=?', array($id));
		return $res == null? null: new Cart($res);
	}
	
	public static function getByUserId($id){
		GLOBAL $DB_HEADER;
		$res = $DB_HEADER->fetchRow('SELECT * FROM carts WHERE user_id=?', array($id)); 
	}
	
	public static function getCurentByUserId($id){
		GLOBAL $DB_HEADER;
		$res = $DB_HEADER->fetchRow('SELECT * FROM carts WHERE user_id=? AND state=? ', array($id, self::PREPARE));
		return $res == null? null: new Cart($res);
	}
	
	public function is_valid_user_id(){
		if(User::getById($this->user_id) == null)
			$this->errors['user_id'] = 'Такого пользователя не существует.';
	}
	
	public function add($id, $count = 1){
		if($count < 1)
			return;
			
		if(strlen($this->shoping) == 0 || preg_match('/<'.$id.',([0-9]+)>/', $this->shoping, $match) == 0)
			$this->shoping .= '<'.$id.','.$count.'>';
		else{
			preg_match('/,([0-9]+)>/', $match[0], $c_count);
			$this->shoping = preg_replace($match[0], $id.','.($c_count[1] + $count), $this->shoping);
		}
		
		$this->count += $count;
	}
	
	public function get_products_id(){
		$matches = array();
		preg_match_all('<([0-9]+),[0-9]+>', $this->shoping, $matches);
		return $matches[1];
	}
	
	public function erase(){
		$this->shoping = '';
		$this->count = 0;
	}
	
	public function pop($id){
		$match = array();
		if(preg_match('/(<'.$id.',([0-9]+)>)/', $this->shoping, $match) > 0){
			$this->shoping = str_replace($match[1], '', $this->shoping);
			$this->count -= (int)$match[2];
		}
	}
	
	public function isExistProduct($product_id){
		return preg_match('/<'.$product_id.',[0-9]+>/', $this->shoping) > 0;
	}
	
	public static function getCartWithFullInfoByUserId($id, $start, $end){
		GLOBAL $DB_HEADER;
		
		return $DB_HEADER->fetchAll(
			'SELECT c.*, o.curent_address, o.state FROM carts as c, orders as o WHERE o.cart_id=c.id AND 
				c.state = '.self::READY.' AND c.user_id = ? ORDER BY c.date DESC LIMIT '.$start.','.$end,
			array($id)
		);
	}
	
	public static function getCountCartWithFullInfoByUserId($id){
		GLOBAL $DB_HEADER;
		$res = $DB_HEADER->fetchRow('SELECT COUNT(*) FROM carts WHERE user_id=? AND state='.self::READY, array($id));
		return $res['COUNT(*)'];
	}
}