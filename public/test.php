<?php
namespace Users;


$myUtil = new Users\Tools\MyUtils();
echo 'my test page<br>';
$email = "l.yuhai@gmail.com";
echo 'email address ='. $email.'<br>';
//$flag = $myUtil->isValidateEmail($email);
echo 'is a validate email?'. $flag; 
?>