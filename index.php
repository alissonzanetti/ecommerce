<?php

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
  $page = new Page();

  //Function __construct will call the header.html page

  //Function setTpl("index") will call the index.html page
  $page->setTpl("index");

  //Function __destruct will call the footer.html page

});

$app->run();

 ?>
