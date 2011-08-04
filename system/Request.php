<?php
require_once 'auth/User.php';
class Request {
	public $SERVER;
	public $GET;
	public $POST;
	public $FILES;
	public $COOKIE;
	public $SESSION;
	public $ENV;
	var $module;
	var $controller;
	var $action;
	public $PARAMS;
	public $user;
	var $processed;
	
	function __construct($server, $get, $post, $files, $cookies, $session, $env){
		$this->SERVER = $server;
		$this->GET = $get;
		$this->POST = $post;
		$this->FILES = $files;
		$this->COOKIE = $cookies;
		$this->SESSION = $session;
		$this->ENV = $env;
		$this->PARAMS = array_merge ( $post, $get );
		$this->processed = false;
		$this->user = null;
	}
	
	public function getUser(){
		$this->user = new User($this);
		$this->user = $this->user->get_authorized(array_merge($this->COOKIE, $this->POST));
	}
	
	public function is_processed() {
		return $this->processed;
	}
	
	public function processed($val) {
		$this->processed = $val;
	}
	
	public function setModuleName($name) {
		$this->module = $name;
	}
	
	public function getModuleName() {
		return $this->module;
	}
	
	public function setControllerName($name) {
		$this->controller = $name;
	}
	
	public function getControllerName() {
		return $this->controller;
	}
	
	public function setAction($action) {
		$this->action = $action;
	}
	
	public function getAction() {
		return $this->action;
	}
	
	public function setParam($key, $val) {
		$this->PARAMS [$key] = $val;
	}
	
	public function getParam($key) {
		return $this->PARAMS [$key];
	}
	
	public function setParams($arg) {
		foreach ( $arg as $key => $val )
			$this->PARAMS [$key] = $val;
	}
	
	public function getParams() {
		if($this->PARAMS == null)
			return array();
		return $this->PARAMS;
	}
	
	public function _hasParam($key) {
		return $this->PARAMS [$key] != null;
	}
	
	public function getPost() {
		if($this->POST == null)
			return array();
		return $this->POST;
	}
	
	public function getGet(){
		if($this->GET == null)
			return array();
		return $this->GET;
	}
	
	public function getCookie() {
		if($this->COOKIE == null)
			return array();
		return $this->COOKIE;
	}
	
	public function getFiles() {
		if($this->FILES == null)
			return array();
		return $this->FILES;
	}
	
	public function getSession() {
		if($this->SESSION == null)
			return array();
		return $this->SESSION;
	}
	
	public function getServer() {
		if($this->SERVER == null)
			return array();
		return $this->SERVER;
	}
	
	public function getEnv() {
		if($this->ENV == null)
			return array();
		return $this->ENV;
	}
	
	public function is_ajax() {
		if (isset ( $this->SERVER ['HTTP_X_REQESTED_WITH'] ))
			if ($_SERVER ['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')
				return true;
		
		return false;
	}
	
	public function getURL() {
		if (isset ( $this->GET ['start_debug'] ) && DEBUG)
			return ereg_replace ( '(.*)' . $this->SERVER ['HTTP_HOST'] . '/' . NAME_PROJECT . $this->SERVER ['SCRIPT_NAME'], '', $this->GET ['original_url'] ) . '/';
		
		return $this->SERVER ['REQUEST_URI'];
	}
}