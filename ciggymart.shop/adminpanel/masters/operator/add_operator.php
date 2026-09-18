<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

if(isset($_POST['addOperator'])) {
    $name = $_POST['operator_name'];
    $contact = $_POST['operator_contact'];
    $email = $_POST['email'];
    $pwd = $_POST['password'];
    $address = $_POST['o_address'];
    $state_id = !empty($_POST['state_id']) ? intval($_POST['state_id']) : 'NULL';
    
    $db->Execute("INSERT INTO tbl_operators (Operator_name, Contact_No, Email, Password, Address, State_Id) VALUES ('$name', '$contact', '$email', '$pwd', '$address', $state_id)");
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

$states = $db->ExecuteQuery("SELECT State_Id, State_Name FROM tbl_states ORDER BY State_Name");
?>

<div class="modern-dashboard">
    <div class="module-header">
        <div class="header-left">
            <h2>Add New User</h2>
            <p>Create a new operator account for the ERP.</p>
        </div>
        <div class="header-right">
            <a class="btn-secondary" href="index.php"><i class="fa fa-arrow-left"></i> Back to List</a>
        </div>
    </div>

    <div class="stat-card" style="max-width: 800px;">
        <form method="post" action="add_operator.php" class="modern-form">
            
            <div class="form-group mb-3">
                <label>User Name <span class="text-danger">*</span></label>
                <input type="text" name="operator_name" class="modern-input" required placeholder="Full Name">
            </div>
            
            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label>Contact Number <span class="text-danger">*</span></label>
                    <input type="text" name="operator_contact" class="modern-input" required placeholder="Phone Number">
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label>State / Region</label>
                    <select name="state_id" class="modern-input">
                        <option value="">-- Select State --</option>
                        <?php foreach($states as $s): ?>
                            <option value="<?php echo $s['State_Id']; ?>"><?php echo htmlspecialchars($s['State_Name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group mb-3">
                <label>Address</label>
                <textarea name="o_address" class="modern-input" rows="3" placeholder="Full Address"></textarea>
            </div>
            
            <hr class="my-4">
            
            <h5 class="mb-3">Login Credentials</h5>
            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label>Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="modern-input" required placeholder="Email (Username)">
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label>Password <span class="text-danger">*</span></label>
                    <input type="text" name="password" class="modern-input" required placeholder="Password">
                </div>
            </div>
            
            <div class="form-actions mt-4 text-end">
                <button type="submit" name="addOperator" class="btn-primary"><i class="fa fa-save"></i> Create User</button>
            </div>
            
        </form>
    </div>
</div>

<?php include(PATH_ADMIN_INCLUDE.'/footer.php'); ?>

