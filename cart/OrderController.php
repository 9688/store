<?php
require_once 'cart/Order.php';

class OrderController extends Controller{
	const COUNT_ON_PAGE = 10;
	
	public function preDispath(){
		if(!$this->getRequest()->user->is_authorized() || $this->getRequest()->user->level_access < User::MODER)
			$this->_redirect(HTTP_404);
	}
	
	public function indexAction(){
		$page = $this->getRequest()->getParam('page', 1);
		$this->getResponce()->setTemplate('admin/orders.html');
		$orders = array();
		foreach(Order::getDelivering(($page - 1) * self::COUNT_ON_PAGE, self::COUNT_ON_PAGE) as $k => $v){
			$orders[] = array(
				'id' => $v['id'],
				'fio' => $v['last_name']/*.' '.mb_substr($v['first_name'], 0, 1).'.'.mb_substr($v['middle_name'], 0, 1)*/,
				'destination_address' => $v['country'].', '.$v['city'].', '.$v['street'].' '
					.$v['house_number'].' '.$v['apartament_number'],
				'curent_address' => $v['curent_address']
			);
		}
		
		$this->getResponce()->setParam('orders', $orders);
		
		$count = Order::getCountDelivering();
		
		$this->getResponce()->setParam('pagination', 
			pagination($count, self::COUNT_ON_PAGE, $page, 4, '/administration/orders'));
			
		
	}
	
	public function setStateAction(){
		$order = Order::getById($this->getRequest()->getParam('id'));
		if($order == null){
			$this->getResponce()->setTemplate('admin/msg.html');
			$this->getResponce()->setParams(array(
				'text' => 'Такого заказа нет.',
				'link' => array('url' => $this->getRequest()->getParam('redirect_to'), 'name' => 'назад')
			));
		}
		
		if($this->getRequest()->getParam('action') == 'save'){
			$order->curent_address = $this->getRequest()->getParam('curent_address');
			$order->state = $this->getRequest()->getParam('state');
			$order->save();
			$this->_redirect($this->getRequest()->getParam('redirect_to'));
		}
		else
			$this->getResponce()->setParam('order', get_object_vars($order));
			
		$this->getResponce()->setTemplate('admin/order.html');
		$this->getResponce()->setParam('redirect_to', $this->getRequest()->getParam('redirect_to'));
	}
}