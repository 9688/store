<?php
require_once 'category/Category.php';
require_once 'category/Comments.php';

class Product extends Model{
	public $category_id;
	public $name;
	public $info;
	public $image;
	public $cost;
	public $mark = 0;
		
	protected function init(){
		$this->name_tb = 'products';	
		$this->fields = array('category_id', 'name', 'info', 'image', 'cost', 'mark');
	}
	
	public static function getProductsByCategoryId($id=1, $start = '0', $end = null){
		GLOBAL $DB_HEADER;
		return $DB_HEADER->fetchAll(
			'SELECT t1.*, t2.name as category
				FROM products AS t1, categories AS t2 WHERE t1.category_id IN
				(SELECT id FROM categories WHERE categories.path LIKE
				(SELECT CONCAT(path, "%") FROM categories WHERE categories.id = ?)) AND t2.id=t1.category_id
				 ORDER BY t1.mark DESC LIMIT '.$start.(isset($end)? ','.$end: ''),
			array($id));
	}
	
	public static function getCountProductsByCategoryId($id){
		GLOBAL $DB_HEADER;
		$res = $DB_HEADER->fetchRow(
			'SELECT COUNT(*) FROM products AS t1, categories AS t2 WHERE t1.category_id IN
				(SELECT id FROM categories WHERE categories.path LIKE
				(SELECT CONCAT(path, "%") FROM categories WHERE categories.id = ?)) AND t2.id=t1.category_id',
			array($id));
			
		return $res['COUNT(*)'];
	} 
	
	public static function getById($id=1){
		GLOBAL $DB_HEADER;
		$res = $DB_HEADER->fetchRow('SELECT * FROM products WHERE id = ?', array($id));
		
		return $res == null? null: new Product($res);
	}
	
	public function is_valid_category_id(){
		if(Category::getById($this->category_id) == null)
			$this->errors['category_id'] = 'Данной категории не существует.';
	}
	
	public function is_valid_name(){
		if(strlen($this->name) == 0)
			$this->errors['name'] = 'Поле не может быть пусто.';
		elseif(strlen($this->name) > 256)
			$this->errors['name'] = 'Поле не должно превышать 256 символов';
	}
	
	public function is_valid_info(){
		if(strlen($this->info) == 0)
			$this->errors['info'] = 'Поле не может быть пусто.';
	}
	
	public function delete(){
		$comments = new Comments();
		$comments->delete($this->id);
		parent::delete();
	}
	
	public function create(){
		$res = parent::create();
		$comments = new Comments();
		$comments->initComments($res->id);
		return $res;
	}
	
	public function is_valid_cost(){
		if(strlen($this->cost) == 0)
			$this->errors['cost'] = 'Поле не может быть пусто.';
		elseif(!preg_match('/^[0-9]+$/', $this->cost))
			$this->errors['cost'] = 'Поле может содержать только числа.';
	}
	
	public static function getCountProductsLinkingToImage($name){
		GLOBAL $DB_HEADER;
		$res = $DB_HEADER->fetchRow('SELECT COUNT(*) FROM products WHERE image=?', array($name));
		return $res['COUNT(*)'];
	}
}