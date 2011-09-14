<?php
class Order extends Model{
	public $cart_id;
	public $first_name = '';
	public $middle_name = '';
	public $last_name = '';
	public $country = '';
	public $city = '';
	public $street = '';
	public $house_number = '';
	public $apartament_number = '';
	public $state = self::DELIVERING;
	public $curent_address = 'На складе';
	
	const DELIVERED = '1';
	const DELIVERING = '0';
	
	public function init(){
		$this->fields = array('cart_id', 'first_name', 'middle_name', 'last_name', 'country', 'city', 'street',
			'house_number', 'apartament_number', 'state', 'curent_address');
		$this->name_tb = 'orders';
	}
	
	public function is_valid_FIELD($name, $val){
		if(array_search($name, array('state', 'curent_address')) !== false)
			return;
			
		if(strlen($val) == 0)
			return 'Поле обязательно для заполнения.';
		elseif(strlen($val) > 256)
			return 'Поле не может быть длинее 256 символов.';
	}
	
	public static function getDelivering($start, $count){
		GLOBAL $DB_HEADER;
		return $DB_HEADER->fetchAll('SELECT o.*, o.state FROM orders as o, carts as c 
			WHERE o.cart_id=c.id AND o.state='.self::DELIVERING.' ORDER BY c.date DESC LIMIT '.$start.','.$count);
	}
	
	public static function getCountDelivering(){
		GLOBAL $DB_HEADER;
		$res = $DB_HEADER->fetchRow('SELECT COUNT(*) FROM orders WHERE state='.self::DELIVERING);
		return $res['COUNT(*)'];
	}
	
	public static function getById($id){
		GLOBAL $DB_HEADER;
		$res = $DB_HEADER->fetchRow('SELECT * FROM orders WHERE id = ?', array($id));
		return $res == null? null: new Order($res);
	}
}