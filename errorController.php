<?php
class errorController extends Controller{
	public function notFoundAction(){
		$this->getResponce()->setTemplate('404.html');
	}
}