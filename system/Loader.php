<?php

Loader::$include_paths = array ();

class Loader {
	private static $autoload = false;
	public static $include_paths = null;
	
	public static function loadFile($filename, $dir = '') {
		$res = self::parse ( $filename );
		
		$path = ROOT . $dir . $res ['module'] . $res ['filename'] . '.php';
		
		if (! in_array ( $path, self::$include_paths ) && ! include $path) {
			throw new Exception ( "file does't exist $path" );
			Loader::$include_paths [] = $path;
		}
		
		return $res['filename'];
	}
	
	public static function loadClass($classname, $dir = '') {
		$res = Loader::parse ( $classname );
		$classname = $res ['filename'];
		
		$path = APLICATION_ROOT.$dir.$res['module'].$classname.'.php';
		if (! in_array ( $path, Loader::$include_paths )) {
			if (! require_once $path)
				throw new Exception ( "file does't exist $path for class $classname" );
			
			Loader::$include_paths [] = $path;
			
			if (! class_exists ( $classname ))
				throw new Exception ( "class $classname doesn't exist." );
		}
		return $classname;
	}
	
	public static function parse($classname) {
		
		$res = ereg_replace ( '(' . CONTENTS_OF_THE_SEPARATOR . ')', SEPARATOR, $classname );
		ereg ( '([' . CONTENTS_OF_THE_CLASS_NAME . ']+)+$', $res, $name );
		
		$name = $name [1];
		
		$start = count ( $res ) - count ( $name );
		$module = ereg_replace ( $name, '', substr ( $res, $start ) );
		
		if (ereg ( '^Twig', $module ))
			$module = TEMPLATING_ROOT . $module;
		
		return array ('module' => $module, 'filename' => $name );
	}
	
	public static function registerAutoload(){
		ini_set ( 'unserialize_callback_func', 'spl_autoload_call' );
		spl_autoload_register ( array (new self (), 'autoload' ) );
	}
	
	public static function autoload($class) {
		if(ereg('^Twig', $class)){
			Loader::loadFile($class);
			return;
		}
		self::loadClass($class);
	}

}
