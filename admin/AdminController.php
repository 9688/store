<?php
require_once 'auth/User.php';
require_once 'category/Product.php';

define('COUNT_USERS_ON_PAGE', 20);

class AdminController extends Controller{
	
	public function preDispath(){
		if(!$this->getRequest()->user->is_authorized() || $this->getRequest()->user->level_access < User::MODER)
			$this->_redirect(HTTP_404);
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
		$this->getResponce()->setTemplate('admin/create_user.html');
		
		if($this->getRequest()->getParam('action') == 'create'){
			$error = array();
			
			$user = new User($this->getRequest());
			$profile = new Profile($this->getRequest());
			
			if($this->getRequest()->user->level_access < User::ADMIN)
				$user->level_access = User::BUYER;
				
			if(is_uploaded_file($this->getRequest()->FILES['avatar']['tmp_name'])){
				$res = validate_file($this->getRequest()->FILES['avatar'], MAX_SIZE_AVATAR);
				if($res !== true)
					$error['avatar'] = $res;
			}
				
			if(!$user->is_valid())
				$error = array_merge($error, $user->errors);
				
			if(!$profile->is_valid())
				$error = array_merge($error, $profile->errors);
			
			if(count($error) == 0){
				if(is_uploaded_file($this->getRequest()->FILES['avatar']['tmp_name'])){
					$profile->avatar = upload($this->getRequest()->FILES['avatar'], null, AVATAR_DIR);
					resizeImg($profile->avatar, AVATAR_DIR, 50, 50);
				}
				else
					$profile->avatar = DEFAULT_AVATAR;

				$profile = $profile->create();
				$user->profile_id = $profile->id;
				$user->password = sha1($user->password);
				$user = $user->create();
				$cart = new Cart(array(
					'user_id' => $user->id,
					'state' => Cart::PREPARE
				));
				$cart->create();
				$this->getResponce()->setTemplate('msg.html');
				$this->getResponce()->setParam('text', 'Пользователь успешно создан.');
			}
			else{
				$this->getResponce()->setParams($this->getRequest()->getParams());
				$this->getResponce()->setParam('error', $error);	
			}
		}
		else
			$this->getResponce()->setParams($this->getRequest()->getParams());
	}
	
	public function editUserAction(){
		
		if($this->getRequest()->user->id == $this->getRequest()->getParam('user_id'))
			$this->_redirect('/error_404');
		
		$user = User::getById($this->getRequest()->getParam('user_id'));
		if($user == null){
			$this->getResponce()->setTemplate('msg.html');
			$this->getResponce()->setParam('text', 'Данный пользователь не существует.');
			return;
		}
		
		$profile = $user->profile;
			
		$this->getResponce()->setTemplate('admin/edit_user_profile.html');
			
		if($this->getRequest()->getParam('action') == 'edit'){
			$error = array();
			
			$newprofile = new Profile($this->getRequest());
			$u = new User($this->getRequest());
			$post = $this->getRequest()->POST;
			
			if($u->level_access < User::BUYER || $u->level_access > User::ADMIN){
				$this->_redirect('/error_404');
				return;
			}
			
			if(is_uploaded_file($this->getRequest()->FILES['avatar']['tmp_name'])){
				$res = validate_file($this->getRequest()->FILES['avatar'], MAX_SIZE_AVATAR);
				if($res !== true)
					$error['avatar'] = $res;
			}
				
			if(!$u->is_valid())
				$error = array_merge($error, $user->errors);
				
			if($post['new_password'] != null || $post['repeat_new_password'] != null){
					$u = new User(array(
							'password' => $this->getRequest()->POST['new_password'],
							'repeatpassword' => $this->getRequest()->POST['new_repeat_password']
						));
						
					if(!$u->is_valid_password())
						$error['new_password'] = $u->errors['password'];
					else
						$u->password = sha1($u->password);
			}
				
			if(!$newprofile->is_valid())
				$error = array_merge($error, $newprofile->errors);
				
			if(count($error) == 0){
				if(is_uploaded_file($this->getRequest()->FILES['avatar']['tmp_name'])){
					$newprofile->avatar = upload($this->getRequest()->FILES['avatar'], null, AVATAR_DIR);
					resizeImg($newprofile->avatar, AVATAR_DIR, 50, 50);
					if($profile->avatar !== $newprofile->avatar && Profile::getCountProfilesLinkingToAvatar($profile->avatar) == 1)
						unlink(MEDIA_ROOT.AVATAR_DIR.'/'.$profile->avatar);
				}
				else
					$newprofile->avatar = $profile->avatar;
					
				$newprofile->id = $profile->id;
				$newprofile->save();
				$user->password = $u->password;
				$user->level_access = $u->level_access;
				$user->save();
				$this->getResponce()->setTemplate('msg.html');
				$this->getResponce()->setParam('text', 'Данные пользователя успешно изменены.');
			}
			else{
				$this->getResponce()->setParam($this->getRequest()->getParams());
				$this->getResponce()->setParams(array_merge(
					array('error' => $error), get_object_vars($user), get_object_vars($profile))
				);
			}
		}
		else
			$this->getResponce()->setParams(array_merge(
				get_object_vars($profile),
				get_object_vars($user)
			));
		
		$this->getResponce()->setParam('avatar', AVATAR_URL.'/'.$profile->avatar);
	}
	
	public function deleteUserAction(){
		if($this->getRequest()->getParam('user_id') == null)
			$this->_redirect('/error_404');

		if($this->getRequest()->user->id == $this->getRequest()->getParam('user_id'))
			$this->_redirect('/error_404');
			
		$user = User::getById($this->getRequest()->getParam('user_id'));
		$this->getResponce()->setTemplate('msg.html');
		
		if($user == null)
			$this->getResponce()->setParam('text', 'Данный пользователь не существует.');
		else{
			if(Profile::getCountProfilesLinkingToAvatar($user->profile->avatar) == 1)
				unlink(MEDIA_ROOT.AVATAR_DIR.'/'.$user->profile->avatar);
				
			$user->delete();
			$this->getResponce()->setParam('text', 'Пользователь успешно удален.');	
		}
	}
	
	public function showUserInfoAction(){
		$user = User::getById($this->getRequest()->getParam('user_id'));
		
		if($user == null){
			$this->getResponce()->setTemplate('msg.html');
			$this->getResponce()->setParam('text', 'Данный пользователь не существует.');
		}
		else{
			$profile = $user->profile;
			$profile->avatar = AVATAR_URL.'/'.$profile->avatar;
			$this->getResponce()->setTemplate('admin/user.html');
			
			$this->getResponce()->setParams(array(
				'data' => get_object_vars($user),
				'data.profile' => get_object_vars($profile),
			));	
		}
	}
	
	public function indexAction(){
		$this->getResponce()->setTemplate('admin/index.html');
	}
	
	public function showListProductAction(){
		$id = $this->getRequest()->getParam('id');
		$id = $id == null? 1: $id;
		
		$list = Product::getProductsByCategoryId($id); 
		if($list != null){
			$this->getResponce()->setTemplate('admin/products.html');
			$this->getResponce()->setParam('products', $list);
		}
		else{
			$this->getResponce()->setTemplate('msg.html');
			$this->getResponce()->setParam('text', 'Такой категории больше не существует.');
		}
			
	}
}