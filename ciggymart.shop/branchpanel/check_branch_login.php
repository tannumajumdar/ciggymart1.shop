<?php 
include('../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
$db = new DBConn();

$table = "tbl_branchs";
$usrfield = "Email";
$usrpassfield = "Password";

$result = $db->checkLogin($table,$usrfield,$_POST['email'],$usrpassfield,$_POST['password']);

if(!empty($result)){
	
	$_SESSION['buser']=$result[1]['Branch_Id'];
	
	echo "true";
}
else{
	echo "false";
}

?>