<?php
require_once 'auth/Profile.php';
require_once 'auth/User.php';

class AuthenticationController extends Controller{
	
	public function registrationAction(){
		$this->getResponce()->setTemplate('auth/registration.html');
		
		
		if($this->getRequest()->user->is_authorized())
			$this->_redirect('/');
		
		$user = new User($this->getRequest()->getParams());
		
		if($this->getRequest()->getParam('action') == 'register'){
			if($user->is_valid()){
				$user->level_access = User::BUYER;
				$user->password = sha1($user->password);
				
				$profile = new Profile();
				$profile = $profile->create();
				$user->profile_id = $profile->id;
				$user = $user->create();
				
				session_start();
				$_SESSION['user_id'] = $user->id;
				
				$this->getResponce()->setTemplate('auth/profile.html');
				$this->getResponce()->setParam('action', 'fill_profile');
			}
			else{
				$this->getResponce()->setParam('error', $user->errors);
				$this->getResponce()->setParams($this->getRequest()->getParams());
			}
		}
		elseif($this->getRequest()->getParam('action') == 'fill_profile'){
			$this->_forward('fill', 'ProfileController');
		}
	}
	
	public function loginAction(){
		if($this->getRequest()->user->is_authorized())
			$this->_redirect('/');

		$user = User::getByLogin($this->getRequest()->POST['login']);
			
		if($user != null && sha1($this->getRequest()->POST['password']) == $user->password){
			$sid = randString();
			$user->sid = $sid;
			$user->save();
			
			$r = setcookie('sid', $sid, time() + 60*60*24*31*48,'/');
			
			$this->_redirect('/');
			
		}
		else{
			$this->getResponce()->setTemplate('msg.html');
			$this->getResponce()->setParams(array('type' => 'error', 'text' => 'Неверный логин или пароль.'));
		}
	}
	
	public function logoutAction(){
		
		if($this->getRequest()->user->is_authorized())
			$r = setcookie('sid', '', 0, '/');
		
		$this->_redirect('/');
	}
}






