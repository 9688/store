<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL ^E_NOTICE);
require_once 'system/Db_adapter.php';
require_once 'system/Model.php';
require_once 'system/Controller.php';
require_once 'system/Dispather.php';
require_once 'system/Loader.php';
require_once 'system/Request.php';
require_once 'system/Responce.php';
require_once 'system/Router.php';
require_once 'sittings.php';
require_once 'system/functions.php';
require_once 'system/img_resize.php';
require_once 'category/Comments.php';

$url = array(
	'^/error_404$' => new Action('indexController', 'notFound'),
	'^/grabber/$'=> new Action('grabber_GrabberController', 'index'),
	'^/grabber/grab$' => new Action('grabber_GrabberController', 'grab'),
	'^/registration$' => new Action('auth_AuthenticationController', 'registration'),
	'^/login$' => new Action('auth_AuthenticationController', 'login'),
	'^/logout$' => new Action('auth_AuthenticationController', 'logout'),
	'^/edit/profile$' => new Action('auth_ProfileController', 'edit'),
	'^/my_cart/add/:product_id<[0-9]+>$' => new Action('cart_CartController', 'add'),
	'^/my_cart/pop/:product_id<[0-9]+>$' => new Action('cart_CartController', 'pop'),
	'^/my_cart/refresh$' => new Action('cart_CartController', 'refresh'),
	'^/my_cart/erase$' => new Action('cart_CartController', 'erase'),
	'^/my_cart$' => new Action('cart_CartController', 'index'),
	'^/my_cart/order$' => new Action('cart_CartController', 'order'),
	'^/my_cart/history/:page<[0-9]+>$' => new Action('cart_CartController', 'history'),
	'^/my_cart/history/cart/:id<[0-9]+>$' => new Action('cart_CartController', 'MoreCartHistory'),
	'^/comment/:comment_id<[0-9.]+>/:mark<(up|down)>$' =>new Action('category_CommentController', 'rating'),
	'^/voted/:type<product>/:id<[0-9]+>/:mark<(up|down)>' => new Action('voted_VotedController', 'voted'),
	'^/administration' => array(
				'$' => new Action('admin_AdminController', 'index'),
				'/users/:group<[A-Za-z]+>/:page<[0-9]+>$' => new Action('admin_AdminController', 'showListUsers'),
				'/create/user' => new Action('admin_AdminController', 'createUser'),
				'/edit/user/:user_id<[0-9]+>$' => new Action('admin_AdminController', 'editUser'),
				'/delete/user/:user_id<[0-9]+>$' => new Action('admin_AdminController', 'deleteUser'),
				'/info/user/:user_id<[0-9]+>$' => new Action('admin_AdminController', 'showUserInfo'),
				'/categories$' => new Action('category_CategoryController', 'index'),
				'/create/categoty$' => new Action('category_categoryController', 'create'),
				'/edit/category$' => new Action('category_categoryController', 'edit'),
				'/delete/category$' => new Action('category_categoryController', 'delete'),
				'/delete/product/:id<[0-9]+>$' => new Action('category_ProductController', 'delete'),
				'/edit/product/:id<[0-9]+>$' => new Action('category_ProductController', 'edit'),
				'/info/product/:id<[0-9]+>$' => new Action('category_ProductController', 'info'),
				'/orders/:page<[0-9]+>$' => new Action('cart_OrderController', 'index'),
				'/edit/order/:id<[0-9]+>$' => new Action('cart_OrderController', 'setState'),
			),
	'^/product/:id<[0-9]+>' => array(
			'$' => new Action('category_ProductController', 'view'),
			'/add/comment/:parent_comment_id<[\.0-9]+>$' => new Action('category_CommentController', 'create')
	)
);

Loader::registerAutoload();
$DB_HEADER = new Db_adapter();
try{
	$request = new Request($_SERVER, $_GET, $_POST, $_FILES, $_COOKIE, $_SESSION, $_ENV);
	$request->getUser();
	$responce = new Responce();
	
	$router = new Router();
	
	foreach(CategoryController::getLinkRouteCategories() as $k => $val){
		$router->addRoute(array(
			'^/administration/products'.$val['link'].'/:page<[0-9]+>$' => new Action(
				'category_ProductController', 'adminList', array('category' => $val)),
			'^/administration/products'.$val['link'].'/create$' => new Action('category_ProductController',
				'create', array('category' => $val)),
		
			'^'.$val['link'].'/:page<[0-9]*>$' => new Action('IndexController', 'index', array('category' => $val))
		));
	}
	
	$router->addRoute($url);
	$router->route($request);
	
	$dispather = new Dispatcher($request, $responce);
	$dispather->preDispatch();
	$dispather->dispath();
	$dispather->postDispach();
}catch(Exception $e){
	echo $e;
}

