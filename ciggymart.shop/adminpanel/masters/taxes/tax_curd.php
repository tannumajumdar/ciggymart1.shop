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

	 $sql="SELECT tax_type FROM tbl_applicable_taxes WHERE tax_type='".$_POST['tax_type']."'";
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
/// Validate that the data already exist or not for EDIT
///*******************************************************
if($_POST['type']=="taxTypeEditValid")
{

	 $sql="SELECT tax_type FROM tbl_applicable_taxes WHERE Tax_id!=".$_POST['tax_id']." AND tax_type='".$_POST['tax_type']."'";
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
if($_POST['type']=="addTax")
{	
	$con= mysql_connect(SERVER,DBUSER,DBPASSWORD);
	mysql_query('SET AUTOCOMMIT=0',$con);
	mysql_query('START TRANSACTION',$con);
	
	try
	{		
		// insert data in zone table
		$res=mysql_query("INSERT INTO tbl_applicable_taxes (tax_type, tax_percent, taxpercent_ondate) VALUES ('".$_POST['tax_type']."','".$_POST['tax_percent']."', NOW())");

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
	$tblname="tbl_applicable_taxes";
	$tblfield=array('tax_type', 'tax_percent', 'taxpercent_ondate');
	$tblvalues=array($_POST['tax_type'],$_POST['tax_percent'], date('Y-m-d'));
	$condition="Tax_id=".$_POST['tax_id'];
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
/// Delete row from tbl_applicable_taxes table 
///*******************************************************
if($_POST['type']=="delete")
{
	 $tblname="tbl_applicable_taxes";
	 $condition="Tax_id=".$_POST['tax_id'];
	 $res=$db->deleteRecords($tblname,$condition);
}

?>