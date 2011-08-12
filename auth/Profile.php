<?php
class Profile extends Model{
	public $first_name;
	public $middle_name;
	public $last_name;
	public $avatar;
	public $country;
	public $city;
	public $street;
	public $house_number;
	public $apartament_number;
	
	protected function init(){
		$this->fields = array('first_name', 'middle_name', 'last_name', 'avatar', 'country', 'city', 'street',
			'house_number', 'apartament_number');
		$this->name_tb = 'profiles';
	}
	
	private function validateCharField($field){
		if(strlen($this->$field) > 0)
			if(strlen($this->field) > 256){
				$this->errors[$field] = 'Поле не может быть длиннее 256 символов.';
				return false;
			}elseif(preg_match('/[!@#$%^&*_";]+/', $this->$field)){
				$this->errors[$field] = 'Поле содержит недопустимые символы.';
				return false;
			}
		return true;
	}
	
	public function is_valid_first_name(){
		return $this->validateCharField('first_name');
	}
	
	public function is_valid_middle_name(){
		return $this->validateCharField('middle_name');
	}
	
	public function is_valid_last_name(){
		return $this->validateCharField('last_name');
	}
	
	public function is_valid_country(){
		return $this->validateCharField('country');
	}
	
	public function is_valid_city(){
		return $this->validateCharField('city');
	}
	
	public function is_valid_street(){
		return $this->validateCharField('street');
	}
	
	public function is_valid_house_number(){
		return $this->validateCharField('house_number');
	}
	
	public function is_valid_apartament_number(){
		return $this->validateCharField('apartament_number');
	}

	public static function getById($id){
		GLOBAL $DB_HEADER;
		$res = $DB_HEADER->fetchRow('SELECT * FROM profiles WHERE id=?', array($id));
		
		return $res == null? null: new Profile($res);
	}
	
	public static function getCountProfilesLinkingToAvatar($name){
		GLOBAL $DB_HEADER;
		$res = $DB_HEADER->fetchCol('SELECT COUNT(*) FROM profiles WHERE avatar=?', array($name));
		return $res['COUNT(*)'];
	}
	
/*	public function create(){
		$this->dbh->insert('INSERT INTO profiles(first_name) VALUES(NULL)', array());
		$res = $this->dbh->fetchRow('SELECT * FROM profiles WHERE id= LAST_INSERT_ID()');
		return $res == null? null: new Profile($res);
	}*/
	
	public function save(){
		$q = 'UPDATE profiles SET ';
		$param = array();
		$fields = array('first_name', 'middle_name', 'last_name', 'avatar', 'country',
			'city', 'street', 'house_number', 'apartament_number');
		
		foreach($fields as $key)
			if($this->$key != null){
				$q .= $key."=?, ";
				$param[] = $this->$key;
			}
			
		$param[] = $this->id;
		$q = substr($q, 0, strlen($q) - 2)." WHERE id=?";
		$this->dbh->update($q, $param);
	}
}