<?php
require_once 'category/Product.php';
require_once 'category/Category.php';
require_once 'category/Comments.php';
require_once 'voted/Voted.php';

define(IMAGE_SIZE, 125);
define(COUNT_ON_PAGE, 14);

class ProductController extends Controller{
	
	public function preDispathForAdmin(){
		if(!$this->getRequest()->user->is_authorized() || $this->getRequest()->user->level_access < User::MODER)
			$this->_redirect('/error_404');
	}
	
	public function adminListAction(){
		$this->preDispathForAdmin();
		$page = $this->getRequest()->getParam('page', 1);
		
		$this->getResponce()->setTemplate('admin/products.html');
		$category = $this->getRequest()->getParam('category');
		
		$products = Product::getProductsByCategoryId($category['id'], ($page - 1)*COUNT_ON_PAGE, COUNT_ON_PAGE);
		
		$count = Product::getCountProductsByCategoryId($category['id']);
		
		$this->getResponce()->setParam('pagination', pagination($count, COUNT_ON_PAGE, $page, 3,
			'/administration/products'.$category['link']));
		
		$this->getResponce()->setParam('categories', Category::getListLeaf());
		$this->getResponce()->setParam('products', $products);
		$this->getResponce()->setParam('category', $category);
		$this->getResponce()->setParam('curent_url', $this->getRequest()->getURL());
		$this->getResponce()->setParam('create_link', preg_replace('/\/[0-9]+$/', '/create', $this->getRequest()->getURL()));
	}
	
	
	public function createAction(){
		$this->preDispathForAdmin();
		$category = $this->getRequest()->getParam('category');
		
		
		if($this->getRequest()->getParam('action') == 'create'){
			$product = new Product($this->getRequest()->POST);
			
			if(is_uploaded_file($this->getRequest()->FILES['image']['tmp_name'])){
				$res = validate_file($this->getRequest()->FILES['image'], MAX_SIZE_AVATAR);
				if($res !== true)
					$product['image'] = $res;
				else{
					$product->image = upload($this->getRequest()->FILES['image'], null, IMAGE_DIR);
					resizeImg($product->image, IMAGE_DIR, IMAGE_SIZE, IMAGE_SIZE);
				}
			}
			else
				$product->image = DEFAULT_IMAGE;
			
			if($product->is_valid()){
				$product = $product->create();
				$this->_redirect($this->getRequest()->getParam('redirect_to', '/administration/products/1'));	
			}
			else{
				$this->getResponce()->setParam('error', $product->errors);
				$this->getResponce()->setParams($this->getRequest()->getParams());
			}
		}
		else
			$this->getResponce()->setParam('category_id', $category['id']);
			
		$categories = Category::getListLeaf(); 
		$this->getResponce()->setParam('categories', $categories);
		$this->getResponce()->setParam('link', $this->getRequest()->getURL());
		$this->getResponce()->setParam('redirect_to', $this->getRequest()->getParam('redirect_to'));
		$this->getResponce()->setTemplate('admin/create_product.html');
	}
	
