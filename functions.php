<?php

function formatPrice(float $vlprice){
  //first separator ","
  //second separator "."
  return number_format($vlprice, 2, ",", ".");
}


 ?>
