<?php

class Category extends Model{
	public $path;
	public $name;
	public $link;
	
	protected function init(){
		$this->name_tb = 'categories';
		$this->fields = array('path', 'name', 'link');
	}
	
	public static function getAllCategories($path = ''){
		GLOBAL $DB_HEADER;
		
		return $DB_HEADER->fetchAll("SELECT * FROM categories");
	}
	
	private static function privateGetAll(){
		GLOBAL $DB_HEADER;
		
		return $DB_HEADER->fetchAll("SELECT * FROM categories");
	}
	
	private static function rebuild($list, $all=null){
		
		$res = array();
		foreach($all == null? self::privateGetAll(): $all as $key => $val)
			$res[$val['id']] = $val;
		
		
		$result = array();
		foreach($list as $key => $val){
			$matches = array();
			preg_match_all('/[^.]+/', $val['path'], $matches);
			$val['link'] = '';
			$val['path_name'] = '';
			foreach($matches[0] as $k){
				$val['link'] = $val['link'].$res[$k]['link'];
				$val['path_name'][] = $res[$k]['name']; 
			}
			$result[] = $val;
		}
		return $result;
	}
	
	public static function getListLeaf(){
		GLOBAL $DB_HEADER;
		$leafs = $DB_HEADER->fetchAll('SELECT * FROM categories AS t1 WHERE 
			(SELECT COUNT(*) FROM categories AS t2 WHERE t2.path LIKE CONCAT(t1.path, "%"))=1');
		return self::rebuild($leafs);
	}
	
	public static function getAll(){
		$all = self::privateGetAll();
		return self::rebuild($all, $all);
	}
	
	public static function getTree(){
		$all = self::getAll();
		$res = array();
		foreach($all as $k => $v)
			array_push_by_creative_key($res, $v['path'], $v);
			
		$res = self::rebuildTree($res);
		return $res;
	}
	
	private static function rebuildTree(&$tree){
		foreach($tree as $k => $val)
			if(is_array($val) && $k != 'subcategories' && $k != 'path_name'){
				$tree['subcategories'][] = self::rebuildTree($tree[$k]);
				unset($tree[$k]);
			}
		return $tree;
	}
	
	
	public function is_valid_name(){
		if(strlen($this->name) == 0)
			$this->errors['name'] = 'Поле не может быть пусто.';
		elseif(!preg_match('/^[А-Яа-яA-Za-z0-9._ ]+$/u', $this->name))
			$this->errors['name'] = 'Имя может содержать только буквы латинского и русского алфавита, цифры, пробел " ", знак подчеркивания "_" и точку ".".';
		else{
			if($this->dbh->fetchRow("SELECT * FROM ".$this->name_tb." WHERE name = ? AND name IN
				SELECT path FROM categories WHERE id=$this->path", array($this->name)) !== null)
				$this->errors['name'] = 'Такая категория уже существет.';
		}
	}
	
	public function is_valid_link(){
		if(strlen($this->link) == 0)
			$this->errors['link'] = 'Поле не должно быть пусто.';
		elseif(!preg_match('/^[A-Za-z0-9_]+$/u', $this->link))
			$this->errors['link'] = 'Ссылка может содержать только буквы латинского алфавита, цифры и знак подчеркивания "_".';
		else{
			$count = $this->dbh->fetchRow("SELECT COUNT(*) FROM ".$this->name_tb." WHERE link=?
				AND path REGEXP CONCAT('^', (SELECT path FROM categories WHERE id=$this->path), '\.[^.]$')",
			 	array('/'.$this->link));
			 	
			if($count['COUNT(*)'] > 1)
				$this->errors['link'] = 'Такая ссылка уже существует.';
		}
	}
	
	public function delete(){
		$this->dbh->delete('CALL `delete_category`(?)', array($this->id));
	}
	
	public function save(){
		$this->dbh->update('CALL `update_category`(?, ?, ?, ?)', array($this->id, $this->path, $this->name, $this->link));
	}
	
	public static function getById($id){
		GLOBAL $DB_HEADER;
		$res = $DB_HEADER->fetchRow('SELECT * FROM categories WHERE id = ?', array($id));
		return $res == null? null: new Category($res);
	}
	
	public function is_valid_path(){
		if(self::getById($this->path) == null)
			$this->errors['parent'] = 'Данной родительской категории не существует.';
	}
}