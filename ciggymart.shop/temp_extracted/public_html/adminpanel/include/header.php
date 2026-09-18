<?php
include(PATH_MASTERS.'/include/head.php');
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

<header class="modern-header">
    <div class="brand-title">
        <i class="fa fa-cubes"></i> KESHRI EXPRESS <span class="brand-badge">ADMIN ERP</span>
    </div>
    <div class="user-info-area">
        <div class="branch-meta">
            <strong>Administrator</strong><br>
            <span style="font-size: 11px; color:#94a3b8;"><?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']) : 'Super Admin'; ?></span>
        </div>
        <button type="button" class="btn-header-logoff" id="logoff">
            <i class="fa fa-power-off"></i> Logoff
        </button>
    </div>
</header>

<nav class="navbar navbar-inverse modern-nav" role="navigation">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#admin-navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>

    <div class="collapse navbar-collapse" id="admin-navbar-collapse">
      <ul class="nav navbar-nav">
        <li><a href="<?php echo PATH_ADMIN_LINK; ?>/home.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown"><i class="fa fa-sitemap"></i> Masters <b class="caret"></b></a>
          <ul class="dropdown-menu multi-level">
            <li class="dropdown-submenu">
            	<a tabindex="-1" href="#"><i class="fa fa-map-marker"></i> Location & Tax Masters</a>
                <ul class="dropdown-menu">
                    <li><a tabindex="-1" href="<?php echo MASTERS_LINK_CONTROL?>/zone">Operational Zone</a></li>
                    <li><a tabindex="-1" href="<?php echo MASTERS_LINK_CONTROL?>/state">State</a></li>
                    <li><a tabindex="-1" href="<?php echo MASTERS_LINK_CONTROL?>/destination">City / Destination Master</a></li>
                    <li><a tabindex="-1" href="<?php echo MASTERS_LINK_CONTROL?>/oda">ODA Master</a></li>
                    <li><a tabindex="-1" href="<?php echo MASTERS_LINK_CONTROL?>/taxes">Tax Slabs</a></li>
                    <li><a tabindex="-1" href="<?php echo MASTERS_LINK_CONTROL?>/hsn">HSN Master</a></li>
                </ul>
            </li>
            <li class="dropdown-submenu">
            	<a tabindex="-1" href="#"><i class="fa fa-calculator"></i> Charge Masters</a>
                <ul class="dropdown-menu">
                    <li><a tabindex="-1" href="<?php echo MASTERS_LINK_CONTROL?>/charges">Pickup, Door & Docket Charges</a></li>
                </ul>
            </li>
            <li><a tabindex="-1" href="<?php echo MASTERS_LINK_CONTROL?>/branch"><i class="fa fa-building"></i> Branch Management</a></li>
          </ul>
        </li>

        <li><a href="<?php echo PATH_ADMIN_LINK?>/invoice-report.php"><i class="fa fa-file-text-o"></i> Invoice Reports</a></li>
        <li><a href="<?php echo PATH_ADMIN_LINK?>/settings.php"><i class="fa fa-cogs"></i> ERP Settings</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="clear"></div>
