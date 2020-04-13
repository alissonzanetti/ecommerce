<?php

use \Hcode\Page;
use \Hcode\Model\Product;

$app->get('/', function() {
  $products = Product::listAll();

  $page = new Page();
  //Function __construct will call the header.html page
  //Function setTpl("index") will call the index.html page
  $page->setTpl("index", [
      "products"=>Product::checkList($products) 
  ]);
  //Function __destruct will call the footer.html page
});



 ?>
