<?php 
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
$db = new DBConn();


///*******************************************************
/// Validate that the data already exist or not
///*******************************************************
if($_POST['type']=="validate1")
{

	$sql="SELECT State_Code FROM tbl_states WHERE State_Code='".$_POST['state_code']."'";
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

	$sql="SELECT State_Name FROM tbl_states WHERE State_Name='".$_POST['state_name']."'";
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
if($_POST['type']=="EditStateCodeValid")
{

	$sql="SELECT State_Code FROM tbl_states WHERE State_Id!=".$_POST['state_id']." AND State_Code='".$_POST['state_code']."'";
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
if($_POST['type']=="EditStateNameValid")
{

	$sql="SELECT State_Name FROM tbl_states WHERE State_Id!=".$_POST['state_id']." AND State_Name='".$_POST['state_name']."'";
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
/// To Insert New State //////////////////////////////////
///*******************************************************
if($_POST['type']=="addState")
{
		$con= mysql_connect(SERVER,DBUSER,DBPASSWORD);
		mysql_query('SET AUTOCOMMIT=0',$con);
		mysql_query('START TRANSACTION',$con);
		
		try
		{	
			$tablename = "tbl_states";
			$tblfield=array('State_Code','State_Name','Zone_Id');
			$tblvalues=array($_POST['state_code'], $_POST['state_name'], $_POST['zone_id']);
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
/// Edit state
///*******************************************************
if($_POST['type']=="editstate")
{
	
	$tblname="tbl_states";
	$tblfield=array('State_Code','State_Name','Zone_Id');
	$tblvalues=array($_POST['state_code'], $_POST['state_name'], $_POST['zone_id']);
	$condition="State_Id=".$_POST['state_id'];
	$res=$db->updateValue($tblname,$tblfield,$tblvalues,$condition);
	
	if (empty($res))
	{
		echo "sudhir";
	}
	else
	{
		echo 1;
	}
}

///*******************************************************
/// Get Dest name Autocomplete////////////////////////////////////////
///*******************************************************

if($_POST['type']=="getZoneCode")
{
	 $sql_county="SELECT Zone_Code FROM tbl_zones WHERE Zone_Code like '".$_POST['keyword']."%'"; 
	 
	 $res=$db->ExecuteQuery($sql_county);
	 foreach ($res as $rs) {
		$Zone_Code = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs['Zone_Code']);
		
        echo '<li onclick="set_item(\''.str_replace("'", "\'", $rs['Zone_Code']).'\')">'.$Zone_Code.'</li>';
	 }
}

///*******************************************************
/// Get Zone Name
///*******************************************************
if($_POST['type']=="getZone")
{
	$sql="SELECT Zone_Name, Zone_Id FROM tbl_zones WHERE Zone_Code='".$_POST['zone_code']."'";
	$res=$db->ExecuteQuery($sql);
		
	if(empty($res))
    {
 		echo "<input type='text' class='form-control input-sm' id='zone_name' name='zone_name' placeholder='Zone Name' readonly='readonly' value='' />";
    }
	else{
		
		echo "<input type='text' class='form-control input-sm' id='zone_name' name='zone_name' placeholder='Zone Name' readonly='readonly' value='".$res[1]['Zone_Name']."' /> <input type='hidden' class='form-control input-sm' id='zone_id' name='zone_id' value='".$res[1]['Zone_Id']."' />";
		
	}	

}


///*******************************************************
/// Delete row from state table
///*******************************************************
if($_POST['type']=="delete")
{
	 $tblname="tbl_states";
	 $condition="State_Id=".$_POST['State_Id'];
	 $res=$db->deleteRecords($tblname,$condition);
}


?>