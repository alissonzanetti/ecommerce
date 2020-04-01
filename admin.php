<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

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

 ?>
