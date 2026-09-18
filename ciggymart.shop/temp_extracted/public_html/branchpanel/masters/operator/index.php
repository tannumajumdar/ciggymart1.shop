<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(BRANCH_PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

// get list of Operators
$operatorList=$db->ExecuteQuery("SELECT Operator_Id, Operator_name, Address, State_Name, Contact_No, Email, Password, CASE WHEN Is_Active=1 THEN 'Block' ELSE 'Unblock' END AS Status
FROM tbl_operators O
INNER JOIN tbl_states S ON S.State_Id = O.State_Id
WHERE Branch_Id=".$_SESSION['buser']); 
?>

<script type="text/javascript"  src="operator.js" ></script>

<div class="pageTitle">
	<div class="ef_header_tools pull-right">
        <a class="btn btn-success btn-sm" href="add_operator.php"><i class="glyphicon glyphicon-plus"></i> <strong>Add New Operator</strong></a>
    </div>
	<h1 class="pull-left">Operators List</h1>
    <div class="clearfix"></div>
</div>
<div class="main">
	
	<div class="clear formbgstyle">
        <table class="table table-hover table-bordered" id="addedProducts">
          <thead>
            <tr class="success">
              <th>Sno.</th>
              <th>Operator Name</th>
              <th>Address</th>
              <th>State</th>
              <th>Contact No</th>
              <th>Email</th>
              <th>Password</th>
              <th>Stauts</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
	<?php 
        $i=1;
        foreach($operatorList as $operatorListVal){ ?>
            <tr>
              <td><?php echo $i;?></td>
              <td><?php echo $operatorListVal['Operator_name'];?></td>
              <td><?php echo $operatorListVal['Address'];?></td>
              <td><?php echo $operatorListVal['State_Name'];?></td>
              <td><?php echo $operatorListVal['Contact_No'];?></td>
              <td><?php echo $operatorListVal['Email'];?></td>
              <td><?php echo $operatorListVal['Password'];?></td>
              <td>
              	<button type="button" id="<?php echo $operatorListVal['Status']."-".$operatorListVal['Operator_Id']; ?>" class="status btn btn-sm <?php echo (($operatorListVal['Status']=="Block") ? 'btn-danger':'btn-info')?>"><i class="fa <?php echo (($operatorListVal['Status']=="Block") ? 'fa-lock':'fa-unlock')?>" aria-hidden="true"></i> <?php echo $operatorListVal['Status']; ?></button>
              </td>
              
              <td><button type="button" id="editbtn" class="btn btn-success btn-sm" onClick="window.location.href='edit_operator.php?id=<?php echo $operatorListVal['Operator_Id'];?>'" > <span class="glyphicon glyphicon-edit"></span> Edit </button>
                <button type="button" class="btn btn-danger btn-sm delete" id="<?php echo $operatorListVal['Operator_Id']; ?>" name="delete"> <span class="glyphicon glyphicon-trash"></span> Delete </button></td>
            </tr>
            <?php $i++;} ?>
          </tbody>
        </table>
    </div>
</div>