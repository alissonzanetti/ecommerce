<?php

use \Hcode\Page;

$app->get('/', function() {
  $page = new Page();
  //Function __construct will call the header.html page
  //Function setTpl("index") will call the index.html page
  $page->setTpl("index");
  //Function __destruct will call the footer.html page
});

 ?>
