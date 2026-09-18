<?php 
include('../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/rate.php');
require_once(PATH_LIBRARIES.'/classes/CourierBillingEngine.php');

$db = new DBConn();
$rateClass = new rate();
$billing = new CourierBillingEngine($db);

$branchId = isset($_SESSION['buser']) ? intval($_SESSION['buser']) : 1;

///*******************************************************
/// for Client invalid
///*******************************************************
if(isset($_POST['type']) && $_POST['type']=="clientInvalid")
{
	$clientName = explode("-", $_POST['client_name']);
	$safeName = $db->escape(trim($clientName[0]));
	$sql=$db->ExecuteQuery("SELECT EXISTS( SELECT 1 FROM tbl_clients WHERE Client_Name='$safeName' AND Branch_Id=$branchId ) AS 'Find'");
	echo (!empty($sql) && isset($sql[1]['Find'])) ? $sql[1]['Find'] : 0;
	exit();
}

///*******************************************************
/// for Destination invalid
///*******************************************************
if(isset($_POST['type']) && $_POST['type']=="destInvalid")
{
	$safeDest = $db->escape(trim($_POST['Destination_Name']));
	$sql=$db->ExecuteQuery("SELECT EXISTS( SELECT 1 FROM tbl_destinations WHERE Destination_Name ='$safeDest' ) AS 'Find'");
	echo (!empty($sql) && isset($sql[1]['Find'])) ? $sql[1]['Find'] : 0;
	exit();
}

///*******************************************************
/// for Consignment No invalid / duplicate
///*******************************************************
if(isset($_POST['type']) && $_POST['type']=="invalidCons")
{
	$safeCons = $db->escape(trim($_POST['consignment_no']));
	$excludeId = isset($_POST['exclude_id']) ? intval($_POST['exclude_id']) : 0;
	$sqlStr = "SELECT EXISTS( SELECT 1 FROM tbl_consignments WHERE Consignment_No='$safeCons' AND Branch_Id=$branchId " . ($excludeId > 0 ? "AND Consignment_Id != $excludeId" : "") . " ) AS 'Find'";
	$sql = $db->ExecuteQuery($sqlStr);
	echo (!empty($sql) && isset($sql[1]['Find'])) ? $sql[1]['Find'] : 0;
	exit();
}

///*******************************************************
/// Get Full Client Details for Auto-Fill
///*******************************************************
if(isset($_POST['type']) && $_POST['type']=="getClientDetails")
{
	$clientId = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
	$clientQuery = isset($_POST['client_name']) ? trim($_POST['client_name']) : '';

	if ($clientId <= 0 && !empty($clientQuery)) {
		$parts = explode('-', $clientQuery);
		$safeName = $db->escape(trim($parts[0]));
		$safeCode = isset($parts[1]) ? $db->escape(trim($parts[1])) : '';
		$res = $db->ExecuteQuery("SELECT * FROM tbl_clients WHERE (Client_Name='$safeName' " . (!empty($safeCode) ? "OR Client_Code='$safeCode'" : "") . ") AND Branch_Id=$branchId LIMIT 1");
	} else {
		$res = $db->ExecuteQuery("SELECT * FROM tbl_clients WHERE Client_Id=$clientId AND Branch_Id=$branchId LIMIT 1");
	}

	if (!empty($res) && isset($res[1])) {
		$c = $res[1];
		$c['Pickup_Charge'] = $billing->getPickupCharge($c['Client_Id']);
		$c['Docket_Charge'] = $billing->getDocketCharge($c['Client_Id']);
		$c['Door_Delivery_Charge'] = $billing->getDoorDeliveryCharge(0, $c['Client_Id']);
		echo json_encode(['status' => 'success', 'data' => $c]);
	} else {
		echo json_encode(['status' => 'error', 'message' => 'Client not found']);
	}
	exit();
}

