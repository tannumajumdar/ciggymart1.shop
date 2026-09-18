<?php 
include('../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');

$db = new DBConn();


///*******************************************************
/// To change Password for user/////////////////////////////////
///*******************************************************

if($_POST['type']=="changePassword")
{	
		 
			$res=mysql_query("update branch_master set Password='".$_POST['new_pwd']."' where Branch_Id=".$_SESSION['buser']."");			
      if(!$res)
	 {
		 echo 0;
		}
   else
   {
	   echo 1;
	  }
		
}



?>
