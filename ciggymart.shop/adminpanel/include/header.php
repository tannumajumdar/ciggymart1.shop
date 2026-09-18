<?php
/*
 * Admin panel shared header: <head>, sidebar, topbar and the dashboard metrics
 * used by home.php.
 *
 * NOTE: this file was rebuilt after refactor_layout.php overwrote it. The markup
 * is reproduced from the last known-good rendered page. The KPI figures below
 * were hard-coded demo values in the original file (the database holds far fewer
 * records); only $totPaid is a real query. Replace them with real queries when
 * the dashboard is wired up for production.
 */
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/head.php');

$db = new DBConn();

if(!isset($_SESSION['user']) || $_SESSION['user'] == "")
{?>
<script>
	window.location.href = '<?php echo PATH_ADMIN_LINK.'/index.php'; ?>';
</script>
<?php exit(); }
?>
<script>
$(document).ready(function() {
    $("#logoff").click(function(){
		$.ajax({
			url:'<?php echo PATH_ADMIN_LINK.'/logout.php'; ?>',
			type:'POST',
			data:{},
			async:false,
			success:function(data){
				if (data=="true") {
					document.location.href='<?php echo PATH_ADMIN_LINK.'/index.php'; ?>';
				}
			}
		});
	});
});
</script>
<?php
// ---- Dashboard metrics used by home.php -------------------------------------
// Real figure: total of every payment receipt on record.
$totPaidRes = $db->ExecuteQuery("SELECT IFNULL(SUM(Payment_Amount),0) AS total FROM tbl_payment_receipts");
$totPaid    = !empty($totPaidRes) ? $totPaidRes[1]['total'] : 0;

// Demo figures carried over from the original file (not derived from the data).
$todayBookings   = 128;
$inTransitCount  = 76;
$deliveredCount  = 45;
$todayBookingVal = 124500;
$outstanding     = 845000;
$totalShipments  = 320;

$today     = date('d M Y');
$yesterday = date('d M Y', strtotime('-1 day'));

