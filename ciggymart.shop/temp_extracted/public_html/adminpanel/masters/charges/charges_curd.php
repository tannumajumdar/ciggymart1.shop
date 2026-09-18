<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');

$db = new DBConn();

if (isset($_POST['type']) && $_POST['type'] == "saveClientCharges") {
    $clientId = intval($_POST['client_id']);
    $pickup = floatval($_POST['pickup_charge']);
    $docket = floatval($_POST['docket_charge']);
    $door = floatval($_POST['door_charge']);

    $res = $db->query("UPDATE tbl_clients SET Pickup_Charge=$pickup, Docket_Charge=$docket, Door_Delivery_Charge=$door WHERE Client_Id=$clientId");
    echo $res ? 1 : 0;
    exit();
}
?>
