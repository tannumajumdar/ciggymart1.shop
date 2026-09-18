<?php 
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
$db = new DBConn();


///*******************************************************
/// check Account exist or not ///////////////////////////
///*******************************************************
if($_POST['type']=="accountNoExis")
{
	
	$sql=$db->ExecuteQuery("SELECT EXISTS( SELECT 1 FROM tbl_banks WHERE Account_No='".$_POST['account_no']."' AND Branch_Id=".$_SESSION['buser']." ) AS 'Find'");
	
	echo $sql[1]['Find'];	  
}

///*******************************************************
/// check Account exist or not ///////////////////////////
///*******************************************************
if($_POST['type']=="accountNoEditExist")
{
	
	$sql=$db->ExecuteQuery("SELECT EXISTS( SELECT 1 FROM tbl_banks WHERE Account_No='".$_POST['account_no']."' AND Bank_Id<>".$_POST['bank_id']." AND Branch_Id=".$_SESSION['buser']." ) AS 'Find'");
	
	echo $sql[1]['Find'];	  
}

///*******************************************************
/// for Account invalid ///////////////////////////////////
///*******************************************************
if($_POST['type']=="accountNoExis")
{
	
	$sql=$db->ExecuteQuery("SELECT EXISTS( SELECT 1 FROM tbl_banks WHERE Account_No='".$_POST['account_no']."' AND Branch_Id=".$_SESSION['buser']." ) AS 'Find'");
	
	echo $sql[1]['Find'];	  
}

///*******************************************************
/// To Insert New Bank //////////////////////////////////
///*******************************************************
if($_POST['type']=="addBank")
{
	$con= mysql_connect(SERVER,DBUSER,DBPASSWORD);
	mysql_query('SET AUTOCOMMIT=0',$con);
	mysql_query('START TRANSACTION',$con);
	
	try
	{	
	
		$tablename = "tbl_banks";
		$tblfield=array('Bank_Name','Account_Name','Account_No','Branch_Address','IFSC_Code','Branch_Id');
		$tblvalues=array($_POST['bank_name'], $_POST['account_name'], $_POST['account_no'], $_POST['branch_address'], $_POST['IFSC_code'], $_SESSION['buser']);
		$res=$db->valInsert($tablename,$tblfield,$tblvalues);
		
		if(!$res)
		{
			throw new Exception('0');
		}
		
		mysql_query("COMMIT",$con);
		echo 1;
	}
	catch(Exception $e)
	{
		echo  $e->getMessage();
		mysql_query('ROLLBACK',$con);
		mysql_query('SET AUTOCOMMIT=1',$con);
	}
}


///*******************************************************
/// Edit hub
///*******************************************************
if($_POST['type']=="editbank")
{
	$tblname="tbl_banks";
	$tblfield=array('Bank_Name','Account_Name','Account_No','Branch_Address','IFSC_Code');
	$tblvalues=array($_POST['bank_name'], $_POST['account_name'], $_POST['account_no'], $_POST['branch_address'], $_POST['IFSC_code']);
	$condition="Bank_Id=".$_POST['bank_id'];
	$res=$db->updateValue($tblname,$tblfield,$tblvalues,$condition);
	
	if (empty($res))
	{
		echo 1;
	}
	else
	{
		echo 0;
	}
}

///*******************************************************
/// Delete row from hub_master table
///*******************************************************
if($_POST['type']=="delete")
{
	 $tblname="tbl_banks";
	 $condition="Bank_Id=".$_POST['Bank_Id'];
	 $res=$db->deleteRecords($tblname,$condition);
}


?>