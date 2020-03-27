<?php

namespace Hcode;

use Rain\Tpl;

class Page {

  private $tpl;
  private $options = [];
  private $defaults =[
    "data"=>[]
  ];

  //magic method construct
  public function __construct($opts = array()){

    //Merge Arrays
    //array_merge () = Attention the order, last always override the first
    $this->options = array_merge($this->defaults, $opts);
    $config = array(
      "tpl_dir"   => $_SERVER["DOCUMENT_ROOT"] . "/views/",
      "cache_dir" => $_SERVER["DOCUMENT_ROOT"] . "/views-cache/",
      "debug"     => false //set to false to improve the speed
    );
    Tpl::configure($config);

    //For another classes have access to the method
    $this->tpl = new Tpl;

    $this->setData($this->options["data"]);

    //Add the header in the HTML page
    $this->tpl->draw("header");
    }

  //Method to insert data in another methods
   private function setData($data = array()){
     foreach ($data as $key => $value) {
       $this->tpl->assign($key, $value);
     }
   }

   //Deal with content templates
   public function setTpl($name, $data = array(), $returnHTML = false){
    $this->setData($data);
    $this->tpl->draw($name, $returnHTML);
   }

  //magic method destruct
   public function __destruct(){
    //Add the footer in the HTML page
    $this->tpl->draw("footer");
  }

}

 ?>
