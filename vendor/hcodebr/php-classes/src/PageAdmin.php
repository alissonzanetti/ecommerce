<?php

namespace Hcode;

class PageAdmin extends Page {

  public function __construct($opts = array(), $tpl_dir = "/views/admin/"){

    //Using heritage, reuse the construct defined on Page class
    parent::__construct($opts, $tpl_dir);
  }

}

 ?>