///*******************************************************
/// Get Full Destination & ODA Details
///*******************************************************
if(isset($_POST['type']) && $_POST['type']=="getDestinationDetails")
{
	$destId = isset($_POST['dest_id']) ? intval($_POST['dest_id']) : 0;
	$destName = isset($_POST['dest_name']) ? trim($_POST['dest_name']) : '';

	if ($destId <= 0 && !empty($destName)) {
		$safeDest = $db->escape(trim($destName));
		$res = $db->ExecuteQuery("SELECT D.*, S.State_Name, S.Zone_Id FROM tbl_destinations D INNER JOIN tbl_states S ON D.State_Id = S.State_Id WHERE D.Destination_Name='$safeDest' LIMIT 1");
	} else {
		$res = $db->ExecuteQuery("SELECT D.*, S.State_Name, S.Zone_Id FROM tbl_destinations D INNER JOIN tbl_states S ON D.State_Id = S.State_Id WHERE D.Destination_Id=$destId LIMIT 1");
	}

	if (!empty($res) && isset($res[1])) {
		$d = $res[1];
		$oda = $billing->checkODA($d['Destination_Id'], $d['Destination_Name'], isset($d['Pincode']) ? $d['Pincode'] : '');
		$d['is_oda'] = $oda['is_oda'];
		$d['oda_charge'] = $oda['oda_charge'];
		$d['door_delivery_charge'] = $billing->getDoorDeliveryCharge($d['Destination_Id']);
		echo json_encode(['status' => 'success', 'data' => $d]);
	} else {
		echo json_encode(['status' => 'error', 'message' => 'Destination not found']);
	}
	exit();
}

///*******************************************************
/// Compute Complete Booking Calculations (Live AJAX)
///*******************************************************
if(isset($_POST['type']) && $_POST['type']=="computeCharges")
{
	$_POST['branch_id'] = $branchId;
	$calc = $billing->computeBookingCharges($_POST);
	echo json_encode(['status' => 'success', 'calc' => $calc]);
	exit();
}

///*******************************************************
/// Legacy Support for Zone Exist check
///*******************************************************
if(isset($_POST['type']) && $_POST['type']=="zoneExist")
{
	$destId = intval($_POST['dest_id']);
	$clientId = intval($_POST['client_id']);
	$sendBy = intval($_POST['send_by']);
	$zoneId = isset($_POST['zone_id']) ? intval($_POST['zone_id']) : 0;

	$getBranchDest = $db->ExecuteQuery("SELECT Destination_Id FROM tbl_branchs WHERE Branch_Id=$branchId");
	$branchDestId = !empty($getBranchDest) ? intval($getBranchDest[1]['Destination_Id']) : 0;
	$getBranchState = $db->ExecuteQuery("SELECT State_Id FROM tbl_destinations WHERE Destination_Id=$branchDestId");
	$branchStateId = !empty($getBranchState) ? intval($getBranchState[1]['State_Id']) : 0;
	$getInputState = $db->ExecuteQuery("SELECT State_Id FROM tbl_destinations WHERE Destination_Id=$destId");
	$inputStateId = !empty($getInputState) ? intval($getInputState[1]['State_Id']) : 0;

	if($branchStateId == $inputStateId && $branchStateId > 0) {
		if($branchDestId == $destId) {
			$sql = $db->ExecuteQuery("SELECT EXISTS(SELECT 1 FROM tbl_rates WHERE Zone_Id=1 AND Client_Id=$clientId AND Send_By=$sendBy) AS Find");
		} else {
			$sql = $db->ExecuteQuery("SELECT EXISTS(SELECT 1 FROM tbl_rates WHERE Zone_Id=2 AND Client_Id=$clientId AND Send_By=$sendBy) AS Find");
		}
	} else {
		$sql = $db->ExecuteQuery("SELECT EXISTS(SELECT 1 FROM tbl_rates WHERE Zone_Id=$zoneId AND Client_Id=$clientId AND Send_By=$sendBy) AS Find");
	}

	echo (!empty($sql) && isset($sql[1]['Find'])) ? $sql[1]['Find'] : 1;
	exit();
}

///*******************************************************
/// Legacy Support for getSubtotal
///*******************************************************
if(isset($_POST['type']) && $_POST['type']=="getSubtotal")
{
	$rateClass->getSubtotal($branchId, $_POST['client_id'], $_POST['dest_id'], $_POST['send_by'], $_POST['weight']);
	exit();
}

