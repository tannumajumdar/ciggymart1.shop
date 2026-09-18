<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
$db = new DBConn();

if (isset($_POST['type']) && $_POST['type'] == 'addClient') {
    // Extract variables
    $code = $db->cleanInput($_POST['Client_Code']);
    $name = $db->cleanInput($_POST['Client_Name']);
    $company = $db->cleanInput($_POST['Company_Name']);
    $address = $db->cleanInput($_POST['Address']);
    $billing = $db->cleanInput($_POST['Billing_Address']);
    $contact = $db->cleanInput($_POST['Contact_No']);
    $person = $db->cleanInput($_POST['Contact_Person']);
    $email = $db->cleanInput($_POST['Email']);
    $password = $db->cleanInput($_POST['Password']);
    $gstin = $db->cleanInput($_POST['GSTIN_No']);
    $pan = $db->cleanInput($_POST['PAN_No']);
    $branch = intval($_POST['Branch_Id']);
    $dest = intval($_POST['Destination_Id']);
    
    // Convert switch value to 1 or 0
    $gst_state = (isset($_POST['GST_Within_State']) && $_POST['GST_Within_State'] == 'on') ? 1 : 0;
    
    // Default surcharges
    $ins = floatval($_POST['Insurance_Percent']);
    $fuel = floatval($_POST['Fuel_Surcharge']);
    $pickup = floatval($_POST['Pickup_Charge']);
    $docket = floatval($_POST['Docket_Charge']);
    $door = floatval($_POST['Door_Delivery_Charge']);

    $sql = "INSERT INTO tbl_clients 
            (Joining_Date, Client_Code, Client_Name, Company_Name, Destination_Id, Address, Billing_Address, Contact_No, Contact_Person, GST_Within_State, GSTIN_No, PAN_No, Insurance_Percent, Fuel_Surcharge, Pickup_Charge, Docket_Charge, Door_Delivery_Charge, Email, Password, Branch_Id, Is_Active)
            VALUES 
            (CURDATE(), '$code', '$name', '$company', $dest, '$address', '$billing', '$contact', '$person', $gst_state, '$gstin', '$pan', $ins, $fuel, $pickup, $docket, $door, '$email', '$password', $branch, 1)";
            
    $res = $db->query($sql);
    
    if ($res) {
        echo "true";
    } else {
        echo "Error saving data.";
    }
}

else if (isset($_POST['type']) && $_POST['type'] == 'getClient') {
    $id = intval($_POST['client_id']);
    $res = $db->ExecuteQuery("SELECT * FROM tbl_clients WHERE Client_Id = $id");
    
    if (!empty($res)) {
        echo json_encode($res[1]);
    } else {
        echo json_encode(['error' => 'Not found']);
    }
}

else if (isset($_POST['type']) && $_POST['type'] == 'editClient') {
    $id = intval($_POST['Client_Id']);
    $code = $db->cleanInput($_POST['Client_Code']);
    $name = $db->cleanInput($_POST['Client_Name']);
    $company = $db->cleanInput($_POST['Company_Name']);
    $address = $db->cleanInput($_POST['Address']);
    $billing = $db->cleanInput($_POST['Billing_Address']);
    $contact = $db->cleanInput($_POST['Contact_No']);
    $person = $db->cleanInput($_POST['Contact_Person']);
    $email = $db->cleanInput($_POST['Email']);
    $password = $db->cleanInput($_POST['Password']);
    $gstin = $db->cleanInput($_POST['GSTIN_No']);
    $pan = $db->cleanInput($_POST['PAN_No']);
    $branch = intval($_POST['Branch_Id']);
    $dest = intval($_POST['Destination_Id']);
    
    $gst_state = (isset($_POST['GST_Within_State']) && $_POST['GST_Within_State'] == 'on') ? 1 : 0;
    
    $ins = floatval($_POST['Insurance_Percent']);
    $fuel = floatval($_POST['Fuel_Surcharge']);
    $pickup = floatval($_POST['Pickup_Charge']);
    $docket = floatval($_POST['Docket_Charge']);
    $door = floatval($_POST['Door_Delivery_Charge']);

    $sql = "UPDATE tbl_clients SET 
            Client_Code='$code', Client_Name='$name', Company_Name='$company', Destination_Id=$dest, 
            Address='$address', Billing_Address='$billing', Contact_No='$contact', Contact_Person='$person',
            GST_Within_State=$gst_state, GSTIN_No='$gstin', PAN_No='$pan', 
            Insurance_Percent=$ins, Fuel_Surcharge=$fuel, Pickup_Charge=$pickup, Docket_Charge=$docket, Door_Delivery_Charge=$door,
            Email='$email', Password='$password', Branch_Id=$branch 
            WHERE Client_Id = $id";
            
    $res = $db->query($sql);
    
    if ($res) {
        echo "true";
    } else {
        echo "Error updating data.";
    }
}

else if (isset($_POST['type']) && $_POST['type'] == 'toggleStatus') {
    $id = intval($_POST['client_id']);
    $status = intval($_POST['status']); // Will be toggled in query
    $newStatus = ($status == 1) ? 0 : 1;
    
    $res = $db->query("UPDATE tbl_clients SET Is_Active = $newStatus WHERE Client_Id = $id");
    
    if ($res) {
        echo "true";
    } else {
        echo "Error toggling status.";
    }
}
?>
