<?php 
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
$db = new DBConn();

///*******************************************************
/// Validate that the data already exist or not
///*******************************************************
if($_POST['type']=="validate1")
{
	$sql="SELECT Destination_Code FROM tbl_destinations WHERE Destination_Code='".$db->escape($_POST['dest_code'])."'";
	$res=$db->ExecuteQuery($sql);
	echo empty($res) ? 1 : 0;
}

if($_POST['type']=="validate2")
{
	$sql="SELECT Destination_Name FROM tbl_destinations WHERE Destination_Name='".$db->escape($_POST['dest_name'])."'";
	$res=$db->ExecuteQuery($sql);
	echo empty($res) ? 1 : 0;
}

if($_POST['type']=="destCodeEditValid")
{
	$sql="SELECT Destination_Code FROM tbl_destinations WHERE Destination_Id!=".intval($_POST['dest_id'])." AND Destination_Code='".$db->escape($_POST['dest_code'])."'";
	$res=$db->ExecuteQuery($sql);
	echo empty($res) ? 1 : 0;
}

if($_POST['type']=="destNameEditValid")
{
	$sql="SELECT Destination_Name FROM tbl_destinations WHERE Destination_Id!=".intval($_POST['dest_id'])." AND Destination_Name='".$db->escape($_POST['dest_name'])."'";
	$res=$db->ExecuteQuery($sql);
	echo empty($res) ? 1 : 0;
}

if($_POST['type']=="getStateName")
{
	$sql="SELECT State_Name, State_Id FROM tbl_states WHERE State_Code='".$db->escape($_POST['state_code'])."'";
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
/// To Insert New Destination
///*******************************************************
if($_POST['type']=="addDestination")
{
	$pincode = isset($_POST['pincode']) ? trim($_POST['pincode']) : '';
	$is_oda = isset($_POST['is_oda']) ? intval($_POST['is_oda']) : 0;
	$oda_charge = isset($_POST['oda_charge']) ? floatval($_POST['oda_charge']) : 0.0;
	$door_charge = isset($_POST['door_charge']) ? floatval($_POST['door_charge']) : 0.0;

	$tablename = "tbl_destinations";
	$tblfield = array('Destination_Code','Destination_Name','State_Id','Zone_Id','Pincode','Is_ODA','ODA_Charge','Door_Delivery_Charge');
	$tblvalues = array($_POST['dest_code'], $_POST['dest_name'], $_POST['state_id'], $_POST['zone_id'], $pincode, $is_oda, $oda_charge, $door_charge);
	$res = $db->valInsert($tablename, $tblfield, $tblvalues);
	
	echo $res ? 1 : 0;
}

///*******************************************************
/// Edit Destination
///*******************************************************
if($_POST['type']=="editdestination")
{
	$pincode = isset($_POST['pincode']) ? trim($_POST['pincode']) : '';
	$is_oda = isset($_POST['is_oda']) ? intval($_POST['is_oda']) : 0;
	$oda_charge = isset($_POST['oda_charge']) ? floatval($_POST['oda_charge']) : 0.0;
	$door_charge = isset($_POST['door_charge']) ? floatval($_POST['door_charge']) : 0.0;

	$tblname = "tbl_destinations";
	$tblfield = array('Destination_Code','Destination_Name','State_Id','Zone_Id','Pincode','Is_ODA','ODA_Charge','Door_Delivery_Charge');
	$tblvalues = array($_POST['dest_code'], $_POST['dest_name'], $_POST['state_id'], $_POST['zone_id'], $pincode, $is_oda, $oda_charge, $door_charge);
	$condition = "Destination_Id=".intval($_POST['id']);
	$res = $db->updateValue($tblname, $tblfield, $tblvalues, $condition);
	
	echo $res ? 1 : 0;
}

if($_POST['type']=="delete")
{
	 $tblname="tbl_destinations";
	 $condition="Destination_Id=".intval($_POST['Destination_Id']);
	 $res=$db->deleteRecords($tblname,$condition);
	 echo $res ? 1 : 0;
}
?>