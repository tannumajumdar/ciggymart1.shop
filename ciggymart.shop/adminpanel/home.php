<?php
require('../config.php');
include_once(PATH_ADMIN_INCLUDE.'/header.php');
?>
<div class="erp-dashboard-content">

            <!-- GREETING CARD -->
            <div class="dashboard-welcome-banner">
                <div class="welcome-left">
                    <span class="welcome-wave-icon">&#128075;</span>
                    <div>
                        <h2>Good Morning, Admin</h2>
                        <p>Here's what's happening with your courier business today.</p>
                    </div>
                </div>

                <div class="welcome-slogan-center">
                    Chhattisgarh Se, Har Disha Tak
                </div>

                <div class="welcome-date-badge">
                    <div class="date-str"><?php echo date('l, d M Y'); ?></div>
                    <div class="tricolor-slogan">Faster &bull; Safer &bull; Stronger For Chhattisgarh</div>
                </div>
            </div>

            <!-- 6 KPI METRIC CARDS -->
            <div class="kpi-metric-grid-6">
                <!-- 1. Today's Bookings -->
                <div class="kpi-card">
                    <div class="kpi-icon-pill kpi-icon-blue"><i class="fa fa-cube"></i></div>
                    <div class="kpi-value"><?php echo $todayBookings; ?></div>
                    <div class="kpi-label">Today's Bookings</div>
                    <div class="kpi-delta delta-up"><i class="fa fa-arrow-up"></i> 12%</div>
                </div>

                <!-- 2. In Transit -->
                <div class="kpi-card">
                    <div class="kpi-icon-pill kpi-icon-green"><i class="fa fa-truck"></i></div>
                    <div class="kpi-value"><?php echo $inTransitCount; ?></div>
                    <div class="kpi-label">In Transit</div>
                    <div class="kpi-delta delta-up"><i class="fa fa-arrow-up"></i> 5%</div>
                </div>

                <!-- 3. Delivered Today -->
                <div class="kpi-card">
                    <div class="kpi-icon-pill kpi-icon-purple"><i class="fa fa-check-circle"></i></div>
                    <div class="kpi-value"><?php echo $deliveredCount; ?></div>
                    <div class="kpi-label">Delivered Today</div>
                    <div class="kpi-delta delta-up"><i class="fa fa-arrow-up"></i> 8%</div>
                </div>

                <!-- 4. Today's Billing -->
                <div class="kpi-card">
                    <div class="kpi-icon-pill kpi-icon-orange"><i class="fa fa-file-text-o"></i></div>
                    <div class="kpi-value">&#8377;<?php echo number_format($todayBookingVal); ?></div>
                    <div class="kpi-label">Today's Billing</div>
                    <div class="kpi-delta delta-up"><i class="fa fa-arrow-up"></i> 10%</div>
                </div>

                <!-- 5. Outstanding -->
                <div class="kpi-card">
                    <div class="kpi-icon-pill kpi-icon-red"><i class="fa fa-money"></i></div>
                    <div class="kpi-value">&#8377;<?php echo number_format($outstanding); ?></div>
                    <div class="kpi-label">Outstanding</div>
                    <div class="kpi-delta delta-down"><i class="fa fa-arrow-down"></i> 3%</div>
                </div>

                <!-- 6. Payments Received -->
                <div class="kpi-card">
                    <div class="kpi-icon-pill kpi-icon-teal"><i class="fa fa-inr"></i></div>
                    <div class="kpi-value">&#8377;<?php echo number_format($totPaid); ?></div>
                    <div class="kpi-label">Payments Received</div>
                    <div class="kpi-delta delta-up"><i class="fa fa-arrow-up"></i> 15%</div>
                </div>
            </div>

            <!-- TWO-COLUMN DASHBOARD GRID -->
            <div class="dashboard-columns-grid">

                <!-- LEFT COLUMN -->
                <div>
                    <!-- CHARTS ROW: Booking Trend & Shipment Status -->
                    <div class="chart-row-flex">
                        <!-- Chart 1: Booking Trend (Last 7 Days) -->
                        <div class="dashboard-content-box">
                            <div class="box-header-row">
                                <h3 class="box-title">Booking Trend (Last 7 Days)</h3>
                                <select class="box-filter-select">
                                    <option>Bookings</option>
                                    <option>Revenue (₹)</option>
                                </select>
                            </div>
                            <!-- Vector Trend Line SVG -->
                            <svg class="chart-line-svg" viewBox="0 0 320 140">
                                <defs>
                                    <linearGradient id="lineGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.3"/>
                                        <stop offset="100%" stop-color="#3b82f6" stop-opacity="0.0"/>
                                    </linearGradient>
                                </defs>
                                <path d="M 10,105 L 60,95 L 110,65 L 160,70 L 210,68 L 260,52 L 310,40 L 310,130 L 10,130 Z" fill="url(#lineGrad)"/>
                                <path d="M 10,105 L 60,95 L 110,65 L 160,70 L 210,68 L 260,52 L 310,40" fill="none" stroke="#2563eb" stroke-width="3" stroke-linecap="round"/>
                                <circle cx="10" cy="105" r="4" fill="#2563eb" stroke="#fff" stroke-width="2"/>
                                <circle cx="60" cy="95" r="4" fill="#2563eb" stroke="#fff" stroke-width="2"/>
                                <circle cx="110" cy="65" r="4" fill="#2563eb" stroke="#fff" stroke-width="2"/>
                                <circle cx="160" cy="70" r="4" fill="#2563eb" stroke="#fff" stroke-width="2"/>
                                <circle cx="210" cy="68" r="4" fill="#2563eb" stroke="#fff" stroke-width="2"/>
                                <circle cx="260" cy="52" r="4" fill="#2563eb" stroke="#fff" stroke-width="2"/>
                                <circle cx="310" cy="40" r="4" fill="#2563eb" stroke="#fff" stroke-width="2"/>
                            </svg>
                            <div style="display:flex; justify-content:space-between; font-size:10.5px; color:#94a3b8; margin-top:4px;">
                                <span>10 Sep</span><span>11 Sep</span><span>12 Sep</span><span>13 Sep</span><span>14 Sep</span><span>15 Sep</span><span>16 Sep</span>
                            </div>
                        </div>

                        <!-- Chart 2: Shipment Status Donut -->
                        <div class="dashboard-content-box">
                            <div class="box-header-row">
                                <h3 class="box-title">Shipment Status</h3>
                            </div>
                            <div class="status-donut-wrap">
                                <div class="donut-circle-graphic">
                                    <div class="donut-circle-inner">
                                        <strong><?php echo $totalShipments; ?></strong>
                                        <span>Total</span>
                                    </div>
                                </div>
                                <ul class="donut-legend-list">
                                    <li><span><span class="legend-marker" style="background:#16a34a;"></span>Delivered</span> <strong>45 (14%)</strong></li>
                                    <li><span><span class="legend-marker" style="background:#2563eb;"></span>In Transit</span> <strong>76 (24%)</strong></li>
                                    <li><span><span class="legend-marker" style="background:#eab308;"></span>Pending</span> <strong>120 (38%)</strong></li>
                                    <li><span><span class="legend-marker" style="background:#dc2626;"></span>RTO</span> <strong>22 (7%)</strong></li>
                                    <li><span><span class="legend-marker" style="background:#9333ea;"></span>Picked Up</span> <strong>57 (17%)</strong></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- RECENT CONSIGNMENTS TABLE -->
                    <div class="recent-table-box">
                        <div class="box-header-row">
                            <h3 class="box-title">Recent Consignments</h3>
                            <a href="<?php echo PATH_ADMIN_LINK; ?>/invoice-report.php" style="font-size:12.5px; font-weight:700; color:#2563eb; text-decoration:none;">View All &rarr;</a>
                        </div>
                        <table class="table-custom-erp">
                            <thead>
                                <tr>
                                    <th>AWB No.</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Destination</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sampleConsignments as $row) { ?>
                                    <tr>
                                        <td><strong><?php echo $row['no']; ?></strong></td>
                                        <td><?php echo $row['date']; ?></td>
                                        <td><?php echo $row['client']; ?></td>
                                        <td><?php echo $row['dest']; ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $row['status_class']; ?>">
                                                <i class="fa fa-circle" style="font-size:7px;"></i> <?php echo $row['status']; ?>
                                            </span>
                                        </td>
                                        <td><strong>&#8377;<?php echo $row['amt']; ?></strong></td>
                                        <td>
                                            <a href="<?php echo PATH_ADMIN_LINK; ?>/invoice-report.php" class="btn-action-view">View</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- BOTTOM ROW: Top Destinations & Revenue Overview -->
                    <div class="bottom-split-row">
                        <!-- Top Destinations -->
                        <div class="dashboard-content-box">
                            <div class="box-header-row">
                                <h3 class="box-title">Top Destinations (This Month)</h3>
                            </div>
                            <div class="bars-chart-flex">
                                <div class="bar-col-item">
                                    <div class="bar-value-top">420</div>
                                    <div class="bar-pill" style="height:90px;"></div>
                                    <div class="bar-label-name">Raipur</div>
                                </div>
                                <div class="bar-col-item">
                                    <div class="bar-value-top">310</div>
                                    <div class="bar-pill" style="height:68px;"></div>
                                    <div class="bar-label-name">Bilaspur</div>
                                </div>
                                <div class="bar-col-item">
                                    <div class="bar-value-top">280</div>
                                    <div class="bar-pill" style="height:60px;"></div>
                                    <div class="bar-label-name">Durg</div>
                                </div>
                                <div class="bar-col-item">
                                    <div class="bar-value-top">190</div>
                                    <div class="bar-pill" style="height:42px;"></div>
                                    <div class="bar-label-name">Korba</div>
                                </div>
                                <div class="bar-col-item">
                                    <div class="bar-value-top">160</div>
                                    <div class="bar-pill" style="height:35px;"></div>
                                    <div class="bar-label-name">Jagdalpur</div>
                                </div>
                                <div class="bar-col-item">
                                    <div class="bar-value-top">120</div>
                                    <div class="bar-pill" style="height:26px;"></div>
                                    <div class="bar-label-name">Raigarh</div>
                                </div>
                            </div>
                        </div>

                        <!-- Revenue Overview -->
                        <div class="dashboard-content-box">
                            <div class="box-header-row">
                                <h3 class="box-title">Revenue Overview</h3>
                                <select class="box-filter-select">
                                    <option>This Month</option>
                                    <option>Last Month</option>
                                </select>
                            </div>
                            <div style="font-size:24px; font-weight:800; color:var(--text-dark); margin:12px 0 4px 0;">
                                &#8377;12,45,000 <span style="font-size:12px; color:#16a34a; font-weight:700;"><i class="fa fa-arrow-up"></i> 18%</span>
                            </div>
                            <div style="font-size:12px; color:var(--text-muted); margin-bottom:14px;">Total Revenue across all hubs</div>
                            <div style="font-size:12px; color:#475569; padding-top:10px; border-top:1px solid #f1f5f9;">
                                Last Month: <strong>&#8377;10,54,000</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div>
                    <!-- 4 QUICK ACTIONS -->
                    <div class="quick-actions-2x2">
                        <a href="<?php echo PATH_ADMIN_LINK; ?>/invoice-report.php" class="btn-quick-act btn-act-blue">
                            <i class="fa fa-plus" style="font-size:20px;"></i>
                            <span>New Consignment</span>
                        </a>
                        <a href="<?php echo MASTERS_LINK_CONTROL; ?>/client" class="btn-quick-act btn-act-green">
                            <i class="fa fa-user-plus" style="font-size:20px;"></i>
                            <span>Add Customer</span>
                        </a>
                        <a href="<?php echo PATH_ADMIN_LINK; ?>/invoice-report.php" class="btn-quick-act btn-act-orange">
                            <i class="fa fa-file-text" style="font-size:20px;"></i>
                            <span>Generate Invoice</span>
                        </a>
                        <a href="<?php echo PATH_ADMIN_LINK; ?>/../index.php#tracking-section" class="btn-quick-act btn-act-purple">
                            <i class="fa fa-crosshairs" style="font-size:20px;"></i>
                            <span>Track Shipment</span>
                        </a>
                    </div>

                    <!-- CHHATTISGARH NETWORK MAP WIDGET -->
                    <div class="map-card-side">
                        <div class="box-header-row" style="margin-bottom:12px;">
                            <h3 class="box-title">Chhattisgarh Network</h3>
                        </div>
                        <div class="map-side-container">
                            <div class="map-pin-node" style="top:20px; left:62%;"><div class="pin-dot"></div>Ambikapur</div>
                            <div class="map-pin-node" style="top:60px; left:68%;"><div class="pin-dot"></div>Korba</div>
                            <div class="map-pin-node" style="top:85px; left:78%;"><div class="pin-dot"></div>Raigarh</div>
                            <div class="map-pin-node" style="top:80px; left:48%;"><div class="pin-dot"></div>Bilaspur</div>
                            <div class="map-pin-node" style="top:105px; left:65%;"><div class="pin-dot"></div>Janjgir</div>
                            
                            <!-- Raipur Central Hub -->
                            <div class="pin-hub-raipur">
                                <div class="pin-dot"></div>
                                <span>Raipur</span>
                            </div>

                            <div class="map-pin-node" style="top:140px; left:18%;"><div class="pin-dot"></div>Rajnandgaon</div>
                            <div class="map-pin-node" style="top:165px; left:40%;"><div class="pin-dot"></div>Durg / Bhilai</div>
                            <div class="map-pin-node" style="top:185px; left:55%;"><div class="pin-dot"></div>Dhamtari</div>
                            <div class="map-pin-node" style="top:215px; left:42%;"><div class="pin-dot"></div>Kanker</div>
                            <div class="map-pin-node" style="top:245px; left:48%;"><div class="pin-dot"></div>Jagdalpur</div>

                            <div style="position:absolute; bottom:12px; right:12px; font-family:'Caveat', cursive; font-size:18px; color:#2563eb; text-align:right; line-height:1.1;">
                                Together We Move<br>Chhattisgarh Forward
                            </div>
                        </div>
                    </div>

                    <!-- SIDE PROMO BANNER -->
                    <div class="side-promo-truck-card">
                        <div>
                            <h4>From Chhattisgarh</h4>
                            <span>To A Stronger Tomorrow</span>
                        </div>
                        <i class="fa fa-truck" style="font-size:32px; color:var(--brand-orange);"></i>
                    </div>
                </div>

            </div>

        </div>
<?php include(PATH_ADMIN_INCLUDE.'/footer.php'); ?>
