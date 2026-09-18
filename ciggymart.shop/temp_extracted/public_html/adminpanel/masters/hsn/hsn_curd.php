<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');

$db = new DBConn();

if (isset($_POST['type']) && $_POST['type'] == "addHSN") {
    $hsn_code = trim($_POST['hsn_code']);
    $description = trim($_POST['description']);
    $gst_percent = floatval($_POST['gst_percent']);
    $cgst_percent = floatval($_POST['cgst_percent']);
    $sgst_percent = floatval($_POST['sgst_percent']);
    $igst_percent = floatval($_POST['igst_percent']);

    $res = $db->valInsert(
        'tbl_hsn_master',
        ['HSN_Code', 'Description', 'GST_Percent', 'CGST_Percent', 'SGST_Percent', 'IGST_Percent', 'Is_Active'],
        [$hsn_code, $description, $gst_percent, $cgst_percent, $sgst_percent, $igst_percent, 1]
    );

    echo $res ? 1 : 0;
    exit();
}

if (isset($_POST['type']) && $_POST['type'] == "deleteHSN") {
    $id = intval($_POST['id']);
    $res = $db->deleteRecords('tbl_hsn_master', "HSN_Id=$id");
    echo $res ? 1 : 0;
    exit();
}
?>
