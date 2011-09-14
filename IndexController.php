<?php
require_once 'auth/User.php';
require_once 'category/Category.php';
require_once 'category/Product.php';

define( 'COUNT_PRODUCTS_ON_PAGE', 12);

class IndexController extends Controller{
	public function preDispath(){
		$this->getResponce()->setTemplate('index.html');
		$this->initPageAction();		
	}
	
	public function initPageAction(){
		$tree = Category::getTree();
		$curent_category = $this->getRequest()->getParam('category');
		$curent_category['path_name'] = array_merge(array('Главная'), array_slice($curent_category['path_name'], 1));
		$this->getResponce()->setParam('path_name', $curent_category['path_name']);
		$this->getResponce()->setParam('category', $tree['subcategories'][0]);
	}
	
	public function indexAction(){
		$page = $this->getRequest()->getParam('page');
		$page = $page == null? 1: $page;
		
		$curent_category = $this->getRequest()->getParam('category');  
	
		$products = Product::getProductsByCategoryId($curent_category['id'], ($page - 1)*COUNT_PRODUCTS_ON_PAGE, COUNT_PRODUCTS_ON_PAGE);
		
		$count = Product::getCountProductsByCategoryId($curent_category['id']);
		$this->getResponce()->setParam('pagination', 
			pagination($count, COUNT_PRODUCTS_ON_PAGE, $page, 4, $curent_category['link']));	
		$this->getResponce()->setParam('products', $products);
	}
	
	public function notFoundAction(){
		$this->getResponce()->setTemplate('404.html');
	}
}