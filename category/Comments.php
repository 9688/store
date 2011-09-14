<?php

require_once 'auth/User.php';

class Comments{
	private $collection;
	
	public function __construct(){
		$this->connection = new Mongo('localhost:27017');
		$this->db = $this->connection->store;
		$this->collection = $this->db->comments;
	}
	
	public function initComments($product_id, $users = array(), $comments = array()){
		$this->collection->insert(array(
			'product_id' => $product_id,
			'user_list' => $users, 
			'comments' => $comments
		));
	}
	
	public function addComment($product_id, $parent_comment_id, $text, $user){
		$data = array('text' => $text, 'user_id' => $user->id, 'id' => '1', 'date' => date('d.n.Y G:i:s'), 'voted' => array());
		
		$obj = $this->collection->findOne(array('product_id' => $product_id));
		
		if($obj == null)
			$this->initComments($product_id, array($user->id), array($data));
		else{
			if($parent_comment_id != 0)
				$pcomment = &$this->findComment($obj['comments'], $parent_comment_id);
				
			if($pcomment == null){
				$pcomment = &$obj;
				$data['id'] = '';
			}
			else 
				$data['id'] = $pcomment['id'].'.';
			
			if(count($pcomment['comments']) == 0)
				$data['id'] .= '1';
			else{
				preg_match('/[^.]$/', $pcomment['comments'][count($pcomment['comments']) - 1]['id'], $match);
				$data['id'] .= $match[0] + 1;
			}
			$pcomment['comments'][] = $data;
			
			if(array_search($user->id, $obj['user_list']) === false)
				$obj['user_list'][] = $user->id;
				
			$this->collection->update(array('product_id' => $product_id), $obj);
		}
	}
	
	private function &findComment(&$con, $id){
		foreach($con as $k => $v)
			if($k !== 'user_list')
				if($v['id'] == $id){
					return $con[$k];
				}
				elseif(is_array($v)){
					$res = &$this->findComment($con[$k], $id);
					if($res !== null)
						return $res;
				}
		return null;
	}
	
	public function getTreeComments($product_id, $cuser_id = null){
		$obj = $this->collection->findOne(array('product_id' => $product_id));
		
		$users = array();
		$res = User::getUsersByIds($obj['user_list']);
		foreach ($res as $k => $v)
			$users[$v['id']] = $v;
			
		$this->rebuildTree($obj['comments'], $users, $cuser_id);
		
		return $obj['comments'];
	}
	
	private function rebuildTree(&$tree, $users, $cuser_id){
		foreach($tree as $k => $v){
			$i = $tree[$k]['user_id'];
			
			if($cuser_id != null){
				if(!strcmp($tree[$k]['user_id'], $cuser_id)){
					$tree[$k]['appraise'] = true;
				}
				else{
					foreach($tree[$k]['voted'] as $voted_users)
						if(!strcmp($cuser_id, $voted_users['user_id'])){
							$tree[$k]['appraise'] = true;
							break;
						}
				}
			}
					
			$tree[$k]['user'] = array(
				'login' => $users[$i]['login'], 
				'avatar' => $users[$i]['avatar'],
				'id' => $users[$i]['id'],
				'rating' => (int)$users[$i]['rating']
			);
			if($v['comments'] !== null){
				$this->rebuildTree($tree[$k]['comments'], $users, $cuser_id);
			}
		}
	}
	
	public function deleteCommensByUserId($id){
		$mapreduce = array(
			'mapreduce' => 'comments',
			'map' => new MongoCode('
				function(){
					for(var i = 0; i < this["user_list"].length; i++)
						if(this["user_list"][i] == '.$id.'){
							emit(this["product_id"], this);
							return;
						}
				}'),
			'reduce' => new MongoCode('function(k, v){
				return v;
			}'),
			'out' => array('replace' => 'temp')
		);
		$mapreduce = $this->db->command($mapreduce);
		$res = $this->db->selectCollection($mapreduce['result'])->find();
		foreach($res as $k => $v){
			$obj = $v['value'];
			$this->removeUserFromComment($obj, $id);
			$pos = array_search($id, $obj['user_list']);
			if($pos !== false)
				unset($obj['user_list'][$pos]);
				
			$this->collection->update(array('product_id' => $obj['product_id']), $obj);
		}
	}
	
	private function removeUserFromComment(&$comment, $id){
		if($comment['comments'] != null)
			foreach($comment['comments'] as $k => $v)
				if($v['user_id'] == $id)
					unset($comment['comments'][$k]);
				else
					$this->removeUserFromComment($comment['comments'][$k], $id);
	}
	
	private function to_obj($cursor){
		$res = null;
		foreach ($cursor as $k => $v)
			$res[$k] = $v;
		return $res;
	}
	
	public function delete($product_id){
		$this->collection->remove(array('product_id' => $product_id));
	}
	
	public function addMark($product_id, $comment_id, $user_id, $mark){
		$obj = $this->collection->findOne(array('product_id' => $product_id));
		$comment = &$this->findComment($obj['comments'], $comment_id);
		$comment['voted'][] = array('user_id' => $user_id,'mark' => $mark);
		$this->collection->update(array('product_id' => $product_id), $obj);
	}
	
	public function isVotedCommentId($product_id, $comment_id, $user_id){
		$obj = $this->collection->findOne(array('product_id' => $product_id));
		$comment = &$this->findComment($obj['comments'], $comment_id);
		foreach ($comment['voted'] as $value)
			if($value['user_id'] == $user_id)
				return true;
				
		return false;
	}
	
	public function getUserIdByCommentId($product_id, $comment_id){
		$obj = $this->collection->findOne(array('product_id' => $product_id));
		$comment = $this->findComment($obj['comments'], $comment_id);
		return $comment['user_id'];
	}
}