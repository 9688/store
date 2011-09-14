<?php
class Profile extends Model{
	public $first_name = '';
	public $middle_name = '';
	public $last_name = '';
	public $avatar = '';
	public $country = '';
	public $city = '';
	public $street = '';
	public $house_number = '';
	public $apartament_number = '';

	protected function init(){
		$this->fields = array('first_name', 'middle_name', 'last_name', 'avatar', 'country', 'city', 'street',
			'house_number', 'apartament_number');
		$this->name_tb = 'profiles';
	}
	
	public function is_valid_FIELD($key, $val){
		if(strlen($val) > 0 && $key != 'avatar')
			if(strlen($val) > 256)
				return 'Поле не может быть длиннее 256 символов.';
			elseif(preg_match('/[!@#$%^&*_";]+/', $val))
				return 'Поле содержит недопустимые символы.';
	}

	public static function getCountProfilesLinkingToAvatar($name){
		GLOBAL $DB_HEADER;
		$res = $DB_HEADER->fetchCol('SELECT COUNT(*) FROM profiles WHERE avatar=?', array($name));
		return $res['COUNT(*)'];
	}
	
	static public function getById($id){
		GLOBAL $DB_HEADER;
		$res = $DB_HEADER->fetchRow('SELECT * FROM profiles WHERE id=?', array($id));
		return $res == null? null: new Profile($res);
	}
	
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