	public function editAction(){
		$this->preDispathForAdmin();
		
		$product = Product::getById($this->getRequest()->getParam('id'));
		
		
		$link_back = $this->getRequest()->getParam('redirect_to', '/administration/products/1');
		if($product == null){
			$this->getResponce()->setTemplate('msg.html');
			$this->getResponce()->setParams(array(
				'text', 'Данный товар не существует',
				'link' => array(
					'url' => $link_back,
					'name' => 'Назад'
				)
			));
			return;
		}
		
		if($this->getRequest()->getParam('action') == 'edit'){
			$newproduct = new Product($this->getRequest());
			
			if(is_uploaded_file($this->getRequest()->FILES['image']['tmp_name'])){
				$res = validate_file($this->getRequest()->FILES['image'], MAX_SIZE_AVATAR);
				
				if($res !== true)
					$newproduct->errors['image'] = $res;
				else{
					$newproduct->image = upload($this->getRequest()->FILES['image'], null, IMAGE_DIR);
					resizeImg($newproduct->image, IMAGE_DIR, IMAGE_SIZE, IMAGE_SIZE);
				}
			}
			else
				$newproduct->image = $product->image;

			if($newproduct->is_valid()){
				if($newproduct->image !== $product->image && 
					Product::getCountProductsLinkingToImage($product->image) == 1 && $product->image != DEFAULT_IMAGE)
					unlink(MEDIA_ROOT.IMAGE_DIR.'/'.$product->image);
				
				$newproduct->id = $product->id;
				$newproduct->save();
				$this->getResponce()->setTemplate('admin/msg.html');
				$this->getResponce()->setParams(array(
					'text', 'Изменения успешно сохранены.',
					'link' => array(
						'url' => $link_back,
						'name' => 'Назад'
					)
				));
				return;
			}
			else
				$this->getResponce()->setParam('error', $newproduct->errors);
		}
		
		$product->image = IMAGE_URL.'/'.$product->image;
		
		$categories = Category::getListLeaf(); 
		$this->getResponce()->setParam('categories', $categories);
		
		$product->category = array_search($categories, $product->category_id);
		$this->getResponce()->setParam('redirect_to', $link_back);
		$this->getResponce()->setTemplate('admin/edit_product.html');
		$this->getResponce()->setParam('product', get_object_vars($product));
	}
	
	public function infoAction(){
		$this->preDispathForAdmin();
		
		$product = Product::getById($this->getRequest()->getParam('id'));
		$back_link = $this->getRequest()->getParam('redirect_to', '/administration/products/1');
		
		if($product == null){
			$this->getResponce()->setTemplate('admin/msg.html');
			$this->getResponce()->setParams(array(
				'text', 'Такого товара не существкет.',
				'link' => array('name' => 'Назад', 'url' => $back_link)
			));
			return;	
		}
		
		$this->getResponce()->setTemplate('admin/product.html');
		$this->getResponce()->setParam('redirect_to', $back_link);
		$product->image = IMAGE_URL.'/'.$product->image;
		$product->category = get_object_vars(Category::getById($product->category_id));
		$this->getResponce()->setParam('product', get_object_vars($product));
	}
	
	public function deleteAction(){	
		$this->preDispathForAdmin();
		
		$product = Product::getById($this->getRequest()->getParam('id'));
		
		if($product == null){
			$this->getResponce()->setTemplate('admin/msg.html');
			$this->getResponce()->setParam('text', 'Такого товара не существует.');
			return;
		}
		
		if($product->image != DEFAULT_IMAGE && Product::getCountProductsLinkingToImage($product->image) == 1)
			unlink(MEDIA_ROOT.IMAGE_DIR.'/'.$product->image);
		
		$product->delete();
		$this->_redirect($this->getRequest()->getParam('redirect_to', '/administration/products/1'));
	}
	
	public function viewAction(){
		$this->getResponce()->setTemplate('product.html');
		$this->getResponce()->setParam('product_link', $this->getRequest()->getURL());
		
		$product = Product::getById($this->getRequest()->getParam('id'));
		
		if($product == null)
			$this->_redirect(HTTP_404);
		
		$product->image = IMAGE_URL.'/'.$product->image;
		
		$comments = new Comments();
		$comments = $comments->getTreeComments($product->id, $this->getRequest()->user->id);
		
		$this->getResponce()->setParam('product', get_object_vars($product));
		$this->getResponce()->setParam('product.voted', Voted::getCountByParam(array(
			'user_id' => $this->getRequest()->user->id,
			'data_id' => $product->id,
			'type' => Voted::$types['product']
		)) != 0);
		
		$this->getResponce()->setParam('comment', array('comments' => $comments));
		$this->_forward('initPage', 'IndexController', '');
	}
}