$sampleConsignments = array(
    array('no'=>'KE102345','date'=>$today,    'client'=>'ABC Traders',       'dest'=>'Bilaspur',  'status'=>'In Transit','status_class'=>'status-in-transit','amt'=>'620'),
    array('no'=>'KE102344','date'=>$today,    'client'=>'Sharma Enterprises','dest'=>'Raipur',    'status'=>'Delivered', 'status_class'=>'status-delivered', 'amt'=>'450'),
    array('no'=>'KE102343','date'=>$yesterday,'client'=>'Vishal Agency',     'dest'=>'Korba',     'status'=>'Picked Up', 'status_class'=>'status-picked-up', 'amt'=>'780'),
    array('no'=>'KE102342','date'=>$yesterday,'client'=>'CG Mart',           'dest'=>'Jagdalpur', 'status'=>'Pending',   'status_class'=>'status-pending',   'amt'=>'1120'),
    array('no'=>'KE102341','date'=>$yesterday,'client'=>'Singh Distributors','dest'=>'Durg',      'status'=>'Delivered', 'status_class'=>'status-delivered', 'amt'=>'640'),
);
?>
<div class="app-layout">
    <!-- SIDEBAR -->
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <div class="brand-logo">
                <!-- Similar SVG or icon based on the mockup -->
                <svg viewBox="0 0 24 24" width="28" height="28" fill="var(--brand-orange)"><path d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                <div class="brand-text">
                    <span class="brand-keshri">KESHRI EXPRESS</span>
                    <span class="brand-sub">Courier ERP</span>
                    <span class="brand-tag">Connecting Chhattisgarh</span>
                </div>
            </div>
        </div>

        <div class="sidebar-menu-scroll">
            
            <ul class="sidebar-menu">
                <li >
                    <a href="<?php echo PATH_ADMIN_LINK; ?>/home.php"><i class="fa fa-home"></i> Dashboard</a>
                </li>
                
                <li class="menu-category">OPERATIONS</li>
                <li ><a href="<?php echo PATH_ADMIN_LINK; ?>/consignments/"><i class="fa fa-cube"></i> Consignments</a></li>
                <li ><a href="<?php echo PATH_ADMIN_LINK; ?>/../index.php#tracking-section"><i class="fa fa-search"></i> Tracking</a></li>
                <li ><a href="<?php echo PATH_ADMIN_LINK; ?>/masters/destination/index.php"><i class="fa fa-map-marker"></i> Destinations (PIN Codes)</a></li>
                
                <li class="menu-category">BILLING</li>
                <li ><a href="<?php echo PATH_ADMIN_LINK; ?>/masters/client"><i class="fa fa-users"></i> Customers</a></li>
                <li ><a href="<?php echo PATH_ADMIN_LINK; ?>/masters/rate"><i class="fa fa-table"></i> Rate Master</a></li>
                <li ><a href="<?php echo PATH_ADMIN_LINK; ?>/invoice/"><i class="fa fa-file-text-o"></i> Invoices</a></li>
                
                <li class="menu-category">ACCOUNTING</li>
                <li ><a href="<?php echo PATH_ADMIN_LINK; ?>/payment/"><i class="fa fa-money"></i> Payments Received</a></li>
                <li ><a href="<?php echo PATH_ADMIN_LINK; ?>/accounting/payments_made.php"><i class="fa fa-credit-card"></i> Payments Made</a></li>
                <li ><a href="<?php echo PATH_ADMIN_LINK; ?>/accounting/expenses.php"><i class="fa fa-pie-chart"></i> Expenses</a></li>
                <li ><a href="<?php echo PATH_ADMIN_LINK; ?>/accounting/ledger.php"><i class="fa fa-address-book"></i> Customer Ledger</a></li>
                <li ><a href="<?php echo PATH_ADMIN_LINK; ?>/accounting/outstanding.php"><i class="fa fa-clock-o"></i> Outstanding</a></li>
                
                <li class="menu-category">REPORTS</li>
                <li ><a href="<?php echo PATH_ADMIN_LINK; ?>/invoice-report.php"><i class="fa fa-bar-chart"></i> Billing Reports</a></li>
                <li><a href="<?php echo PATH_ADMIN_LINK; ?>/reports/consignments.php"><i class="fa fa-file-excel-o"></i> Consignment Reports</a></li>
                <li ><a href="<?php echo PATH_ADMIN_LINK; ?>/paymentreport/"><i class="fa fa-line-chart"></i> Payment Reports</a></li>
                <li><a href="<?php echo PATH_ADMIN_LINK; ?>/reports/customers.php"><i class="fa fa-users"></i> Customer Reports</a></li>
                
                <li class="menu-category">SETTINGS</li>
                <li ><a href="<?php echo PATH_ADMIN_LINK; ?>/masters/courier"><i class="fa fa-truck"></i> Courier Partner</a></li>
                <li ><a href="<?php echo PATH_ADMIN_LINK; ?>/masters/operator"><i class="fa fa-user-circle"></i> Users & Roles</a></li>
                <li ><a href="<?php echo PATH_ADMIN_LINK; ?>/settings/company.php"><i class="fa fa-cog"></i> Company Settings</a></li>
                <li ><a href="<?php echo PATH_ADMIN_LINK; ?>/settings/invoice.php"><i class="fa fa-file-pdf-o"></i> Invoice Settings</a></li>
            </ul>
        </div>
    </aside>
    
    <script>
    (function() {
        var currentUrl = window.location.href.split('?')[0].split('#')[0];
        var lis = document.querySelectorAll('.sidebar-menu li');
        lis.forEach(function(li) { li.classList.remove('active'); });
        
        var links = Array.from(document.querySelectorAll('.sidebar-menu a')).filter(function(a) { return a.getAttribute('href') !== '#'; });
        links.sort(function(a, b) { return b.href.length - a.href.length; });
        
        var found = false;
        for(var i = 0; i < links.length; i++) {
            var linkUrl = links[i].href.split('?')[0].split('#')[0];
            if (currentUrl === linkUrl || (currentUrl.startsWith(linkUrl) && linkUrl.endsWith('/'))) {
                links[i].parentElement.classList.add('active');
                found = true;
                break;
            }
        }
        if(!found) {
            var home = document.querySelector('.sidebar-menu a[href*="home.php"]');
            if(home) home.parentElement.classList.add('active');
        }
    })();
    </script>

    
<div class="app-main-wrapper">
    <!-- TOPBAR -->
    <header class="app-topbar">
        <div class="topbar-left">
            <button class="btn-menu-toggle"><i class="fa fa-bars"></i></button>
            <div class="search-box">
                <i class="fa fa-search search-icon"></i>
                <input type="text" placeholder="Search AWB / Customer / Mobile / Invoice...">
                <span class="search-shortcut">Ctrl + K</span>
            </div>
        </div>
        
        <div class="topbar-right">
            <button class="btn-notification">
                <i class="fa fa-bell"></i>
                <span class="badge">5</span>
            </button>
            
            <div class="user-profile">
                <div class="user-avatar"><i class="fa fa-user"></i></div>
                <div class="user-info">
                    <span class="user-name">Admin</span>
                    <span class="user-role">Super Admin</span>
                </div>
            </div>
            
            <button id="logoff" class="btn-logout" title="Logout"><i class="fa fa-sign-out"></i></button>
        </div>
    </header>



<script>
document.addEventListener('DOMContentLoaded', function() {
    var menuBtn = document.querySelector('.btn-menu-toggle');
    if (menuBtn) {
        menuBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (window.innerWidth <= 768) {
                document.body.classList.toggle('sidebar-open');
            } else {
                document.body.classList.toggle('sidebar-collapsed');
            }
        });
    }
    
    var mainWrapper = document.querySelector('.app-main-wrapper');
    if (mainWrapper) {
        mainWrapper.addEventListener('click', function() {
            if (window.innerWidth <= 768 && document.body.classList.contains('sidebar-open')) {
                document.body.classList.remove('sidebar-open');
            }
        });
    }
});
</script>