///*******************************************************
/// To Insert New Consignment
///*******************************************************
if(isset($_POST['type']) && $_POST['type']=="addConsignment")
{
	$_POST['branch_id'] = $branchId;
	$calc = $billing->computeBookingCharges($_POST);

	$rawdate = isset($_POST['date']) ? $_POST['date'] : date('d-m-Y');
	$date = date('Y-m-d', strtotime($rawdate));

	$isInsured = (!empty($_POST['is_insured']) && $_POST['is_insured'] == 1) ? 1 : 0;
	$insuredValue = $isInsured ? floatval($_POST['insured_value']) : 0.0;
	$insuranceCharge = $calc['insurance_charge'];
	$otherCharges = floatval($calc['other_charges']);
	$insuranceOtherCharges = $insuranceCharge + $otherCharges;

	$fields = [
		'Consignment_No', 'Destination_Id', 'Mode', 'No_Of_Pieces', 'Send_By', 
		'Total_Weight_In_KG', 'Volumetric_Weight', 'Chargeable_Weight',
		'Length_CM', 'Width_CM', 'Height_CM', 'HSN_Code', 'Commodity_Type', 'Package_Type',
		'Insured_Value', 'Subtotal', 'Discount_Percent', 'Discount_Rs', 'Total_Amount',
		'Pickup_Charge', 'Door_Delivery_Charge', 'Docket_Charge', 'ODA_Charge', 'Insurance_Charge',
		'Other_Charges', 'Insurance_Other_Charges', 'Client_id', 'Branch_Id', 'Date_Of_Submit',
		'Consignee_Name', 'Consignee_Address', 'Consignee_City', 'Consignee_Mobile', 'Consignee_Pincode',
		'Is_Insured', 'Insurance_Provider', 'Insurance_Policy_No'
	];

	$values = [
		$_POST['consignment_no'],
		intval($_POST['dest_id']),
		intval(isset($_POST['mode']) ? $_POST['mode'] : 1),
		intval(isset($_POST['pieces']) ? $_POST['pieces'] : 1),
		intval(isset($_POST['send_by']) ? $_POST['send_by'] : 1),
		$calc['weight_details']['actual_weight'],
		$calc['weight_details']['volumetric_weight'],
		$calc['weight_details']['chargeable_weight'],
		$calc['weight_details']['length'],
		$calc['weight_details']['width'],
		$calc['weight_details']['height'],
		isset($_POST['hsn_code']) ? $_POST['hsn_code'] : '996812',
		isset($_POST['commodity_type']) ? $_POST['commodity_type'] : 'General Goods',
		isset($_POST['package_type']) ? $_POST['package_type'] : 'Box',
		$insuredValue,
		$calc['base_freight'],
		$calc['discount_percent'],
		$calc['discount_rs'],
		$calc['total_amount'],
		$calc['pickup_charge'],
		$calc['door_delivery_charge'],
		$calc['docket_charge'],
		$calc['oda_charge'],
		$insuranceCharge,
		$otherCharges,
		$insuranceOtherCharges,
		intval($_POST['client_id']),
		$branchId,
		$date,
		isset($_POST['consignee_name']) ? $_POST['consignee_name'] : '',
		isset($_POST['consignee_address']) ? $_POST['consignee_address'] : '',
		isset($_POST['consignee_city']) ? $_POST['consignee_city'] : '',
		isset($_POST['consignee_mobile']) ? $_POST['consignee_mobile'] : '',
		isset($_POST['consignee_pincode']) ? $_POST['consignee_pincode'] : '',
		$isInsured,
		isset($_POST['insurance_provider']) ? $_POST['insurance_provider'] : '',
		isset($_POST['insurance_policy_no']) ? $_POST['insurance_policy_no'] : ''
	];

	$res = $db->valInsert('tbl_consignments', $fields, $values);
	echo $res ? 1 : 0;
	exit();
}

