<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model{

  const SESSION = "User";

  public static function login($login, $password){

    $sql = new Sql();
    $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
      ":LOGIN"=>$login
    ));

    //Test if there is a login
    if(count($results) === 0) {
      //With "\Exception" it finds the principal scope to throw the alerts
      throw new \Exception("Usuário inexistente ou senha inválida");
    }

    $data = $results[0];

    //Verify Password
    if (password_verify($password, $data["despassword"]) === true) {
      $user = new User();
      //setData = Dinamic method to set an get values
      //Getters and Setters with magic methods
      $user->setData($data);
      //Creating a session with values from Model class
      $_SESSION[User::SESSION] = $user->getValues();
      /*
      var_dump($user);
      exit;
      */
      return $user;
    } else {
      throw new \Exception("Usuário inexistente ou senha inválida");
    }
  }

  public static function verifyLogin($inadmin = true) {
    //If not logged yet
    //OR it is false
    //OR iduser is empty by casting (int type)
    //OR iduser is not an admin ($inadmin)
    if(
      !isset($_SESSION[User::SESSION])
      ||
      !$_SESSION[User::SESSION]
      ||
      !(int)$_SESSION[User::SESSION]["iduser"] > 0
      ||
      (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
    ){
      header("Location: /admin/login");
      //Exit to avoid redirection
      exit;
    }
  }

  public static function logout(){
    $_SESSION[User::SESSION] = NULL;
  }

}


 ?>
