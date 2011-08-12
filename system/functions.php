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


function validate_file($file, $maxsize){
	if(!preg_match('/image\//',$file['type']))
		return 'Файл имеет недопустимый формат.';
	elseif($file['size'] > $maxsize * 1024 *1024)
		return 'Файл не должен превышать '.$maxsize.' мб.';
	else 
		return true;
}
	
function upload($file, $newname=null, $dir=null){
	
	if($newname == null)
		$newname = sha1_file($file['tmp_name']);
		
	$newname .= '.'.substr($file['type'], 6);	
		
	$path = MEDIA_ROOT.($dir == null? '/': $dir);
	
	if(!file_exists($path.'/'.$newname))
		move_uploaded_file($file['tmp_name'], $path.'/'.$newname);
	
	return $newname;
}
	
function resizeImg($filename, $dir, $w, $h){
	img_resize(MEDIA_ROOT.$dir.'/'.$filename, MEDIA_ROOT.$dir.'/'.$filename, $w, $h);
}