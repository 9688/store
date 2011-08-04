<?php
function randString(){
  $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
  $numChars = strlen($chars);
  $string = '';
  for ($i = 0; $i < 30; $i++) {
    $string .= substr($chars, rand(1, $numChars) - 1, 1);
  }
  return sha1($string);
}