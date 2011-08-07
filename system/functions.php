<?php
function randString(){
  $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
  $numChars = strlen($chars);
  $string = '';
  for ($i = 0; $i < 30; $i++) {
    $string .= substr($chars, rand(1, $numChars) - 1, 1);
  }
  return sha1($string);
}

class Image{
	public $error;
	public $filename;
	public $dir;
	
	public function __construct($filename=null, $dir=null){
		$this->filename = $filename;
		$this->dir = $dir;
		$this->error = null;
	}
	
	public function upload($file, $maxsize, $newname=null, $dir=null){
		if(!isset($file))
			return;
		
		if(!is_uploaded_file($file['tmp_name'])){
			$this->error = 'Файл не загружен.';
			return;
		}
			
		if(!preg_match('/image\//',$file['type'])){
			$this->error = 'Файл имеет недопустимый формат.';
			return;
		}
		

		if($file['size'] > $maxsize * 1024 *1024){
			$this->error = 'Файл не должен превышать '.$maxsize.' мб.';
			return;
		}
		
		if($newname == null)
			$newname = sha1_file($file['tmp_name']);
			
		$newname .= '.'.substr($file['type'], 6);	
			
		$path = MEDIA_ROOT.($dir == null? '/': $dir);
		
		if(!file_exists($path.'/'.$newname))
			move_uploaded_file($file['tmp_name'], $path.'/'.$newname);
		
		$this->dir = $path;
		$this->filename = $newname;
	}
	
	public function resize($w, $h){
		img_resize($this->dir.'/'.$this->filename, $this->dir.'/'.$this->filename, $w, $h);
	}
}