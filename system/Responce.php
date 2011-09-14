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
	
	public function setParams($args){
		if (is_array($args))
			foreach($args as $k => $v)
				array_push_by_creative_key($this->params, $k, $v, true);
	}
	
	public function setParam($key, $val){
		array_push_by_creative_key($this->params, $key, $val, true);
	}
	
	public function send(){
		if(strlen($this->template) != 0){
			$tmp = $this->header->loadTemplate($this->template);
			echo $tmp->render($this->params);
		}
	}
}