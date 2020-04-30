<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model{

  /*
  const SESSION = "User";
  //Cryptography key with 16 characters
  const SECRET = "HcodePhp7_Secret";
  */
  const SESSION = "User";
	const SECRET = "HcodePhp7_Secret";
	const SECRET_IV = "HcodePhp7_Secret_IV";
	const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";
	const SUCCESS = "UserSucesss";

  public static function getFromSession()	{
		$user = new User();
		if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0) {
			$user->setData($_SESSION[User::SESSION]);
		}
		return $user;
	}

  public static function checkLogin($inadmin = true){
    //If not logged yet
    //OR it is false
    //OR iduser is empty by casting (int type)
		if (
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
		) {
			//Not logged
			return false;
		} else {
      //Is it a admin route?
			if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) {
				return true;
			} else if ($inadmin === false) {
				return true;
			} else {
				return false;
			}
		}
	}

  public static function login($login, $password)	{
		$sql = new Sql();
		$results = $sql->select("
      SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson
      WHERE a.deslogin = :LOGIN
    ", array(
			":LOGIN"=>$login
		));
    //Test if there is a login
		if (count($results) === 0){
      //With "\Exception" it finds the principal scope to throw the alerts
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}
		$data = $results[0];
    //Verify Password
		if (password_verify($password, $data["despassword"]) === true){
			$user = new User();
			$data['desperson'] = utf8_encode($data['desperson']);
      //setData = Dinamic method to set an get values
      //Getters and Setters with magic methods
			$user->setData($data);
      //Creating a session with values from Model class
			$_SESSION[User::SESSION] = $user->getValues();
			return $user;
		} else {
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}
	}

  public static function verifyLogin($inadmin = true){
    //If not logged yet
    //OR it is false
    //OR iduser is empty by casting (int type)
    //OR iduser is not an admin ($inadmin)
		if (!User::checkLogin($inadmin)) {
			if ($inadmin) {
				header("Location: /admin/login");
			} else {
				header("Location: /login");
			}
			exit;
		}
	}

  //Logout
  public static function logout(){
    $_SESSION[User::SESSION] = NULL;
  }

  //List all users
  public static function listAll(){
    $sql = new Sql();
    return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
  }

  //Save users in DB
  public function save(){
    $sql = new Sql();
    /*
    pdesperson VARCHAR(64),
    pdeslogin VARCHAR(64),
    pdespassword VARCHAR(256),
    pdesemail VARCHAR(128),
    pnrphone BIGINT,
    pinadmin TINYINT
    */
    //Call a precedure
    $results =  $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",
    array(
      ":desperson"=>utf8_decode($this->getdesperson()),
      ":deslogin"=>$this->getdeslogin(),
      ":despassword"=>User::getPasswordHash($this->getdespassword()),
      ":desemail"=>$this->getdesemail(),
      ":nrphone"=>$this->getnrphone(),
      ":inadmin"=>$this->getinadmin()
    ));
    $this->setData($results[0]);
  }

  public function get($iduser) {
    $sql = new Sql();
    $results = $sql->select("
     SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson)
     WHERE a.iduser = :iduser
    ", array(
      ":iduser"=>$iduser
    ));
    $data = $results[0];
    $data['desperson'] = utf8_encode($data['desperson']);
    $this->setData($data);
  }

  public function update() {
    $sql = new Sql();
    //Call a precedure
    $results =  $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",
    array(
      ":iduser"=>$this->getiduser(),
      ":desperson"=>utf8_decode($this->getdesperson()),
      ":deslogin"=>$this->getdeslogin(),
      ":despassword"=>User::getPasswordHash($this->getdespassword()),
      ":desemail"=>$this->getdesemail(),
      ":nrphone"=>$this->getnrphone(),
      ":inadmin"=>$this->getinadmin()
    ));
    $this->setData($results[0]);
  }

  public function delete ($value=''){
    $sql = new Sql();
    $sql->query("CALL sp_users_delete(:iduser)", array(
      ":iduser"=>$this->getiduser()
    ));
  }

  public static function getForgot($email) {
    $sql = new Sql();
    $results = $sql->select("
      SELECT *
      FROM tb_persons a
      INNER JOIN tb_users b USING(idperson)
      WHERE a.desemail = :email;
    ", array(
        ":email"=>$email
    ));

    if(count($results) === 0) {
      throw new \Exception("Não foi possível recuperar a senha.");
    } else {
      $data = $results[0];
      $resultsRecovery = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
        ":iduser"=>$data["iduser"],
        ":desip"=>$_SERVER["REMOTE_ADDR"]
      ));

      if(count($resultsRecovery) === 0) {
        throw new \Exception("Não foi possível recuperar a senha 1");
      } else {
        $dataRecovery = $resultsRecovery[0];
        //mcrypt_encrypt(cipher, key, data, mode)
        //$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery["idrecovery"], MCRYPT_MODE_ECB));
        $code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));
				$code = base64_encode($code);
        $link="http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";
        $mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir senha da Hcode Store", "forgot", array(
          "name"=>$data["desperson"],
          "link"=>$link
        ));
        $mailer->send();
        return;
      }
    }
  }

  public static function validForgotDecrypt($code){
    $code = base64_decode($code);
		$idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));
    //$idrecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRET, base64_decode($code), MCRYPT_MODE_ECB);
    $sql = new Sql();
    $results = $sql->select("
      SELECT *
      FROM tb_userspasswordsrecoveries a
      INNER JOIN tb_users b USING(iduser)
      INNER JOIN tb_persons c USING(idperson)
      WHERE
        a.idrecovery = :idrecovery
        AND
        a.dtrecovery IS NULL
        AND
        DATE_ADD(a.dtregister, INTERVAL 2 HOUR) >= NOW();
    ", array(
        ":idrecovery"=>$idrecovery
    ));

    if(count($results) === 0){
      throw new \Exception("Não foi possível recuperar a senha 2");
    } else {
      return $results[0];
    }
  }

  public static function setForgotUsed($idrecovery){
    $sql = new Sql();
    $sql->query("
    UPDATE tb_userspasswordsrecoveries
    SET dtrecovery = NOW()
    WHERE idrecovery = :idrecovery;
    ", array(
        ":idrecovery"=>$idrecovery
    ));
  }

  public function setPassword($password){
  	$sql = new Sql();
  	$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
  		":password"=>$password,
  		":iduser"=>$this->getiduser()
  	));
  }

  public static function setError($msg){
    $_SESSION[User::ERROR] = $msg;
  }

  public static function getError(){
    $msg =  (isset($_SESSION[User::ERROR])) &&  ($_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : "";
    User::clearError();
    return $msg;
  }

  public static function clearError(){
    $_SESSION[User::ERROR] = NULL;
  }

  public static function setErrorRegister($msg){
    $_SESSION[User::ERROR_REGISTER] = $msg;
  }

  //When save password, encrypt it
  public static function getPasswordHash($password){
    return password_hash($password, PASSWORD_DEFAULT, [
      'cost'=>12
    ]);
  }

}

 ?>
