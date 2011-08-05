<?php
require_once 'auth/User.php';

define('COUNT_USERS_ON_PAGE', 10);

class AdminController extends Controller{
	
	public function preDispath(){
		if(!$this->getRequest()->user->is_authorized() || $this->getRequest()->user->level_access < User::MODER)
			$this->_redirect(HTTP_404);
		
		$this->getResponce()->setParam('category', array(
			'link' => '',
			'name' => '',
			'subcaterories' => array(
				array(
					'name' => 'Пользователи',
					'link' => '/administration/users/all/1',
					'subcaterories' => array(
						array('name' => 'Покупатели', 'link' => '/administration/users/buyers/1'),
						array('name' => 'Модераторы', 'link' => '/administration/users/moders/1'),
						array('name' => 'Администраторы', 'link' => '/administration/users/admins/1')
					)
				)
			)
		));
	}
	
	public function showListUsersAction(){
		$this->getResponce()->setTemplate('admin/users.html');
		
		$curent_page = $this->getRequest()->getParam('page') == null? 1: $this->getRequest()->getParam('page');
		
		switch($this->getRequest()->getParam('group')){
			case 'all':
				$level_access = User::REGISTERED;
				break;
			case 'buyers':
				$level_access = User::BUYER;
				break;
			case 'moders':
				$level_access = User::MODER;
				break;
			case 'admins':
				$level_access = User::ADMIN;
				break;
			default:
				$this->_redirect(HTTP_404);
		}
		
		$user_list = User::getListFromAccess($level_access);
		
		$count_page = (int)(count($user_list) / COUNT_USERS_ON_PAGE) + 
			(count($user_list) % COUNT_USERS_ON_PAGE == 0? 0: 1);
			
		$user_list = array_splice($user_list, ($curent_page - 1) * COUNT_USERS_ON_PAGE, COUNT_USERS_ON_PAGE);
		 
		$this->getResponce()->setParams(array(
			'users' => $user_list,
			'count_page' => $count_page,
			'curent_page' => $curent_page,
			'level_access' => $level_access,
			'group' => $this->getRequest()->getParam('group')
		));
	}
	
	public function createUserAction(){
		$this->getResponce()->setParam('action', 'create');
		$this->getResponce()->setParam('redirect_to', getenv("HTTP_REFERER"));
		$this->_forward('registration', 'AuthenticationController', 'auth');
	}
	
	public function indexAction(){
		$this->getResponce()->setTemplate('admin/index.html');
	}
}