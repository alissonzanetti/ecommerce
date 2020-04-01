<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

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

 ?>
