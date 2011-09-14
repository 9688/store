<?php

class Action {
	public $controller;
	public $method;
	public $params;
	
	function __construct($controller, $method, $params = array()) {
		$this->controller = $controller;
		$this->method = $method;
		$this->params = $params;
	}
	
	function getController() {
		return $this->controller;
	}
	
	function getMethod() {
		return $this->method;
	}
	
	function getParams(){
		return $this->params;
	}
}

class Router {
	public $routes;
	
	function addRoute($route) {
		foreach ( $route as $key => $val )
			$this->routes [$key] = $val;
	}
	
	function route(&$request) {
		$reg_src_arg = ':([' . CONTENTS_OF_THE_PARAM_NAME . ']+<.+>)';
		$reg_dest_arg = substr ( $reg_src_arg, 1 );
		$url = $request->getURL ();
		$next_p = $this->routes;
		$pattern = '';
		$pattern_p = '';
		
		while ( is_array ( $next_p ) ) {
			$find = false;
			foreach ( $next_p as $key => $val ) {
				$find = false;
				$k = ereg_replace(':['.CONTENTS_OF_THE_PARAM_NAME.']+<', '(', $key);
				$k = ereg_replace('>', ')', $k);
				
				//$k = ereg_replace ( $reg_src_arg, $reg_dest_arg, $key );
				
				if (ereg ( $pattern . $k , $url )) {
					$next_p = $next_p [$key];
					$pattern_p .= $key;
					$pattern .= $k;
					$find = true;
					break;
				}
			}
			
			if(!$find)
				if(DEBUG)
					throw new Exception ( "route $pattern does not exist for $url", 0 );
				else
					header('Location: /error_404', false);
		}
		
				
		ereg ( $pattern, $url, $vals );
		$keys = array();
		preg_match_all( '/:(['.CONTENTS_OF_THE_PARAM_NAME.']+)/', $pattern_p, $keys );
		$keys = $keys[1];
		ereg ( "$reg_dest_arg/$", $url, $action );
		
		$res = Loader::parse ( $next_p->getController () );
		$request->setModuleName ( $res ['module'] );
		$request->setControllerName ( $res ['filename'] );
		$request->setAction ( $next_p->getMethod () );
		$request->setParams ( $next_p->getParams () );
		$params = array_combine ( $keys, array_slice ( $vals, 1 , count($keys)) );
		$request->setParams ( $params );
	}
}