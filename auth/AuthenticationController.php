<?php
require_once 'auth/User.php';

class AuthenticationController extends Controller{
	
	public function registrationAction(){
		$this->getResponce()->setTemplate('auth/registration.html');
		
		
		if($this->getRequest()->user->is_authorized() && $this->getRequest()->user->level_access == User::BUYER)
			$this->_redirect('/');
		
		$user = new User($this->getRequest()->getParams());
		
		if($this->getRequest()->POST['action'] == 'register'){
			if($user->is_valid()){
				if($this->getRequest()->user->level_access == User::BUYER)
					$user->level_access = User::BUYER;
					
				$redirect_to = $this->getRequest()->getParam('redirect_to');
				$redirect_to = $redirect_to == null? '/': $redirect_to;
				
				$user->create();
				$this->_redirect($redirect_to);
			}
			else{
				$param = array_merge($this->getRequest()->getParams(), array('error' => $user->errors));
				$this->getResponce()->setParams($param);
			}
		}
		elseif($this->getRequest()->user->level_access > User::BUYER)
			$this->getResponce()->setParams($this->getRequest()->getParams());
	}
	
	public function loginAction(){
		if($this->getRequest()->user->is_authorized())
			$this->_redirect('/');
		
		$user = new User($this->getRequest());
		$user = $user->getByLogin();
			
		if($user == null){
			$this->getResponce()->setTemplate('auth/msg.html');
			$this->getResponce()->setParams(array('type' => 'error', 'text' => 'Неверный логин или пароль.'));
			return;
		}
		
		$sid = randString();
		$user->sid = $sid;
		$user->save();
		
		$r = setcookie('sid', $sid, time() + 60*60*24*31*48,'/');
		
		$this->_redirect('/');
	}
	
	public function logoutAction(){
		
		if($this->getRequest()->user->is_authorized())
			$r = setcookie('sid', '', 0, '/');
		
		$this->_redirect('/');
	}
}






