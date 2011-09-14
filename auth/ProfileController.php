<?php
require_once 'auth/User.php';
require_once 'auth/Profile.php';

class ProfileController extends Controller{
	
	public function fillAction(){
		$this->getResponce()->setTemplate('auth/profile.html');
		session_start();
		
		if(isset($_SESSION['user_id'])){
			$user = User::getById($_SESSION['user_id']);
			$profile = $user->profile;
			$error = array();
			
			if($profile == null){
				errorController::addError('when creating a user profile ID does not exist.');
				$this->_redirect('/');
				return;
			}
			
			$res = $this->edit($profile, array_merge($this->getRequest()->POST, $this->getRequest()->FILES), $error);
			
			if(count($error) == 0){
				unset($_SESSION['user_id']);
				session_destroy();
				$this->getResponce()->setTemplate('msg.html');
				$this->getResponce()->setParam('text', 'Регистрация успешно завершена.');
			}
			else
				$this->getResponce()->setParams(array_merge($this->getRequest()->getParams(), array('error' => $error)));
		}
		else{
			$this->_redirect('/');
			session_destroy();
			return;
		}
		$this->_forward('initPage', 'IndexController', '');
	}
	
	public function edit($profile, $param, &$error){
		$newprofile = new Profile($param);
		$newprofile->id = $profile->id;
		
		if(!$newprofile->is_valid())
			$error = array_merge($newprofile->errors, $error);
		
		if(is_uploaded_file($param['avatar']['tmp_name'])){
			$res = validate_file($param['avatar'], MAX_SIZE_AVATAR);
			if($res !== true)
				$error['avatar'] = $res; 
		}
		
		if(count($error) == 0){
			if(is_uploaded_file($param['avatar']['tmp_name'])){
				$newprofile->avatar = upload($param['avatar'], null, AVATAR_DIR);
				resizeImg($newprofile->avatar, AVATAR_DIR, 50, 50);
				
				if($profile->avatar !== $newprofile->avatar && Profile::getCountProfilesLinkingToAvatar($profile->avatar) == 1)
					unlink(MEDIA_ROOT.AVATAR_DIR.'/'.$profile->avatar);
			}
			else
				$newprofile->avatar = $profile->avatar;
				
			$newprofile->save();
			if($this->getRequest()->getParam('action') == 'edit')
				$this->getResponce()->setParam('user.avatar', AVATAR_URL.'/'.$newprofile->avatar);
			return true;
		}
		else
			return $error;		
	}
	
	public function editAction(){
		$this->getResponce()->setTemplate('auth/profile.html');
		
		if(!$this->getRequest()->user->is_authorized()){
			$this->_redirect('/error_404');
			return;
		}
		
		$user = $this->getRequest()->user;
		$profile = $user->profile;
		
		$error = array();
		
		if($this->getRequest()->getParam('action') == 'edit'){
			$post = $this->getRequest()->POST;
			if(strlen($post['password']) || strlen($post['new_password']) || strlen($post['repeat_new_password']))
				if(sha1($this->getRequest()->POST['password']) === $user->password){
					$u = new User(array(
							'password' => $this->getRequest()->POST['new_password'],
							'repeatpassword' => $this->getRequest()->POST['new_repeat_password']
						));
						
					if(!$u->is_valid_password())
						$error['new_password'] = $u->errors['password'];
					else
						$u->password = sha1($u->password);
				}
				else
					$error['password'] = 'Вы ввели неверный пароль.';
			
			$res = $this->edit($profile, array_merge($this->getRequest()->POST, $this->getRequest()->FILES), $error);
			
			if(count($error) == 0){
				$user->password = $u->password;
				$user->save();
				$this->_redirect('/');
			}
			else 
				$this->getResponce()->setParams(array_merge(
					$this->getRequest()->getParams(),
					array('error' => $error)
				));
		}
		else
			$this->getResponce()->setParams(get_object_vars($profile));

		$this->_forward('initPage', 'IndexController', '');
	}
}	
	