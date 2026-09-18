<?php 
@require_once('config.php');
if (file_exists(dirname(__FILE__).'/libraries/classes/DBConn.php')) {
    require_once(dirname(__FILE__).'/libraries/classes/DBConn.php');
}

$db = null;
$trackResult = null;
$searchedAWB = '';
$totCount = 154200;
$totBranches = 38;
$totDest = 1850;

try {
    if (class_exists('DBConn')) {
        $db = new DBConn();
        if (!empty($_GET['awb'])) {
            $searchedAWB = $db->escape(trim($_GET['awb']));
            $sql = "SELECT C.*, DATE_FORMAT(C.Date_Of_Submit,'%d-%m-%Y') AS BookingDate, 
                    D.Destination_Name, S.State_Name, B.Branch_Name, B.Contact_No AS Branch_Phone, CL.Client_Name
                    FROM tbl_consignments C
                    LEFT JOIN tbl_destinations D ON D.Destination_Id = C.Destination_Id
                    LEFT JOIN tbl_states S ON S.State_Id = D.State_Id
                    LEFT JOIN tbl_branchs B ON B.Branch_Id = C.Branch_Id
                    LEFT JOIN tbl_clients CL ON CL.Client_Id = C.Client_Id
                    WHERE C.Consignment_No='$searchedAWB' LIMIT 1";
            $trackResult = $db->ExecuteQuery($sql);
        }

        $statBookings = $db->ExecuteQuery("SELECT COUNT(*) AS total_count FROM tbl_consignments");
        if (!empty($statBookings) && isset($statBookings[1]['total_count']) && intval($statBookings[1]['total_count']) > 0) {
            $totCount = intval($statBookings[1]['total_count']);
        }
        $statBranches = $db->ExecuteQuery("SELECT COUNT(*) AS total_br FROM tbl_branchs WHERE Is_Active=1");
        if (!empty($statBranches) && isset($statBranches[1]['total_br']) && intval($statBranches[1]['total_br']) > 0) {
            $totBranches = intval($statBranches[1]['total_br']);
        }
        $statDest = $db->ExecuteQuery("SELECT COUNT(*) AS total_dst FROM tbl_destinations");
        if (!empty($statDest) && isset($statDest[1]['total_dst']) && intval($statDest[1]['total_dst']) > 0) {
            $totDest = intval($statDest[1]['total_dst']);
        }
    }
} catch (Exception $e) {
    // Graceful fallback for initial DB setup
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keshri Express Logistics — Nationwide Courier & Express Cargo ERP</title>
    <meta name="description" content="Fast, reliable, and secure express logistics, consignment booking, volumetric weight calculation, automated GST tax invoices, and real-time tracking across India.">
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <!-- CSS Dependencies -->
    <link href="css/bootstrap.css" rel="stylesheet" type="text/css" />
    <link href="css/modern_erp.css" rel="stylesheet" type="text/css" />
    
    <!-- jQuery -->
    <script src="js/jquery-1.10.2.js"></script>

    <style>
        :root {
            --brand-primary: #2563eb;
            --brand-primary-dark: #1d4ed8;
            --brand-accent: #0284c7;
            --brand-dark: #0f172a;
            --brand-slate: #1e293b;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Top Navigation */
        .site-navbar {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 16px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .nav-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #ffffff;
        }
        .nav-logo-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #2563eb, #38bdf8);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
        }
        .nav-logo-text {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #ffffff;
        }
        .nav-logo-badge {
            background: #3b82f6;
            color: #ffffff;
            font-size: 10px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 6px;
            margin-left: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            font-size: 13.5px;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .nav-btn-outline {
            color: #cbd5e1;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: transparent;
        }
        .nav-btn-outline:hover {
            color: #ffffff;
            border-color: #ffffff;
            background: rgba(255, 255, 255, 0.05);
            text-decoration: none;
        }
        .nav-btn-primary {
            color: #ffffff;
            background: #2563eb;
            border: 1px solid #2563eb;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
        }
        .nav-btn-primary:hover {
            background: #1d4ed8;
            color: #ffffff;
            text-decoration: none;
            transform: translateY(-1px);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 45%, #172554 100%);
            padding: 65px 20px 85px 20px;
            color: #ffffff;
            position: relative;
            box-shadow: inset 0 -20px 30px rgba(0, 0, 0, 0.25);
        }
        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .hero-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(56, 189, 248, 0.12);
            border: 1px solid rgba(56, 189, 248, 0.3);
            color: #38bdf8;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .hero-heading {
            font-size: 44px;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -1px;
            margin-bottom: 16px;
        }
        .hero-heading span {
            background: linear-gradient(135deg, #38bdf8 0%, #818cf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-lead {
            font-size: 17px;
            color: #94a3b8;
            max-width: 580px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        /* Interactive Portal Widget (Tabs for Track & Logins) */
        .portal-widget-box {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            padding: 0;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #0f172a;
        }
        .portal-tab-header {
            display: flex;
            background: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
        }
        .portal-tab-btn {
            flex: 1;
            padding: 16px 12px;
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            border: none;
            background: transparent;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            outline: none;
        }
        .portal-tab-btn:hover {
            color: #0f172a;
            background: #e2e8f0;
        }
        .portal-tab-btn.active {
            background: #ffffff;
            color: #2563eb;
            border-bottom: 3px solid #2563eb;
        }
        .portal-tab-content {
            padding: 28px 26px;
        }
        .tab-pane-portal {
            display: none;
        }
        .tab-pane-portal.active {
            display: block;
        }

        /* Forms in Widget */
        .widget-form-group {
            margin-bottom: 18px;
        }
        .widget-form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }
        .widget-input {
            width: 100%;
            height: 48px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            padding: 10px 16px;
            font-size: 14.5px;
            font-weight: 500;
            color: #0f172a;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }
        .widget-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            outline: none;
        }
        .widget-submit-btn {
            width: 100%;
            height: 48px;
            border-radius: 8px;
            background: #2563eb;
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }
        .widget-submit-btn:hover {
            background: #1d4ed8;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.35);
        }
        .widget-submit-btn-dark {
            background: #0f172a;
        }
        .widget-submit-btn-dark:hover {
            background: #1e293b;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.35);
        }

        /* Stats Bar */
        .stats-section {
            margin-top: -35px;
            position: relative;
            z-index: 20;
            padding: 0 20px;
        }
        .stats-card-container {
            max-width: 1200px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            padding: 30px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            text-align: center;
        }
        .stat-item {
            padding: 10px;
            border-right: 1px solid #f1f5f9;
        }
        .stat-item:last-child {
            border-right: none;
        }
        .stat-number {
            font-size: 34px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
        }
        .stat-label {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        /* Service Cards Section */
        .services-section {
            max-width: 1200px;
            margin: 80px auto;
            padding: 0 20px;
        }
        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }
        .section-title {
            font-size: 32px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            margin-bottom: 12px;
        }
        .section-subtitle {
            font-size: 16px;
            color: #64748b;
            max-width: 600px;
            margin: 0 auto;
        }
        .service-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }
        .service-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 30px 24px;
            transition: all 0.25s ease;
            display: flex;
            flex-direction: column;
        }
        .service-card:hover {
            border-color: #2563eb;
            transform: translateY(-5px);
            box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1);
        }
        .service-icon-wrap {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .service-card-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 10px 0;
        }
        .service-card-desc {
            font-size: 13.5px;
            color: #64748b;
            line-height: 1.6;
            margin: 0;
        }

        /* Quick Access Callouts */
        .portals-banner-section {
            background: #0f172a;
            color: #ffffff;
            padding: 70px 20px;
            margin-top: 60px;
        }
        .portals-banner-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .portal-quick-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            padding: 30px;
            transition: all 0.2s ease;
        }
        .portal-quick-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: #38bdf8;
        }

        /* Footer */
        .site-footer {
            background: #090d16;
            color: #94a3b8;
            padding: 50px 20px 30px 20px;
            border-top: 1px solid #1e293b;
            font-size: 13.5px;
        }
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 40px;
            padding-bottom: 40px;
            border-bottom: 1px solid #1e293b;
        }
        .footer-bottom {
            max-width: 1200px;
            margin: 30px auto 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #64748b;
            font-size: 12.5px;
        }

        @media (max-width: 991px) {
            .service-grid, .stats-card-container, .portals-banner-container, .footer-container {
                grid-template-columns: 1fr;
            }
            .hero-heading {
                font-size: 34px;
            }
            .stat-item {
                border-right: none;
                border-bottom: 1px solid #f1f5f9;
                padding-bottom: 20px;
            }
            .stat-item:last-child {
                border-bottom: none;
            }
        }
    </style>
