<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;


$app->get("/admin/users/:iduser/password", function($iduser){
  User::verifyLogin();
  $user = new User();
  $user->get((int)$iduser);
  $page = new PageAdmin();
  $page->setTpl("users-password", [
    'user'=>$user->getValues(),
    'msgError'=>User::getError(),
    'msgSuccess'=>User::getSuccess()
  ]);
});

$app->post("/admin/users/:iduser/password", function($iduser){
  User::verifyLogin();
  if(!isset($_POST['despassword']) || $_POST['despassword'] === ''){
    User::setError("Preencha a nova senha.");
    header("Location: /admin/users/$iduser/password");
    exit;
  }
  if(!isset($_POST['despassword-confirm']) || $_POST['despassword-confirm'] === ''){
    User::setError("Confirme a nova senha.");
    header("Location: /admin/users/$iduser/password");
    exit;
  }
  if($_POST['despassword'] != $_POST['despassword-confirm']){
    User::setError("Confirme corretamente as senhas.");
    header("Location: /admin/users/$iduser/password");
    exit;
  }
  $user = new User();
  $user->get((int)$iduser);
  $user->setPassword(User::getPasswordHash($_POST['despassword']));
  User::setSuccess("Senha alterada com sucesso");
  $page = new PageAdmin();
  $page->setTpl("users-password", [
    'user'=>$user->getValues(),
    'msgError'=>User::getError(),
    'msgSuccess'=>User::getSuccess()
  ]);
});

//List all users with Pagination
$app->get('/admin/users', function() {
  User::verifyLogin();
  $search = (isset($_GET['search'])) ? $_GET['search'] : "";
  $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
  if($search != ''){
    $pagination = User::getUsersPageSearch($search, $page);
  } else {
    $pagination = User::getUsersPage($page);
  }
  $pages = [];
  for($x = 0; $x < $pagination['pages']; $x++){
    array_push($pages, [
      'href'=>'/admin/users?'.http_build_query([
        'page'=>$x+1,
        'search'=>$search
      ]),
      'text'=>$x+1
    ]);
  }
  $page = new PageAdmin();
  $page->setTpl("users", array(
    "users"=>$pagination['data'],
    "search"=>$search,
    "pages"=>$pages
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
