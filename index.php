<?php

//Enable SESSION use
session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
  $page = new Page();
  //Function __construct will call the header.html page
  //Function setTpl("index") will call the index.html page
  $page->setTpl("index");
  //Function __destruct will call the footer.html page
});

$app->get('/admin', function() {
  //Only once logged we verify the SESSION
  User:: verifyLogin();
  $page = new PageAdmin();
  $page->setTpl("index");
});

$app->get('/admin/login', function() {
  $page = new PageAdmin([
    //Disable default header/footer from Page class
    "header"=>false,
    "footer"=>false
  ]);
  $page->setTpl("login");
});

$app->post('/admin/login', function() {
  //Class User with static method Login
  //Receive POST from Login
  User::login($_POST["login"], $_POST["password"]);
  //Redirect page
  header("Location: /admin");
  exit;
});

$app->get('/admin/logout', function() {
  User::logout();
  header("Location: /admin/login");
  exit;
});

//List all users
$app->get('/admin/users', function() {
  User::verifyLogin();
  $users = User::listAll();
  $page = new PageAdmin();
  $page->setTpl("users", array(
    "users"=>$users
  ));
});

//Create users
$app->get("/admin/users/create", function() {
  User::verifyLogin();
  $page = new PageAdmin();
  $page->setTpl("users-create");
});

//Delete users via POST
//Pass iduser as an argument
//Take care the order. If /delete were not declared before /:iduser/, it will never be executed
$app->get('/admin/users/:iduser/delete', function($iduser) {
  User::verifyLogin();
  $user = new User();
  $user->get((int)$iduser);
  $user->delete();
  header("Location: /admin/users");
  exit;
});

//Update users
//Pass iduser as an argument
$app->get('/admin/users/:iduser', function($iduser) {
  User::verifyLogin();
  $user  = new User();
  $user->get((int)$iduser);
  $page = new PageAdmin();
  $page->setTpl("users-update", array(
    "user"=>$user->getValues()
  ));
});

//Create users via POST
$app->post('/admin/users/create', function() {
  /*
  User::verifyLogin();
  //var_dump($_POST);
  $user = new User();
  //Verify if user is an admin or not
  $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
  $user->setData($_POST);
  $user->save();
  //var_dump($user);
  header("Location: /admin/users");
  exit;
  */
  User::verifyLogin();
	$user = new User();
 	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
 	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, ["cost"=>12]);
 	$user->setData($_POST);
	$user->save();
	header("Location: /admin/users");
 	exit;
});

//Update users via POST
//Pass iduser as an argument
$app->post('/admin/users/:iduser', function($iduser) {
  User::verifyLogin();
  $user = new User();
  $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
  $user->get((int)$iduser);
  $user->setData($_POST);
  $user->update();
  header("Location: /admin/users");
  exit;
});

$app->get("/admin/forgot", function() {
  $page = new PageAdmin([
    "header"=>false,
    "footer"=>false
  ]);
  $page->setTpl("forgot");
});

$app->post("/admin/forgot", function() {
  $user = User::getForgot($_POST["email"]);
  header("Location: /admin/forgot/sent");
  exit;
});

$app->get("/admin/forgot/sent", function() {
  $page = new PageAdmin([
    "header"=>false,
    "footer"=>false
  ]);
  $page->setTpl("forgot-sent");
});

$app->get("/admin/forgot/reset", function() {
  //Verify if the recovery code is valid
  $user = User::validForgotDecrypt($_GET["code"]);
  $page = new PageAdmin([
    "header"=>false,
    "footer"=>false
  ]);
  //Pass the name and code to the template
  $page->setTpl("forgot-reset", array(
    "name"=>$user["desperson"],
    "code"=>$_GET["code"]
  ));
});

$app->post("/admin/forgot/reset", function(){
	$forgot = User::validForgotDecrypt($_POST["code"]);
	User::setForgotUsed($forgot["idrecovery"]);
	$user = new User();
	$user->get((int)$forgot["iduser"]);
	//$password = User::getPasswordHash($_POST["password"]);
	//$user->setPassword($_POST["password"]);
  $password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
    "const"=>12
  ]);
  $user->setPassword($password);
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot-reset-success");
});

$app->get("/admin/categories", function(){
  User::verifyLogin();
  $categories = Category::listAll();
  $page = new PageAdmin();
	$page->setTpl("categories", [
    "categories"=>$categories
  ]);
});

$app->get("/admin/categories/create", function(){
  User::verifyLogin();
  $page = new PageAdmin();
	$page->setTpl("categories-create");
});

$app->post("/admin/categories/create", function(){
  User::verifyLogin();
  $category = new Category();
  $category->setData($_POST);
  $category->save();
  header("Location: /admin/categories");
  exit;
});

$app->get("/admin/categories/:idcategory/delete", function($idcategory){
  User::verifyLogin();
  $category = new Category();
  $category->get((int)$idcategory);
  $category->delete();
  header("Location: /admin/categories");
  exit;
});

$app->get("/admin/categories/:idcategory", function($idcategory){
  User::verifyLogin();
  $category = new Category();
  $category->get((int)$idcategory);
  $page = new PageAdmin();
  $page->setTpl("categories-update", [
    "category"=>$category->getValues()
  ]);
});

$app->post("/admin/categories/:idcategory", function($idcategory){
  User::verifyLogin();
  $category = new Category();
  $category->get((int)$idcategory);
  $category->setData($_POST);
  $category->save();
  header("Location: /admin/categories");
  exit;
});

$app->run();

 ?>
