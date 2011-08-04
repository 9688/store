<?php
class Responce{
	var $template;
	var $params;
	var $header;
	
	function __construct(){
		$this->template = '';
		$this->params = array();
		$loader = new Twig_Loader_Filesystem(TEMPLATES_ROOT);
		$this->header = new Twig_Environment($loader, array('cache' => DEBUG? false: TEMPLATES_ROOT.'/compilation_cache'));
		$redirected = false;
	}
	
	public function setTemplate($path){
		$this->template = $path;
	}
	
	public function setParams($key, $val = null){
		if ($val == null && is_array($key))
			foreach($key as $k => $v)
				$this->params[$k] = $v;
		else
			$this->params[$key] = $val;
	}
	
	public function setParam($key, $val){
		$this->params[$key] = $val;
	}
	
	public function send(){
		if(strlen($this->template) != 0){
			$tmp = $this->header->loadTemplate($this->template);
			echo $tmp->render($this->params);
		}
	}
}