///*******************************************************
/// To Edit Consignment
///*******************************************************
if(isset($_POST['type']) && $_POST['type']=="editConsignment")
{
	$_POST['branch_id'] = $branchId;
	$calc = $billing->computeBookingCharges($_POST);

	$rawdate = isset($_POST['date']) ? $_POST['date'] : date('d-m-Y');
	$date = date('Y-m-d', strtotime($rawdate));

	$isInsured = (!empty($_POST['is_insured']) && $_POST['is_insured'] == 1) ? 1 : 0;
	$insuredValue = $isInsured ? floatval($_POST['insured_value']) : 0.0;
	$insuranceCharge = $calc['insurance_charge'];
	$otherCharges = floatval($calc['other_charges']);
	$insuranceOtherCharges = $insuranceCharge + $otherCharges;

	$fields = [
		'Consignment_No', 'Destination_Id', 'Mode', 'No_Of_Pieces', 'Send_By', 
		'Total_Weight_In_KG', 'Volumetric_Weight', 'Chargeable_Weight',
		'Length_CM', 'Width_CM', 'Height_CM', 'HSN_Code', 'Commodity_Type', 'Package_Type',
		'Insured_Value', 'Subtotal', 'Discount_Percent', 'Discount_Rs', 'Total_Amount',
		'Pickup_Charge', 'Door_Delivery_Charge', 'Docket_Charge', 'ODA_Charge', 'Insurance_Charge',
		'Other_Charges', 'Insurance_Other_Charges', 'Client_id', 'Date_Of_Submit',
		'Consignee_Name', 'Consignee_Address', 'Consignee_City', 'Consignee_Mobile', 'Consignee_Pincode',
		'Is_Insured', 'Insurance_Provider', 'Insurance_Policy_No'
	];

	$values = [
		$_POST['consignment_no'],
		intval($_POST['dest_id']),
		intval(isset($_POST['mode']) ? $_POST['mode'] : 1),
		intval(isset($_POST['pieces']) ? $_POST['pieces'] : 1),
		intval(isset($_POST['send_by']) ? $_POST['send_by'] : 1),
		$calc['weight_details']['actual_weight'],
		$calc['weight_details']['volumetric_weight'],
		$calc['weight_details']['chargeable_weight'],
		$calc['weight_details']['length'],
		$calc['weight_details']['width'],
		$calc['weight_details']['height'],
		isset($_POST['hsn_code']) ? $_POST['hsn_code'] : '996812',
		isset($_POST['commodity_type']) ? $_POST['commodity_type'] : 'General Goods',
		isset($_POST['package_type']) ? $_POST['package_type'] : 'Box',
		$insuredValue,
		$calc['base_freight'],
		$calc['discount_percent'],
		$calc['discount_rs'],
		$calc['total_amount'],
		$calc['pickup_charge'],
		$calc['door_delivery_charge'],
		$calc['docket_charge'],
		$calc['oda_charge'],
		$insuranceCharge,
		$otherCharges,
		$insuranceOtherCharges,
		intval($_POST['client_id']),
		$date,
		isset($_POST['consignee_name']) ? $_POST['consignee_name'] : '',
		isset($_POST['consignee_address']) ? $_POST['consignee_address'] : '',
		isset($_POST['consignee_city']) ? $_POST['consignee_city'] : '',
		isset($_POST['consignee_mobile']) ? $_POST['consignee_mobile'] : '',
		isset($_POST['consignee_pincode']) ? $_POST['consignee_pincode'] : '',
		$isInsured,
		isset($_POST['insurance_provider']) ? $_POST['insurance_provider'] : '',
		isset($_POST['insurance_policy_no']) ? $_POST['insurance_policy_no'] : ''
	];

	$condition = "Consignment_Id=" . intval($_POST['consignment_id']);
	$res = $db->updateValue('tbl_consignments', $fields, $values, $condition);
	echo $res ? 1 : 0;
	exit();
}

///*******************************************************
/// Delete Consignment
///*******************************************************
if(isset($_POST['type']) && $_POST['type']=="delete")
{
	$id = intval($_POST['id']);
	$res = $db->deleteRecords('tbl_consignments', "Consignment_Id=$id AND Branch_Id=$branchId");
	echo $res ? 1 : 0;
	exit();
}
?>