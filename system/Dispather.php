<?php

require_once 'auth/Profile.php';
require_once 'category/CategoryController.php';
require_once 'cart/Cart.php';

class Dispatcher {
	var $request;
	var $responce;
	
	function __construct(&$request, &$responce) {
		$this->request = $request;
		$this->responce = $responce;
	}
	
	function dispath() {
		try {
			$lastClassController = Loader::loadClass ( $this->request->getControllerName (), $this->request->getModuleName () );
			
			$last = new $lastClassController ( $this->request, $this->responce );
			$x = get_class_methods ( get_class ( $last ) );
			$last->preDispath();
			
			while ( ! $this->request->is_processed () ) {
				$classController = Loader::loadClass ( $this->request->getControllerName(), $this->request->getModuleName());
				
				$methods = get_class_methods ( $classController );
				$method = $this->request->getAction () . POSTFIX_ACTION_NAME;
				if (! in_array ( $method, $methods )) {
					$cont = $this->request->getControllerName ();
					throw new Exception ( "method of $method doesn't exist in $cont", 1 );
				}
				
				$controller = new $classController ( $this->request, $this->responce );
				$this->request->processed ( true );
				$controller->$method ();
			}
			
			$last->postDispath();
		} catch ( Exception $e ) {
			throw $e;
		}
	}
	
	function preDispatch(){
		if($this->request->user->is_authorized()){
			$this->request->user->profile = Profile::getById($this->request->user->profile_id);
			$profile = clone $this->request->user->profile;
			
			$profile->avatar = AVATAR_URL.'/'.$this->request->user->profile->avatar;  
			$this->request->user->cart = Cart::getCurentByUserId($this->request->user->id);
			
			$this->responce->setParam(
				'user',
				 array(
				 	'id' => $this->request->user->id,
				 	'login' => $this->request->user->login,
				 	'level_access' => $this->request->user->level_access,
				 	'rating' => $this->request->user->rating,
				 	'profile' => get_object_vars($profile),
				 	'cart' => get_object_vars($this->request->user->cart)
				 )
			);
		}
	}
	
	function postDispach(){
		$this->responce->send();
	}
}