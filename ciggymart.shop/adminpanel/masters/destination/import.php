<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');

$db = new DBConn();

$msg = '';
$msgType = '';
$totalProcessed = 0;
$totalInserted = 0;
$totalUpdated = 0;
$totalFailed = 0;

if(isset($_POST['uploadCsv'])) {
    if(!empty($_FILES['csv_file']['name'])) {
        $fileName = $_FILES['csv_file']['tmp_name'];
        $file = fopen($fileName, "r");
        
        // Skip header
        fgetcsv($file, 10000, ",");
        
        // Cache states
        $states = $db->ExecuteQuery("SELECT State_Id, LOWER(State_Name) AS State_Name FROM tbl_states");
        $stateMap = [];
        foreach($states as $st) {
            $stateMap[$st['State_Name']] = $st['State_Id'];
        }
        
        while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
            $totalProcessed++;
            
            $destCode = trim($column[0]);
            $destName = trim($column[1]);
            $stateName = strtolower(trim($column[2]));
            $pincode = trim($column[3]);
            $isOda = intval($column[4]);
            $odaCharge = floatval($column[5]);
            $doorCharge = floatval($column[6]);
            
            if(empty($destCode) || empty($destName) || empty($stateName)) {
                $totalFailed++;
                continue;
            }
            
            $stateId = isset($stateMap[$stateName]) ? $stateMap[$stateName] : 0;
            if($stateId == 0) {
                // Try to create state if it doesn't exist
                $stCode = strtoupper(substr($stateName, 0, 3));
                $stNameDb = ucwords($stateName);
                $db->Execute("INSERT INTO tbl_states (State_Code, State_Name, Is_Active) VALUES ('$stCode', '$stNameDb', 1)");
                $stateId = $db->last_insert_id();
                $stateMap[$stateName] = $stateId;
            }
            
            // Check if exists (by Dest_Code or Name)
            $exists = $db->ExecuteQuery("SELECT Destination_Id FROM tbl_destinations WHERE Destination_Code = '$destCode' OR Pincode = '$pincode'");
            
            if(!empty($exists) && count($exists) > 0) {
                // Update
                $id = $exists[0]['Destination_Id'];
                $sql = "UPDATE tbl_destinations SET 
                        Destination_Name='$destName', State_Id=$stateId, Pincode='$pincode', 
                        Is_ODA=$isOda, ODA_Charge=$odaCharge, Door_Delivery_Charge=$doorCharge 
                        WHERE Destination_Id=$id";
                $db->Execute($sql);
                $totalUpdated++;
            } else {
                // Insert
                $sql = "INSERT INTO tbl_destinations (Destination_Code, Destination_Name, State_Id, Pincode, Is_ODA, ODA_Charge, Door_Delivery_Charge, Is_Active) 
                        VALUES ('$destCode', '$destName', $stateId, '$pincode', $isOda, $odaCharge, $doorCharge, 1)";
                $db->Execute($sql);
                $totalInserted++;
            }
        }
        
        $msgType = 'success';
        $msg = "Successfully processed $totalProcessed records! Inserted: $totalInserted | Updated: $totalUpdated | Failed: $totalFailed";
    } else {
        $msgType = 'danger';
        $msg = "Please select a valid CSV file.";
    }
}
?>

<div class="modern-page-head">
    <div>
        <h1><i class="fa fa-upload text-primary"></i> Bulk Import Destinations & PIN Codes</h1>
        <span style="color:#64748b; font-size:13px;">Upload thousands of Pincodes instantly using a CSV file. The system will automatically map States and update existing codes to prevent duplicates.</span>
    </div>
    <div>
        <a href="index.php" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back to Destinations</a>
    </div>
</div>

<div class="container-fluid" style="padding: 0 24px 50px 24px;">

    <?php if(!empty($msg)) { ?>
        <div class="alert alert-<?php echo $msgType; ?>" style="border-radius: 8px; font-weight:600;">
            <?php echo $msg; ?>
        </div>
    <?php } ?>

    <div class="row">
        <!-- Left: Upload Form -->
        <div class="col-md-5">
            <div class="erp-card">
                <div class="erp-card-header">
                    <h3 class="erp-card-title"><i class="fa fa-file-excel-o text-success"></i> Upload CSV File</h3>
                </div>
                <div class="erp-card-body">
                    <p style="color:#64748b; font-size:13px; margin-bottom: 20px;">
                        Make sure your CSV file follows the exact template structure. If you are using Excel (.xlsx), use <strong>Save As -> CSV (Comma delimited)</strong> before uploading.
                    </p>
                    
                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group custom-fg">
                            <label>Select CSV File <span class="text-danger">*</span></label>
                            <input type="file" name="csv_file" accept=".csv" class="form-control" style="padding:10px; height:auto; background:#f8fafc;" required>
                        </div>
                        
                        <div style="margin-top: 24px;">
                            <button type="submit" name="uploadCsv" class="btn btn-success btn-block" style="padding: 10px; font-size:15px;"><i class="fa fa-cloud-upload"></i> Upload & Process PIN Codes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: Instructions & Template -->
        <div class="col-md-7">
            <div class="erp-card" style="border-left: 3px solid #3b82f6;">
                <div class="erp-card-header">
                    <h3 class="erp-card-title"><i class="fa fa-info-circle text-primary"></i> Data Format Instructions</h3>
                </div>
                <div class="erp-card-body">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                        <span style="color:#334155; font-weight:600;">Download the strict CSV template for uploading:</span>
                        <a href="download_template.php" class="btn btn-primary btn-sm"><i class="fa fa-download"></i> Download Template</a>
                    </div>
                    
                    <div class="erp-table-responsive" style="margin-top:20px;">
                        <table class="table table-bordered" style="font-size:12px;">
                            <thead style="background:#f1f5f9;">
                                <tr>
                                    <th>Destination Code</th>
                                    <th>City Name</th>
                                    <th>State Name</th>
                                    <th>Pincode</th>
                                    <th>Is ODA</th>
                                    <th>ODA Charge</th>
                                    <th>Door Charge</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>MUM01</td>
                                    <td>Mumbai</td>
                                    <td>Maharashtra</td>
                                    <td>400001</td>
                                    <td>0</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                </tr>
                                <tr>
                                    <td>REM02</td>
                                    <td>Remote Village</td>
                                    <td>Karnataka</td>
                                    <td>560123</td>
                                    <td>1</td>
                                    <td>250.00</td>
                                    <td>50.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <ul style="color:#64748b; font-size:13px; margin-top:16px; padding-left:20px;">
                        <li><strong>Is ODA:</strong> Use <strong>1</strong> for Yes, and <strong>0</strong> for No.</li>
                        <li><strong>State Name:</strong> If the state does not exist in the database, the system will automatically create it.</li>
                        <li><strong>Duplicates:</strong> If the <em>Destination Code</em> or <em>Pincode</em> already exists, the system will update the existing record with the new charges.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include(PATH_ADMIN_INCLUDE.'/footer.php'); ?>
