<?php 
include('../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
$db = new DBConn();

$table = "tbl_admin_login";
$usrfield = "username";
$usrpassfield = "password";

$result = $db->checkLogin($table,$usrfield,$_POST['user'],$usrpassfield,$_POST['password']);

if(!empty($result)){
	
	$_SESSION['user']=$result[1]['Admin_Id'];
	
	echo "true";
}
else{
	echo "false";
}

?>