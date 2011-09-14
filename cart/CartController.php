<?php

require_once 'cart/Cart.php';
require_once 'category/Product.php';
require_once 'category/Category.php';
require_once 'cart/Order.php';

class CartController extends Controller{
	const COUNT_ON_PAGE = 10;
	
	public function preDispath(){
		if(!$this->getRequest()->user->is_authorized())
			$this->_redirect(HTTP_404);
	}
	
	public function addAction(){
		$cart = &$this->getRequest()->user->cart;
		
		$product = Product::getById($this->getRequest()->getParam('product_id'));
		
		if($product == null){
			$this->_redirect(HTTP_404);
			return;
		}
		
		$cart->add($product->id, $this->getRequest()->getParam('count', 1));
			
		$cart->save();
		
		$this->_redirect($this->getRequest()->getParam('redirect_to'));
	}
	
	public function popAction(){
		$cart = $this->getRequest()->user->cart;
		$product_id = $this->getRequest()->getParam('product_id');
		if(!$cart->isExistProduct($product_id)){
			$this->_redirect(HTTP_404);
			return;
		}
		
		$cart->pop($product_id);
		$cart->save();
		$this->_redirect('/my_cart');
	}
	
	public function indexAction(){
		$this->getResponce()->setTemplate('cart.html');
		
		$cart = $this->getRequest()->user->cart;
		$products = $cart->getProducts();
		
		$this->getResponce()->setParams($this->buildFullInfoCartForInfo($products));
		$tree = Category::getTree();
		$this->getResponce()->setParam('category', $tree['subcategories'][0]);
	}
	
	public function refreshAction(){
		$cart = &$this->getRequest()->user->cart;
		$products_id = $cart->get_products_id();
		$cart->erase();
		
		foreach($products_id as $k => $v)
			$cart->add($v, $this->getRequest()->getParam($v));
				
		$cart->save();
		
		$this->_redirect('/my_cart');
	}
	
	public function eraseAction(){
		$cart = &$this->getRequest()->user->cart;
		
		$cart->erase();
		$cart->save();
		
		$this->_redirect('/my_cart');
	}
	
	public function orderAction(){
		$this->getResponce()->setTemplate('order_cart.html');
		
		$cart = $this->getRequest()->user->cart;
		if($cart->count == 0){
			$this->_redirect('/my_cart');
			return;
		}
			
		if($this->getRequest()->getParam('action') == 'to_order'){
			
			$order = new Order($this->getRequest()); 
			$order->cart_id = (int)$cart->id;

			if($order->is_valid()){
				$cart->state = Cart::READY;
				$cart->date = date('d.n.Y G:i:s');
				foreach($cart->getProducts() as $k => $v)
					$cart->cost += $v['count'] * $v['cost'];
				
				$cart->save();
				
				$order->create();
				
				$newcart = new Cart(array('user_id' => $cart->user_id));
				$this->getRequest()->user->cart = $newcart->create();
				
				$this->getResponce()->setTemplate('msg.html');
				$this->getResponce()->setParam('text', 'Заказ принят.');
				$this->getResponce()->setParam('user.cart', get_object_vars($this->getRequest()->user->cart));
			}
			else{
				$this->getResponce()->setParam('addres', $this->getRequest()->getParams());
				$this->getResponce()->setParam('error', $order->errors);
			}	
		}
		else
			$this->getResponce()->setParam('addres', get_object_vars($this->getRequest()->user->profile));
		
			$this->setTreeCategories();
	}
	
	private function setTreeCategories(){
		$tree = Category::getTree();
		$this->getResponce()->setParam('category', $tree['subcategories'][0]);
	}
	
	
	public function historyAction(){
		$this->getResponce()->setTemplate('cart_history.html');
		$page = $this->getRequest()->getParam('page');
		
		$carts = Cart::getCartWithFullInfoByUserId(
			$this->getRequest()->user->id, ($page - 1) * self::COUNT_ON_PAGE, self::COUNT_ON_PAGE);
			
		$count = Cart::getCountCartWithFullInfoByUserId($this->getRequest()->user->id);
		
		$this->getResponce()->setParam('pagination', 
			pagination($count, self::COUNT_ON_PAGE, $this->getRequest()->getParam('page'), 4, '/my_cart/history'));
			
		$this->getResponce()->setParam('carts', $carts);
		$this->setTreeCategories();
	}
	
	private function buildFullInfoCartForInfo($products){
		$total = array('cost' => 0, 'count' => 0);
		foreach($products as $k => $v){
			$products[$k]['image'] = IMAGE_URL.'/'.$v['image'];
			$total['cost'] += $v['cost'] * $v['count'];
			$total['count'] += $v['count'];
		}
		
		return array('total' => $total, 'shoping' => $products);
	}
	
	public function MoreCartHistoryAction(){
		$cart = Cart::getById($this->getRequest()->getParam('id'));
		
		if($cart == null)
			$this->_redirect($this->getRequest()->getParam('redirect_to'));

		$this->getResponce()->setTemplate('cart_info.html');
		$this->getResponce()->setParams($this->buildFullInfoCartForInfo($cart->getProducts()));
		$this->getResponce()->setParam('redirect_to', $this->getRequest()->getParam('redirect_to'));
		$this->setTreeCategories();
	}
}