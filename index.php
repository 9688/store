<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL ^E_NOTICE);
require_once 'system/Db_adapter.php';
require_once 'system/Model.php';
require_once 'system/Dispather.php';
require_once 'system/Loader.php';
require_once 'system/Request.php';
require_once 'system/Responce.php';
require_once 'system/Router.php';
require_once 'system/Controller.php';
require_once 'sittings.php';
require_once 'system/functions.php';
require_once 'system/img_resize.php';

$url = array(
	'^/$' => new Action('IndexController', 'index'),
	'^/registration$' => new Action('auth_AuthenticationController', 'registration'),
	'^/login$' => new Action('auth_AuthenticationController', 'login'),
	'^/logout$' => new Action('auth_AuthenticationController', 'logout'),
	'^/edit/profile$' => new Action('auth_ProfileController', 'edit'),
	'^/administration' => array(
				'$' => new Action('admin_AdminController', 'index'),
				'/users/:group<[A-Za-z]+>/:page<[0-9]+>$' => new Action('admin_AdminController', 'showListUsers'),
				'/create/user' => new Action('admin_AdminController', 'createUser'),
				'/edit/user/:user_id<[0-9]+>$' => new Action('admin_AdminController', 'editUser'),
				'/delete/user/:user_id<[0-9]+>$' => new Action('admin_AdminController', 'deleteUser'),
				'/info/user/:user_id<[0-9]+>$' => new Action('admin_AdminController', 'showUserInfo')
			),
	'404$' => new Action('errorController', 'notFound')
);

Loader::registerAutoload();
$DB_HEADER = new Db_adapter();
try{
	$request = new Request($_SERVER, $_GET, $_POST, $_FILES, $_COOKIE, $_SESSION, $_ENV);
	$request->getUser();
	$responce = new Responce();
	
	$router = new Router();
	$router->addRoute($url);
	$router->route($request);
	
	$dispather = new Dispatcher($request, $responce);
	$dispather->preDispatch();
	$dispather->dispath();
	$dispather->postDispach();
}catch(Exception $e){
	echo $e;
}

