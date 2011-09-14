<?php
require_once 'category/Category.php';

class CategoryController extends Controller{
	
	public function preDispathAdmin(){
		if(!$this->getRequest()->user->is_authorized() || $this->getRequest()->user->level_access < User::MODER)
			$this->_redirect('/error_404');
	}
	
	public function indexAction(){
		$this->preDispathAdmin();
		$this->getResponce()->setParam('category', Category::getTree());
		$this->getResponce()->setTemplate('admin/categories.html');
	}
	
	public static function getLinkRouteCategories(){
		$res = Category::getAll();
		return $res;
	}
	
	public function createAction(){
		$this->preDispathAdmin();
		if($this->getRequest()->getParam('action') == 'create'){
			$req = $this->getRequest();
			
			$category = new Category(array(
				'name' => $req->getParam('name'),
				'path' => $req->getParam('parent'),
				'link' => $req->getParam('link')	
			));
			
			if($category->is_valid()){
				$category->create();
				$this->_redirect('/administration/categories');
			}
			else{
				$this->getResponce()->setTemplate('admin/create_category.html');
				$this->getResponce()->setParam('error', $category->errors);
				$this->getResponce()->setParams($this->getRequest()->getParams());
			}
		}
		else{
			$this->getResponce()->setTemplate('admin/create_category.html');
			$this->getResponce()->setParam('parent', $this->getRequest()->getParam('parent'));
		}
		$this->getResponce()->setParam('category', Category::getTree());
	}
	
	public function editAction(){
		$this->preDispathAdmin();
		if($this->getRequest()->getParam('id') === '1')
			$this->_redirect(HTTP_404);
		
		$category = Category::getById($this->getRequest()->getParam('id'));
		
		if($category == null){
			$this->getResponce()->setTemplate('msg.html');
			$this->getResponce()->getParam('text', 'Данная категория не существует.');
			return;
		}
		
		if($this->getRequest()->getParam('action') == 'edit'){
			$req = $this->getRequest();
			
			$category = new Category(array(
				'name' => $req->getParam('name'),
				'path' => $req->getParam('parent'),
				'link' => $req->getParam('link'),
				'id' => $category->id
			));
			
			if($category->is_valid()){
				$category->save();
				$this->getResponce()->setTemplate('msg.html');
				$this->getResponce()->setParam('text', 'Категория успешно изменена.');
				$this->getResponce()->setParam('link', array('name' => 'Назад', 'url' => '/administration/categories'));
			}
			else{
				$this->getResponce()->setTemplate('admin/edit_category.html');
				$this->getResponce()->setParams($this->getRequest()->getParams());
				$this->getResponce()->setParam('error', $category->errors);	
			}
		}
		else{
			$this->getResponce()->setTemplate('admin/edit_category.html');
			preg_match_all('/[^.]+/i', $category->path, $res);
			$parent = Category::getById($res[0][count($res[0]) - 2]);
			$res = null;
			preg_match('/[^\/]+$/i', $category->link, $res);
			$this->getResponce()->setParams(array(
				'name' => $category->name,
				'link' => $res[0],
				'parent' => $parent->id,
				'id' => $category->id
			));
		}
		$this->getResponce()->setParam('category', Category::getTree());
	}
	
	public function deleteAction(){
		$this->preDispathAdmin();
		if($this->getRequest()->getParam('id') === '1')
			$this->_forward('404');
			
		$category = Category::getById($this->getRequest()->getParam('id'));
		
		if($category == null){
			$this->getResponce()->setTemplate('msg.html');
			$this->getResponce()->getParam('text', 'Данная категория не существует.');
			return;
		}
		
		$category->delete();
		$this->_redirect('/administration/categories');
	}
}


