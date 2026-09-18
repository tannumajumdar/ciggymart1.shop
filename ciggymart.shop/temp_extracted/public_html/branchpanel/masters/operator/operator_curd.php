<?php 
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
$db = new DBConn();


///*******************************************************
/// Validate that the data already exist or not
///*******************************************************
if($_POST['type']=="emailExist")
{

	$sql="SELECT Email FROM tbl_operators WHERE Email='".$_POST['email']."'";
	$res=$db->ExecuteQuery($sql);
		
	if(empty($res))
    {
 		echo 1;
    }
	else
	{
		echo 0;
	}

}

///*******************************************************
/// Validate that the data already exist or not
///*******************************************************
if($_POST['type']=="emailEditExist")
{

	$sql="SELECT Email FROM tbl_operators WHERE Operator_Id!=".$_POST['operator_id']." AND Email='".$_POST['email']."'";
	$res=$db->ExecuteQuery($sql);
		
	if(empty($res))
    {
 		echo 1;
    }
	else
	{
		echo 0;
	}

}

///*******************************************************
/// Update Status Block or Unblock ///////////////////////
///*******************************************************
if($_POST['type']=="updateStatus")
{
	$status=explode('-',$_POST['status']);
	if($status[0]=='Block')
	{
		$sql= mysql_query("UPDATE tbl_operators SET Is_Active=0 WHERE Operator_Id=".$status[1]);
		if (!empty($sql))
		{
			echo 1;
		}
	}
	else{
		$sql= mysql_query("UPDATE tbl_operators SET Is_Active=1 WHERE Operator_Id=".$status[1]);
		if (!empty($sql))
		{
			echo 2;
		}
	}
}

///*******************************************************
/// Get State Name
///*******************************************************
if($_POST['type']=="getStateName")
{
	$sql="SELECT State_Name, State_Id FROM tbl_states WHERE State_Code='".$_POST['state_code']."'";
	$res=$db->ExecuteQuery($sql);
		
	if(empty($res))
    {
 		echo "<input type='text' class='form-control input-sm' id='state_name' name='state_name' placeholder='State Name' readonly='readonly' value='' />";
    }
	else{
		
		echo "<input type='text' class='form-control input-sm' id='state_name' name='state_name' placeholder='State Name' readonly='readonly' value='".$res[1]['State_Name']."' /> <input type='hidden' class='form-control input-sm' id='state_id' name='state_id' value='".$res[1]['State_Id']."' />";
		
	}	
}


///*******************************************************
/// To Insert New Hub //////////////////////////////////
///*******************************************************
if($_POST['type']=="addOperator")
{
	$con= mysql_connect(SERVER,DBUSER,DBPASSWORD);
	mysql_query('SET AUTOCOMMIT=0',$con);
	mysql_query('START TRANSACTION',$con);
	
	try
	{	
	
		$tablename = "tbl_operators";
		$tblfield=array('Operator_name','Address','State_Id','Contact_No','Email','Password','Branch_Id','Is_Active');
		$tblvalues=array($_POST['operator_name'], $_POST['o_address'], $_POST['state_id'], $_POST['operator_contact'], $_POST['email'], $_POST['password'], $_SESSION['buser'],1);
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
if($_POST['type']=="editoperator")
{
	$tblname="tbl_operators";
	$tblfield=array('Operator_name','Address','State_Id','Contact_No','Email','Password');
	$tblvalues=array($_POST['operator_name'], $_POST['o_address'], $_POST['state_id'], $_POST['operator_contact'], $_POST['email'], $_POST['password']);
	$condition="Operator_Id=".$_POST['operator_id'];
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
	 $tblname="hub_master";
	 $condition="Hub_Id=".$_POST['Hub_Id'];
	 $res=$db->deleteRecords($tblname,$condition);
}


?>