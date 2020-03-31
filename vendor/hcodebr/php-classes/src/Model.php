<?php

namespace Hcode;

class Model {
  //Container for values
  private $values = [];
  //We need to know which class called the method (Getter ou Setter)
  public function __call($name, $args){
    //Substr descart first three characters (Set or Get)
    $method = substr($name, 0, 3);
    //From 3 to the maximum length
    $fieldName = substr($name, 3, strlen($name));
    /*
    var_dump($method, $fieldName);
    //Exit. If not, will Redirect
    exit;
    */
    switch ($method) {
      case 'get':
          //idcategory is only defined at the database
          //So, getidcategory will not return. To get around, use NULL
          return (isset($this->values[$fieldName])) ? $this->values[$fieldName] : NULL;
        break;

      case 'set':
          $this->values[$fieldName] = $args[0];
        break;
    }
  }

  public function setData($data = array()) {
    foreach ($data as $key => $value) {
      //Dinamic setting of setting
      $this->{"set". $key}($value);
    }
  }

  public function getValues() {
    //Return $values
    return $this->values;
  }

}
 ?>
