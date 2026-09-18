<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(BRANCH_PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

// get list of rate
$zone=$db->ExecuteQuery("SELECT Rate_Id, Zone_Code, Zone_Name,Service_Tax, Insurance FROM rate_master r INNER JOIN zone_master z
on r.Zone=z.Zone_Id where Customer_Id is null");

?>
<script type="text/javascript" src="rate.js" ></script>
<div class="main">
	<div class="ef_header_tools pull-right">
        <a class="btn btn-success btn-sm" href="ratelist.php"><i class="glyphicon glyphicon-list"></i> <strong>Custom Rate List</strong></a>
        <a class="btn btn-success btn-sm" href="index.php"><i class="glyphicon glyphicon-plus"></i> <strong>Add Custom Rate</strong></a>
    </div>
	<h4>Default Rate List</h4>
	<div class="clear formbgstyle">
        <table class="table table-hover table-bordered" id="addedProducts">
          <thead>
            <tr class="success">
              <th>Sno.</th>
              <th>Zone Code</th>
              <th>Zone Name</th>
              <th>Service Tax</th>
              <th>Insurance</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php 
                $i=1;
                foreach($zone as $val){ ?>
            <tr>
              <td><?php echo $i;?></td>
              <td><?php echo $val['Zone_Code'];?></td>
              <td><?php echo $val['Zone_Name'];?></td>
              <td><?php echo $val['Service_Tax'];?></td>
              <td><?php echo $val['Insurance'];?></td>
              
              <td><button type="button" id="view" class="btn btn-info btn-sm" onClick="window.location.href='viewdefaultrate.php?id=<?php echo $val['Rate_Id'];?>'" >  View Rate </button>
              </td>
            </tr>
            <?php $i++;} ?>
          </tbody>
        </table>
    </div>
</div>