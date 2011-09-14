<?php

require_once 'category/Comments.php';
class CommentController extends Controller{
	public function preDispath(){
		if(!$this->getRequest()->user->is_authorized())
			$this->_redirect(HTTP_404);
	}

	public function createAction(){
		if($this->getRequest()->getParam('action') == 'create'){
			
			if(strlen($this->getRequest()->getParam('text')) > 0){
				$comments = new Comments();
				$comments->addComment(
					$this->getRequest()->getParam('id'),
					$this->getRequest()->getParam('parent_comment_id'),
					$this->getRequest()->getParam('text'),
					$this->getRequest()->user
				);
			}
	
			$this->_redirect($this->getRequest()->getParam('redirect_to', '/'));
		}
		else{
			$this->getResponce()->setTemplate('product.html');
		
			$this->getResponce()->setParams(array(
				'action' => 'add-comment',
				'product_link' => '/products/'.$this->getRequest()->getParam('id'),
				'parent_comment_id' => $this->getRequest()->getParam('parent_comment_id')
			));
			$this->_forward('view', 'ProductController', 'category');
		}
	}
	
	public function ratingAction(){
		$post = $this->getRequest()->getParams();
		
		if($this->getRequest()->user->addRating($post['product_id'],$post['comment_id'], $post['mark'] == 'up'? 1: -1))
			$this->_redirect($this->getRequest()->getParam('redirect_to'));
		else
			$this->_redirect(HTTP_404);
	}
}