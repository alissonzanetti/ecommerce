<?php

//Enable SESSION use
session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

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

$app->run();

 ?>
