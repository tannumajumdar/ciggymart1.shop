<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');

$db = new DBConn();

/*
 * Zone-wise pickup rates: one row per client + zone in tbl_pickup_charges.
 * A blank box removes that zone's rate so the client's flat charge applies.
 */
/*
 * Rate master charge setup: fuel, insurance, FOV and urgent rates for one
 * company. The row is created by the migration, so this only ever updates.
 */
if (isset($_POST['type']) && $_POST['type'] == "saveChargeSetup") {
    $branchId = intval($_POST['branch_id']);
    if ($branchId <= 0) {
        echo "0";
        exit();
    }

    $map = array(
        'Fuel_Percent'      => 'fuel_percent',
        'Insurance_Percent' => 'insurance_percent',
        'Insurance_Minimum' => 'insurance_minimum',
        'FOV_Percent'       => 'fov_percent',
        'FOV_Minimum'       => 'fov_minimum',
        'Urgent_Percent'    => 'urgent_percent',
        'Urgent_Minimum'    => 'urgent_minimum',
    );

    $fields = array();
    $values = array();
    foreach ($map as $column => $postKey) {
        $fields[] = $column;
        $values[] = isset($_POST[$postKey]) ? floatval($_POST[$postKey]) : 0.0;
    }

    $existing = $db->ExecuteQuery("SELECT Charge_Id FROM tbl_charge_master WHERE Branch_Id=$branchId");
    if (!empty($existing)) {
        $res = $db->updateValue('tbl_charge_master', $fields, $values, "Branch_Id=$branchId");
    } else {
        $fields[] = 'Branch_Id';
        $values[] = $branchId;
        $fields[] = 'Is_Active';
        $values[] = 1;
        $res = $db->valInsert('tbl_charge_master', $fields, $values);
    }

    echo $res ? "1" : "0";
    exit();
}

if (isset($_POST['type']) && $_POST['type'] == "saveZonePickup") {
    $clientId = intval($_POST['client_id']);
    $rates = isset($_POST['rates']) && is_array($_POST['rates']) ? $_POST['rates'] : array();

    if ($clientId <= 0) {
        echo "0";
        exit();
    }

    foreach ($rates as $zoneId => $amount) {
        $zoneId = intval($zoneId);
        if ($zoneId <= 0) {
            continue;
        }
        $existing = $db->ExecuteQuery("SELECT Pickup_Charge_Id FROM tbl_pickup_charges
            WHERE Client_Id=$clientId AND Zone_Id=$zoneId");

        if (trim($amount) === '') {
            if (!empty($existing)) {
                $db->deleteRecords('tbl_pickup_charges', "Client_Id=$clientId AND Zone_Id=$zoneId");
            }
            continue;
        }

        $charge = floatval($amount);
        if (!empty($existing)) {
            $db->updateValue('tbl_pickup_charges', array('Default_Charge', 'Is_Active'), array($charge, 1),
                "Client_Id=$clientId AND Zone_Id=$zoneId");
        } else {
            $db->valInsert('tbl_pickup_charges',
                array('Client_Id', 'Zone_Id', 'Default_Charge', 'Is_Active'),
                array($clientId, $zoneId, $charge, 1));
        }
    }

    echo "1";
    exit();
}

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
