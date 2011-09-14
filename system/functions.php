<?php
function randString($len=30){
  $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
  $numChars = strlen($chars);
  $string = '';
  for ($i = 0; $i < $len; $i++) {
    $string .= substr($chars, rand(1, $numChars) - 1, 1);
  }
  return $string;
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

function array_push_by_creative_key(&$con, $key, $val, $replace=false){ 
	$k = array();
	if(preg_match('/^[^.]+/', $key, $k))
		if(strlen($k[0]) === strlen($key))
			if($con[$k[0]] == null || $replace)
				$con[$k[0]] = $val;
			else
				$con[$k[0]] += $val;
		else
			array_push_by_creative_key($con[$k[0]], substr($key, strlen($k[0]) + 1), $val, $replace);
}

function pagination($fieldscount, $fields_on_page_count, $curent_page, $pagedisprange=3, $url){
	$curent_page = isset($curent_page)? $curent_page: 1;
	$result = array();
	$count_on_page = ceil($fieldscount / $fields_on_page_count);
	
	$start = $curent_page - $pagedisprange;
	$end = $curent_page + $pagedisprange;
	if($start < 1){
		$end += abs($start) + 1;
		$start = 1; 
		if($end > $count_on_page)
			$end = ($count_on_page > 0? $count_on_page: 1);
	}
	elseif($end > $count_on_page){
		$start -= $end - $count_on_page;
		$end = $count_on_page;
		if($start < 1)
			$start = 1;
	}
	
	if($start != $end){
		if($curent_page > $start)
			$result['start'] = 1;
		if($curent_page < $end)
			$result['end'] = $count_on_page;
	}
	
	$result['curent'] = $curent_page;
	$result['url'] = $url;
	$result['pages'] = range($start, $end);
	return $result;
}