<?php
class errorController extends Controller{
	public function notFoundAction(){
		$this->getResponce()->setTemplate('404.html');
	}
	
	public static function addError($text){
		if(!DEBUG){
			$f = fopen(ERROR_LOG_FILE, 'a');
			fwrite($f, '[ '.date( 'H\hi l d F', time() ).']\n'.$text.'\n');
			fclose($f);
		}
		else
			echo $text.'\n';
	}
}