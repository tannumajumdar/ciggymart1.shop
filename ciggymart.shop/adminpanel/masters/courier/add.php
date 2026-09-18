<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

if(isset($_POST['addPartner'])) {
    $name = $_POST['partner_name'];
    $url = $_POST['tracking_url'];
    $contact = $_POST['contact_no'];
    
    $db->Execute("INSERT INTO tbl_courier_partners (Partner_Name, Tracking_URL, Contact_No) VALUES ('$name', '$url', '$contact')");
    echo "<script>window.location.href='index.php';</script>";
    exit;
}
?>

<div class="modern-dashboard">
    <div class="module-header">
        <div class="header-left">
            <h2>Add Courier Partner</h2>
            <p>Register a new delivery partner in the system.</p>
        </div>
        <div class="header-right">
            <a class="btn-secondary" href="index.php"><i class="fa fa-arrow-left"></i> Back to List</a>
        </div>
    </div>

    <div class="stat-card" style="max-width: 600px;">
        <form method="post" action="add.php" class="modern-form">
            <div class="form-group mb-3">
                <label>Partner Name <span class="text-danger">*</span></label>
                <input type="text" name="partner_name" class="modern-input" required placeholder="e.g. BlueDart, Delhivery">
            </div>
            
            <div class="form-group mb-3">
                <label>Contact Number</label>
                <input type="text" name="contact_no" class="modern-input" placeholder="Support or Account Manager Contact">
            </div>
            
            <div class="form-group mb-4">
                <label>Tracking URL Format</label>
                <input type="url" name="tracking_url" class="modern-input" placeholder="https://example.com/track?awb=">
                <small class="text-muted">Users can click this to track third-party shipments.</small>
            </div>
            
            <div class="form-actions mt-4 text-end">
                <button type="submit" name="addPartner" class="btn-primary"><i class="fa fa-save"></i> Save Partner</button>
            </div>
        </form>
    </div>
</div>

<?php include(PATH_ADMIN_INCLUDE.'/footer.php'); ?>

