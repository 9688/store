<?php
require_once 'auth/User.php';
class IndexController extends Controller{
	
	public function preDispath(){
		$this->getResponce()->setTemplate('index.html');
	}
	
	function indexAction(){
	}
}