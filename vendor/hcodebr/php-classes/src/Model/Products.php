<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Products extends Model{

  //List all products
  public static function listAll(){
    $sql = new Sql();
    return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
  }

  //Save products
  public function save(){
    $sql = new Sql();
    //Call a precedure
    $results =  $sql->select("
    CALL sp_products_save(
      :idproduct, :desproduct,
      :vlprice, :vlwidth,
      :vlheight, :vllength,
      :vlweight, :desurl
      )", array(
        ":idproduct"=>$this->getidproduct(),
        ":desproduct"=>$this->getdesproduct(),
        ":vlprice"=>$this->getvlprice(),
        ":vlwidth"=>$this->getvlwidth(),
        ":vlheight"=>$this->gevltheight(),
        ":vllength"=>$this->getvllength(),
        ":vlweight"=>$this->getvlweight(),
        ":desurl"=>$this->getdesurl()
    ));
    $this->setData($results[0]);
  }


  public function get($idproduct){
    $sql = new Sql();
    $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", [
      ":idproduct"=>$idproduct
    ]);
    $this->setData($results[0]);
  }

  public function delete(){
    $sql = new Sql();
    $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", [
      ":idproduct"=>$this->getidproduct()
    ]);
  }

}

 ?>
