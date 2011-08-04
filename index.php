<?php
require_once 'system/Db_adapter.php';
require_once 'system/Dispather.php';
require_once 'system/Loader.php';
require_once 'system/Model.php';
require_once 'system/Request.php';
require_once 'system/Responce.php';
require_once 'system/Router.php';
require_once 'system/Controller.php';
require_once 'sittings.php';
require_once 'system/functions.php';

$url = array(
	'^/$' => new Action('IndexController', 'index'),
	'^/registration$' => new Action('auth_AuthenticationController', 'registration'),
	'^/login$' => new Action('auth_AuthenticationController', 'login'),
	'^/logout$' => new Action('auth_AuthenticationController', 'logout'),
	'^/administration' => array(
				'$' => new Action('admin_AdminController', 'index'),
				'/users/:group<[A-Za-z]+>/:page<[0-9]+>$' => new Action('admin_AdminController', 'showListUsers'),
				'/create/user' => new Action('admin_AdminController', 'createUser')
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