</head>
<body>

    <!-- Main Navigation Bar -->
    <nav class="site-navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-logo">
                <div class="nav-logo-icon"><i class="fa fa-cubes"></i></div>
                <div class="nav-logo-text">KESHRI EXPRESS <span class="nav-logo-badge">ERP 2.0</span></div>
            </a>
            <div class="nav-actions">
                <a href="#track" onclick="switchPortalTab('track')" class="nav-btn nav-btn-outline"><i class="fa fa-search"></i> Track AWB</a>
                <a href="branchpanel/" class="nav-btn nav-btn-primary"><i class="fa fa-truck"></i> Branch Portal</a>
                <a href="adminpanel/" class="nav-btn nav-btn-outline"><i class="fa fa-lock"></i> Admin Console</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Tabbed Widget (Tracking & Quick Login) -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="row" style="display:flex; flex-wrap:wrap; align-items:center;">
                <div class="col-md-7 col-xs-12" style="margin-bottom:30px;">
                    <div class="hero-badge-pill">
                        <i class="fa fa-shield"></i> Enterprise Express Courier & Logistics ERP
                    </div>
                    <h1 class="hero-heading">
                        Next-Gen Logistics, Billing & <span>Real-Time Express</span> Network
                    </h1>
                    <p class="hero-lead">
                        End-to-end consignment booking, automated volumetric weight calculation, GST-compliant invoicing, instant payment receipts, and nationwide tracking for franchise hubs and corporate clients.
                    </p>

                    <div style="display:flex; gap:16px; flex-wrap:wrap;">
                        <a href="branchpanel/" class="nav-btn nav-btn-primary" style="padding:12px 24px; font-size:15px;">
                            <i class="fa fa-arrow-right"></i> Open Branch Operations
                        </a>
                        <a href="adminpanel/" class="nav-btn nav-btn-outline" style="padding:12px 24px; font-size:15px;">
                            <i class="fa fa-cog"></i> Administrator Settings
                        </a>
                    </div>
                </div>

                <!-- Interactive Tabbed Widget -->
                <div class="col-md-5 col-xs-12" id="track">
                    <div class="portal-widget-box">
                        <div class="portal-tab-header">
                            <button type="button" class="portal-tab-btn active" id="tabBtnTrack" onclick="switchPortalTab('track')">
                                <i class="fa fa-search"></i> Track AWB
                            </button>
                            <button type="button" class="portal-tab-btn" id="tabBtnBranch" onclick="switchPortalTab('branch')">
                                <i class="fa fa-truck"></i> Branch Login
                            </button>
                            <button type="button" class="portal-tab-btn" id="tabBtnAdmin" onclick="switchPortalTab('admin')">
                                <i class="fa fa-lock"></i> Admin Login
                            </button>
                        </div>

                        <div class="portal-tab-content">
                            <!-- TAB 1: TRACK AWB -->
                            <div class="tab-pane-portal active" id="paneTrack">
                                <form method="GET" action="index.php#track">
                                    <div class="widget-form-group">
                                        <label>Consignment / AWB Tracking Number</label>
                                        <input type="text" name="awb" class="widget-input" placeholder="e.g. 146780671" value="<?php echo htmlspecialchars($searchedAWB); ?>" required autofocus>
                                    </div>
                                    <button type="submit" class="widget-submit-btn">
                                        <i class="fa fa-location-arrow"></i> Track Consignment
                                    </button>
                                </form>

                                <?php if (!empty($searchedAWB)) { ?>
                                    <div style="margin-top:20px;">
                                        <?php if (!empty($trackResult) && isset($trackResult[1])) { 
                                            $t = $trackResult[1]; ?>
                                            <div style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:10px; padding:16px; font-size:13px;">
                                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                                    <strong style="color:#0f172a; font-size:14px;">AWB #<?php echo htmlspecialchars($t['Consignment_No']); ?></strong>
                                                    <span class="badge-pill-modern badge-success">BOOKED / IN TRANSIT</span>
                                                </div>
                                                <div style="margin-bottom:5px;"><strong>Booking Date:</strong> <?php echo $t['BookingDate']; ?></div>
                                                <div style="margin-bottom:5px;"><strong>Origin Branch:</strong> <?php echo htmlspecialchars($t['Branch_Name']); ?></div>
                                                <div style="margin-bottom:5px;"><strong>Destination:</strong> <?php echo htmlspecialchars($t['Destination_Name']); ?> (<?php echo htmlspecialchars($t['State_Name']); ?>)</div>
                                                <div style="margin-bottom:5px;"><strong>Consignee:</strong> <?php echo htmlspecialchars(!empty($t['Consignee_Name']) ? $t['Consignee_Name'] : 'Registered Customer'); ?></div>
                                                <div style="margin-top:8px; padding-top:8px; border-top:1px solid #e2e8f0; color:#2563eb; font-weight:700;">
                                                    <i class="fa fa-truck"></i> Mode: <?php echo ($t['Send_By'] == 1) ? 'Surface Cargo' : (($t['Send_By'] == 2) ? 'Air Express' : 'Urgent'); ?> | Weight: <?php echo $t['Total_Weight_In_KG']; ?> KG
                                                </div>
                                            </div>
                                        <?php } else { ?>
                                            <div class="alert alert-danger" style="margin-bottom:0; font-size:13px; border-radius:8px;">
                                                <i class="fa fa-exclamation-circle"></i> No record found for AWB #<?php echo htmlspecialchars($searchedAWB); ?>. Please check the consignment number.
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>

                            <!-- TAB 2: BRANCH LOGIN -->
                            <div class="tab-pane-portal" id="paneBranch">
                                <form id="homeBranchLoginForm">
                                    <div class="widget-form-group">
                                        <label>Registered Branch Email</label>
                                        <input type="email" id="homeBUser" class="widget-input" placeholder="e.g. raipur@keshriexpress.com" required>
                                    </div>
                                    <div class="widget-form-group">
                                        <label>Branch Password</label>
                                        <input type="password" id="homeBPass" class="widget-input" placeholder="Enter branch password" required>
                                    </div>
                                    <button type="button" id="homeBLoginBtn" class="widget-submit-btn">
                                        <i class="fa fa-sign-in"></i> Sign In to Branch Panel
                                    </button>
                                    <div id="homeBMsg" class="alert alert-danger" style="display:none; margin-top:14px; font-size:13px; padding:10px; border-radius:8px;"></div>
                                </form>
                            </div>

                            <!-- TAB 3: ADMIN LOGIN -->
                            <div class="tab-pane-portal" id="paneAdmin">
                                <form id="homeAdminLoginForm">
                                    <div class="widget-form-group">
                                        <label>Administrator Username</label>
                                        <input type="text" id="homeAUser" class="widget-input" placeholder="Enter admin username" required>
                                    </div>
                                    <div class="widget-form-group">
                                        <label>Admin Password</label>
                                        <input type="password" id="homeAPass" class="widget-input" placeholder="Enter admin password" required>
                                    </div>
                                    <button type="button" id="homeALoginBtn" class="widget-submit-btn widget-submit-btn-dark">
                                        <i class="fa fa-lock"></i> Sign In to Admin Console
                                    </button>
                                    <div id="homeAMsg" class="alert alert-danger" style="display:none; margin-top:14px; font-size:13px; padding:10px; border-radius:8px;"></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Operational Statistics Bar -->
    <section class="stats-section">
        <div class="stats-card-container">
            <div class="stat-item">
                <div class="stat-number"><?php echo number_format($totCount); ?>+</div>
                <div class="stat-label">Total Shipments Handled</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" style="color:#2563eb;"><?php echo $totBranches; ?>+</div>
                <div class="stat-label">Franchise & Hub Branches</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" style="color:#059669;"><?php echo number_format($totDest); ?>+</div>
                <div class="stat-label">Destinations & Pin Codes</div>
            </div>
        </div>
    </section>

    <!-- ERP Features Grid -->
    <section class="services-section">
        <div class="section-header">
            <h2 class="section-title">Enterprise Express Logistics Platform</h2>
            <div class="section-subtitle">Automated volumetric rating, GST invoices, digital receipts, and real-time ledger accounting.</div>
        </div>

        <div class="service-grid">
            <div class="service-card">
                <div class="service-icon-wrap"><i class="fa fa-cube"></i></div>
                <h3 class="service-card-title">Volumetric Weight Engine</h3>
                <p class="service-card-desc">Automatic $\frac{L \times W \times H}{5000}$ calculation and higher chargeable weight comparison during booking.</p>
            </div>

            <div class="service-card">
                <div class="service-icon-wrap"><i class="fa fa-map-marker"></i></div>
                <h3 class="service-card-title">Automated ODA Detection</h3>
                <p class="service-card-desc">Out of Delivery Area destination identification with visual badges and automated surcharge tariffs.</p>
            </div>

            <div class="service-card">
                <div class="service-icon-wrap"><i class="fa fa-file-text-o"></i></div>
                <h3 class="service-card-title">GST Tax Invoices & Reports</h3>
                <p class="service-card-desc">Itemized GST billing, CGST/SGST/IGST tax breakdowns, HSN 996812 compliance, and downloadable invoice PDFs.</p>
            </div>

            <div class="service-card">
                <div class="service-icon-wrap"><i class="fa fa-credit-card"></i></div>
                <h3 class="service-card-title">Payment Receipts & Ledgers</h3>
                <p class="service-card-desc">Auto-generated sequential payment receipts, balance settlement records, and printable receipt slips.</p>
            </div>

            <div class="service-card">
                <div class="service-icon-wrap"><i class="fa fa-envelope-o"></i></div>
                <h3 class="service-card-title">Automated SMTP Emailing</h3>
                <p class="service-card-desc">Instant customer notification dispatch with invoices and payment receipts sent directly to client inboxes.</p>
            </div>

            <div class="service-card">
                <div class="service-icon-wrap"><i class="fa fa-shield"></i></div>
                <h3 class="service-card-title">Transit Insurance Protection</h3>
                <p class="service-card-desc">Declared value risk assessment with automatic 2% transit insurance calculations and policy tracking.</p>
            </div>
        </div>
    </section>

    <!-- Quick Access Banners -->
    <section class="portals-banner-section">
        <div class="portals-banner-container">
            <div class="portal-quick-card">
                <div style="font-size:28px; color:#38bdf8; margin-bottom:12px;"><i class="fa fa-truck"></i></div>
                <h3 style="font-size:22px; font-weight:800; margin:0 0 10px 0;">Branch Operations Panel</h3>
                <p style="color:#94a3b8; font-size:14px; line-height:1.6; margin-bottom:20px;">
                    Consignment booking, client rate tariff management, AWB label generation, payment collection, and daily dispatch reports.
                </p>
                <a href="branchpanel/" class="nav-btn nav-btn-primary"><i class="fa fa-sign-in"></i> Launch Branch Panel</a>
            </div>

            <div class="portal-quick-card">
                <div style="font-size:28px; color:#818cf8; margin-bottom:12px;"><i class="fa fa-lock"></i></div>
                <h3 style="font-size:22px; font-weight:800; margin:0 0 10px 0;">Admin Master ERP</h3>
                <p style="color:#94a3b8; font-size:14px; line-height:1.6; margin-bottom:20px;">
                    Network branch management, operational shipping zones, GST tax configuration, ODA masters, and company-wide financial reports.
                </p>
                <a href="adminpanel/" class="nav-btn nav-btn-outline" style="border-color:#818cf8; color:#818cf8;"><i class="fa fa-cog"></i> Launch Admin ERP</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-container">
            <div>
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
                    <div style="width:32px; height:32px; background:#2563eb; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:16px;"><i class="fa fa-cubes"></i></div>
                    <strong style="color:#ffffff; font-size:18px;">KESHRI EXPRESS LOGISTICS</strong>
                </div>
                <p style="max-width:440px; line-height:1.6; margin-bottom:14px;">
                    Fast, secure, and reliable nationwide express courier, full truckload cargo, and digital ERP supply-chain management.
                </p>
                <div><strong>Corporate Hub:</strong> Thakur Building, Opp Wallfort City Gate, Ring Road No-1, Kushalpur, Raipur-492013</div>
            </div>

            <div>
                <h4 style="color:#ffffff; font-size:15px; font-weight:700; margin-top:0; margin-bottom:16px;">Quick Links</h4>
                <ul style="list-style:none; padding:0; margin:0; line-height:2;">
                    <li><a href="branchpanel/" style="color:#94a3b8; text-decoration:none;"><i class="fa fa-angle-right"></i> Branch Operations</a></li>
                    <li><a href="adminpanel/" style="color:#94a3b8; text-decoration:none;"><i class="fa fa-angle-right"></i> Admin Console</a></li>
                    <li><a href="#track" onclick="switchPortalTab('track')" style="color:#94a3b8; text-decoration:none;"><i class="fa fa-angle-right"></i> Track Consignment</a></li>
                    <li><a href="branchpanel/changepwd/" style="color:#94a3b8; text-decoration:none;"><i class="fa fa-angle-right"></i> Change Password</a></li>
                </ul>
            </div>

            <div>
                <h4 style="color:#ffffff; font-size:15px; font-weight:700; margin-top:0; margin-bottom:16px;">System Information</h4>
                <div style="line-height:1.9;">
                    <div><strong>Domain:</strong> <a href="https://ciggymart.shop" style="color:#38bdf8; text-decoration:none;">https://ciggymart.shop</a></div>
                    <div><strong>GSTIN:</strong> 22AAGCK2836D1ZY</div>
                    <div><strong>Support:</strong> support@ciggymart.shop</div>
                    <div><strong>Helpline:</strong> 0771-4004544</div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div>&copy; <?php echo date('Y'); ?> Keshri Express Logistics Private Limited. All Rights Reserved.</div>
            <div>Built with Modern Enterprise ERP Architecture</div>
        </div>
    </footer>

    <!-- Interactive JavaScript -->
    <script>
        function switchPortalTab(tab) {
            $('.portal-tab-btn').removeClass('active');
            $('.tab-pane-portal').removeClass('active');

            if (tab === 'track') {
                $('#tabBtnTrack').addClass('active');
                $('#paneTrack').addClass('active');
            } else if (tab === 'branch') {
                $('#tabBtnBranch').addClass('active');
                $('#paneBranch').addClass('active');
            } else if (tab === 'admin') {
                $('#tabBtnAdmin').addClass('active');
                $('#paneAdmin').addClass('active');
            }
        }

        $(document).ready(function() {
            // Branch Direct Ajax Login from Home Widget
            $('#homeBLoginBtn').click(function() {
                var btn = $(this);
                var email = $('#homeBUser').val().trim();
                var password = $('#homeBPass').val().trim();
                $('#homeBMsg').hide().text('');

                if (!email || !password) {
                    $('#homeBMsg').html('<i class="fa fa-exclamation-circle"></i> Please enter email and password.').slideDown();
                    return;
                }

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Signing In...');

                $.ajax({
                    url: 'branchpanel/check_branch_login.php',
                    type: 'POST',
                    data: { email: email, password: password },
                    success: function(data) {
                        if (data.trim() === 'true') {
                            btn.html('<i class="fa fa-check"></i> Redirecting...');
                            window.location.href = 'branchpanel/home.php';
                        } else {
                            btn.prop('disabled', false).html('<i class="fa fa-sign-in"></i> Sign In to Branch Panel');
                            $('#homeBMsg').html('<i class="fa fa-times-circle"></i> Invalid Branch Email or Password.').slideDown();
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html('<i class="fa fa-sign-in"></i> Sign In to Branch Panel');
                        $('#homeBMsg').html('<i class="fa fa-exclamation-triangle"></i> Network error. Please try again.').slideDown();
                    }
                });
            });

            // Admin Direct Ajax Login from Home Widget
            $('#homeALoginBtn').click(function() {
                var btn = $(this);
                var user = $('#homeAUser').val().trim();
                var password = $('#homeAPass').val().trim();
                $('#homeAMsg').hide().text('');

                if (!user || !password) {
                    $('#homeAMsg').html('<i class="fa fa-exclamation-circle"></i> Please enter username and password.').slideDown();
                    return;
                }

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Authenticating...');

                $.ajax({
                    url: 'adminpanel/check_login.php',
                    type: 'POST',
                    data: { user: user, password: password },
                    success: function(data) {
                        if (data.trim() === 'true') {
                            btn.html('<i class="fa fa-check"></i> Redirecting...');
                            window.location.href = 'adminpanel/home.php';
                        } else {
                            btn.prop('disabled', false).html('<i class="fa fa-lock"></i> Sign In to Admin Console');
                            $('#homeAMsg').html('<i class="fa fa-times-circle"></i> Invalid Admin Username or Password.').slideDown();
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html('<i class="fa fa-lock"></i> Sign In to Admin Console');
                        $('#homeAMsg').html('<i class="fa fa-exclamation-triangle"></i> Network error. Please try again.').slideDown();
                    }
                });
            });
        });
    </script>
</body>
</html>