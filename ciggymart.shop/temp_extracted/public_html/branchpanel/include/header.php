<?php
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(BRANCH_PATH_MASTERS.'/include/head.php');

$db = new DBConn();

if(!isset($_SESSION['buser']) || $_SESSION['buser'] == "")
{?>
<script>
	window.location.href = '<?php echo BRANCH_PATH_ADMIN_LINK.'/index.php'; ?>';
</script>
<?php exit(); }
?>
<script>
$(document).ready(function() {
	$("#logoff").click(function(){
		$.ajax({
			url:'<?php echo BRANCH_PATH_ADMIN_LINK.'/branch_logout.php'; ?>',
			type:'POST',
			data:{},
			async:false,
			success:function(data){
				if (data=="true") {
					document.location.href='<?php echo BRANCH_PATH_ADMIN_LINK.'/index.php'; ?>';
				}
			}
		});
	});
});
</script>
<?php 
$sql="SELECT Branch_Code, Branch_Name, Franchise_Name FROM tbl_branchs WHERE Branch_Id='".intval($_SESSION['buser'])."'";
$res=$db->ExecuteQuery($sql);
$branchCode = !empty($res) ? $res[1]['Branch_Code'] : '';
$branchName = !empty($res) ? $res[1]['Branch_Name'] : 'Branch';
?>

<header class="modern-header">
    <div class="brand-title">
        <i class="fa fa-truck"></i> KESHRI EXPRESS <span class="brand-badge">BRANCH ERP</span>
    </div>
    <div class="user-info-area">
        <div class="branch-meta">
            <strong><?php echo htmlspecialchars($branchName); ?></strong> (<?php echo htmlspecialchars($branchCode); ?>)<br>
            <span style="font-size: 11px; color:#94a3b8;">Franchise: <?php echo !empty($res[1]['Franchise_Name']) ? htmlspecialchars($res[1]['Franchise_Name']) : 'Main Hub'; ?></span>
        </div>
        <button type="button" class="btn-header-logoff" id="logoff">
            <i class="fa fa-power-off"></i> Logoff
        </button>
    </div>
</header>

<nav class="navbar navbar-inverse modern-nav" role="navigation">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#branch-navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>

    <div class="collapse navbar-collapse" id="branch-navbar-collapse">
      <ul class="nav navbar-nav">
        <li><a href="<?php echo BRANCH_PATH_ADMIN_LINK; ?>/home.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown"><i class="fa fa-folder-open"></i> Masters <b class="caret"></b></a>
          <ul class="dropdown-menu">
            <li><a tabindex="-1" href="<?php echo BRANCH_MASTERS_LINK_CONTROL?>/clients"><i class="fa fa-users"></i> Clients Master</a></li>
            <li><a tabindex="-1" href="<?php echo BRANCH_MASTERS_LINK_CONTROL?>/rate"><i class="fa fa-table"></i> Rate Master</a></li>
            <li><a tabindex="-1" href="<?php echo BRANCH_MASTERS_LINK_CONTROL?>/operator"><i class="fa fa-user"></i> Operators</a></li>
            <li><a tabindex="-1" href="<?php echo BRANCH_MASTERS_LINK_CONTROL?>/banks"><i class="fa fa-university"></i> Banks</a></li>
          </ul>
        </li>

        <li><a href="<?php echo BRANCH_PATH_ADMIN_LINK?>/consignments/"><i class="fa fa-barcode"></i> Consignment Booking</a></li>
        
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown"><i class="fa fa-file-text-o"></i> Billing & Payments <b class="caret"></b></a>
          <ul class="dropdown-menu">
            <li><a tabindex="-1" href="<?php echo BRANCH_PATH_ADMIN_LINK?>/invoice/"><i class="fa fa-plus-circle"></i> Generate Invoice</a></li>
            <li><a tabindex="-1" href="<?php echo BRANCH_PATH_ADMIN_LINK?>/invoice/report.php"><i class="fa fa-file-pdf-o"></i> Invoice Report</a></li>
            <li class="divider"></li>
            <li><a tabindex="-1" href="<?php echo BRANCH_PATH_ADMIN_LINK?>/payment/"><i class="fa fa-credit-card"></i> Create Payment Receipt</a></li>
            <li><a tabindex="-1" href="<?php echo BRANCH_PATH_ADMIN_LINK?>/paymentreport/"><i class="fa fa-history"></i> Payment Receipts Report</a></li>
          </ul>
        </li>

        <li><a href="<?php echo BRANCH_PATH_ADMIN_LINK?>/changepwd/"><i class="fa fa-key"></i> Change Password</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="clear"></div>
