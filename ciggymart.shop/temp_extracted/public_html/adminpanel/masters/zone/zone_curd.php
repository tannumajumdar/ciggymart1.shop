<?php 
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
require_once(PATH_LIBRARIES.'/classes/resize.php');
$db = new DBConn();


///*******************************************************
/// Validate that the data already exist or not
///*******************************************************
if($_POST['type']=="validate1")
{

	 $sql="SELECT Zone_Code FROM tbl_zones WHERE Zone_Code='".$_POST['zone_code']."'";
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
if($_POST['type']=="validate2")
{

	 $sql="SELECT Zone_Name FROM tbl_zones WHERE Zone_Name='".$_POST['zone_name']."'";
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
/// Validate that the data already exist or not in EDIT
///*******************************************************
if($_POST['type']=="zoneCodeEditValid")
{

	 $sql="SELECT Zone_Code FROM tbl_zones WHERE Zone_Code='".$_POST['zone_code']."' AND  Zone_Id!=".$_POST['zone_id'];
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
/// Validate that the data already exist or not in EDIT
///*******************************************************
if($_POST['type']=="zoneNameEditValid")
{

	 $sql="SELECT Zone_Name FROM tbl_zones WHERE Zone_Name='".$_POST['zone_name']."' AND  Zone_Id!=".$_POST['zone_id'];
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
/// Insert New Zone //////////////////////////////////////
///*******************************************************
if($_POST['type']=="addZone")
{	
	$con= mysql_connect(SERVER,DBUSER,DBPASSWORD);
	mysql_query('SET AUTOCOMMIT=0',$con);
	mysql_query('START TRANSACTION',$con);
	
	try
	{		
		// insert data in zone table
		$res=mysql_query("INSERT INTO tbl_zones (Zone_Code, Zone_Name) VALUES ('".$_POST['zone_code']."','".$_POST['zone_name']."')");

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
/// Edit Zone
///*******************************************************
if($_POST['type']=="edit")
{
	$tblname="tbl_zones";
	$tblfield=array('Zone_Code', 'Zone_Name');
	$tblvalues=array($_POST['zone_code'],$_POST['zone_name']);
	$condition="Zone_Id=".$_POST['zone_id'];
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
/// Delete row from ZONE table 
///*******************************************************
if($_POST['type']=="delete")
{
	 $tblname="tbl_zones";
	 $condition="Zone_Id=".$_POST['zone_id'];
	 $res=$db->deleteRecords($tblname,$condition);
}

?>