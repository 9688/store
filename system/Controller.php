<?php
class Controller {
	private $request;
	private $responce;
	
	function __construct(&$request, &$responce) {
		$this->request = $request;
		$this->responce = $responce;
		$this->init ();
	}
	
	protected function init() {
	}
	
	public function preDispath() {
	}
	
	public function postDispath() {
	}
	
	function getRequest() {
		return $this->request;
	}
	
	function getResponce() {
		return $this->responce;
	}
	
	function _getParam($key) {
		return $this->request->getAllParams ();
	}
	
	function _getParams() {
		return $this->request->getParams ();
	}
	
	function _setParam($key, $val) {
		$this->request->setParam ( $key, $val );
	}
	
	function _hasParam($key) {
		return $this->request->hasParam ( $key );
	}
	
	function _forward($action, $controller = null, $module = null, $params = null) {
		$this->request->setAction ( $action );
		
		if ($controller !== null)
			$this->request->setControllerName ( $controller );
		
		if ($module !== null)
			$this->request->setModuleName ( $module.SEPARATOR.SEPARATOR );
		
		if ($params !== null)
			$this->request->setParams ( $params );
		
		$this->request->processed ( false );
	}
	
	function _redirect($url, $now=true) {
		$this->getRequest()->processed(true);
		header('Location: '.$url, false);
		if($now)
			exit(0);
	}
	
	function _refresh($now = true){
		$this->getRequest()->processed(true);
		header('Refresh: 2');
		if($now)
			exit(0);
	}
	
	function __call($name, $arguments) {
		throw new Exception ( "Doesn't exist method $name", 1 );
	}
}