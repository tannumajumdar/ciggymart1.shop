<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');

$db = new DBConn();

if (isset($_POST['type']) && $_POST['type'] == "addODA") {
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $pincode = trim($_POST['pincode']);
    $zone_id = intval($_POST['zone_id']);
    $oda_charge = floatval($_POST['oda_charge']);

    $res = $db->valInsert(
        'tbl_oda_master',
        ['City', 'State', 'Pincode', 'Zone_Id', 'Is_ODA', 'ODA_Charge', 'Is_Active'],
        [$city, $state, $pincode, $zone_id, 1, $oda_charge, 1]
    );

    echo $res ? 1 : 0;
    exit();
}

if (isset($_POST['type']) && $_POST['type'] == "deleteODA") {
    $id = intval($_POST['id']);
    $res = $db->deleteRecords('tbl_oda_master', "ODA_Id=$id");
    echo $res ? 1 : 0;
    exit();
}

if (isset($_POST['type']) && $_POST['type'] == "checkODA") {
    require_once(PATH_LIBRARIES.'/classes/CourierBillingEngine.php');
    $billing = new CourierBillingEngine($db);
    $destId = isset($_POST['dest_id']) ? intval($_POST['dest_id']) : 0;
    $destName = isset($_POST['dest_name']) ? trim($_POST['dest_name']) : '';
    $pincode = isset($_POST['pincode']) ? trim($_POST['pincode']) : '';

    $oda = $billing->checkODA($destId, $destName, $pincode);
    echo json_encode($oda);
    exit();
}
?>
