<?php 
@require_once('config.php');
if (file_exists(dirname(__FILE__).'/libraries/classes/DBConn.php')) {
    require_once(dirname(__FILE__).'/libraries/classes/DBConn.php');
}

$db = null;
$trackResult = null;
$searchedAWB = '';
$totCount = 154200;
$totBranches = 52;
$totDistricts = 33;
$totVehicles = 220;

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
    }
} catch (Exception $e) {
    // Graceful fallback for initial DB setup
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keshri Express - Logistics & Shipping Solution</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: '#2563EB',
                        secondary: '#1E40AF',
                        accent: '#38BDF8'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased selection:bg-primary selection:text-white">

    <!-- Topbar -->
    <div class="bg-slate-900 text-slate-300 py-2 text-xs font-medium">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <div class="flex space-x-6">
                <span><i class="fa-solid fa-envelope text-accent mr-2"></i> info@keshriexpress.com</span>
                <span><i class="fa-solid fa-phone text-accent mr-2"></i> +91 98765 43210</span>
            </div>
            <div class="flex space-x-4">
                <a href="#" class="hover:text-white transition"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" class="hover:text-white transition"><i class="fa-brands fa-twitter"></i></a>
                <a href="#" class="hover:text-white transition"><i class="fa-brands fa-linkedin-in"></i></a>
            </div>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <!-- Logo -->
                <div class="flex items-center space-x-3 cursor-pointer">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary to-secondary rounded-lg flex items-center justify-center text-white font-bold text-xl shadow-lg">K</div>
                    <div class="flex flex-col">
                        <span class="text-xl font-bold tracking-tight text-slate-900">Keshri<span class="text-primary">Express</span></span>
                        <span class="text-[10px] uppercase font-semibold text-slate-500 tracking-wider">Logistics Solutions</span>
                    </div>
                </div>

                <!-- Mobile menu toggle (hidden from md up) -->
                <button id="navToggle" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="mobileMenu"
                        class="md:hidden inline-flex items-center justify-center w-11 h-11 rounded-lg text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-primary/40 transition">
                    <i id="navToggleIcon" class="fa-solid fa-bars text-xl"></i>
                </button>

                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-8 items-center">
                    <a href="#" class="text-slate-600 hover:text-primary font-medium transition">Home</a>
                    <a href="#services" class="text-slate-600 hover:text-primary font-medium transition">Services</a>
                    <a href="#tracking" class="text-slate-600 hover:text-primary font-medium transition">Track Order</a>
                    <div class="flex space-x-3 ml-4">
                        <button class="bg-white border-2 border-slate-200 text-slate-700 px-5 py-2 rounded-lg font-medium hover:border-primary hover:text-primary transition" onclick="$('#modalBranch').removeClass('hidden')">Branch Login</button>
                        <button class="bg-primary text-white px-5 py-2 rounded-lg font-medium shadow-md shadow-primary/30 hover:bg-secondary transition" onclick="$('#modalAdmin').removeClass('hidden')">Admin Login</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Panel -->
        <div id="mobileMenu" class="hidden md:hidden border-t border-slate-100 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 space-y-1">
                <a href="#" class="block px-3 py-3 rounded-lg text-slate-700 hover:bg-slate-50 hover:text-primary font-medium transition">Home</a>
                <a href="#services" class="block px-3 py-3 rounded-lg text-slate-700 hover:bg-slate-50 hover:text-primary font-medium transition">Services</a>
                <a href="#tracking" class="block px-3 py-3 rounded-lg text-slate-700 hover:bg-slate-50 hover:text-primary font-medium transition">Track Order</a>
                <div class="pt-3 mt-2 border-t border-slate-100 space-y-3">
                    <button class="w-full bg-white border-2 border-slate-200 text-slate-700 px-5 py-3 rounded-lg font-medium hover:border-primary hover:text-primary transition" onclick="$('#modalBranch').removeClass('hidden')">Branch Login</button>
                    <button class="w-full bg-primary text-white px-5 py-3 rounded-lg font-medium shadow-md shadow-primary/30 hover:bg-secondary transition" onclick="$('#modalAdmin').removeClass('hidden')">Admin Login</button>
                </div>
            </div>
        </div>
    </nav>

    <script>
    (function () {
        var btn  = document.getElementById('navToggle');
        var menu = document.getElementById('mobileMenu');
        var icon = document.getElementById('navToggleIcon');
        if (!btn || !menu) { return; }

        function setOpen(open) {
            menu.classList.toggle('hidden', !open);
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (icon) {
                icon.classList.toggle('fa-bars', !open);
                icon.classList.toggle('fa-xmark', open);
            }
        }

        btn.addEventListener('click', function () {
            setOpen(menu.classList.contains('hidden'));
        });

        // Close after tapping a link or a login button.
        menu.addEventListener('click', function (e) {
            if (e.target.closest('a, button')) { setOpen(false); }
        });

        // Reset when resizing up to the desktop breakpoint.
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 768) { setOpen(false); }
        });
    })();
    </script>

    <!-- Hero Section -->
    <section class="relative bg-white overflow-hidden py-16 lg:py-24">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-primary/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-72 h-72 bg-accent/10 rounded-full blur-3xl"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                
                <!-- Left Content -->
                <div>
                    <div class="inline-flex items-center bg-blue-50 text-primary px-3 py-1 rounded-full text-sm font-semibold mb-6">
                        <span class="w-2 h-2 bg-primary rounded-full mr-2 animate-pulse"></span>
                        India's Leading Logistics Platform
                    </div>
                    <h1 class="text-4xl lg:text-6xl font-bold text-slate-900 leading-tight mb-6">
                        Streamline your <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-accent">Supply Chain</span> today.
                    </h1>
                    <p class="text-lg text-slate-600 mb-8 max-w-lg leading-relaxed">
                        Manage orders, track shipments, and optimize your entire logistics workflow with our real-time tracking platform.
                    </p>

                    <!-- Tracking Box -->
                    <div id="tracking" class="bg-white p-2 rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.08)] border border-slate-100 flex items-center mb-8 max-w-xl">
                        <form method="GET" action="" class="w-full flex">
                            <div class="flex-grow flex items-center pl-4">
                                <i class="fa-solid fa-box-open text-slate-400 mr-3"></i>
                                <input type="text" name="awb" placeholder="Enter AWB or Order Number..." class="w-full bg-transparent border-none outline-none text-slate-700 font-medium placeholder:font-normal placeholder:text-slate-400" required>
                            </div>
                            <button type="submit" class="bg-primary hover:bg-secondary text-white px-8 py-3 rounded-lg font-semibold transition shadow-md shadow-primary/20 whitespace-nowrap ml-2">
                                Track Now <i class="fa-solid fa-arrow-right ml-2 text-sm"></i>
                            </button>
                        </form>
                    </div>

                    <div class="flex items-center space-x-4 text-sm text-slate-500 font-medium">
                        <p>Trusted partners:</p>
                        <div class="flex space-x-3 grayscale opacity-60">
                            <i class="fa-brands fa-amazon text-2xl hover:grayscale-0 hover:opacity-100 transition"></i>
                            <i class="fa-brands fa-dhl text-2xl hover:grayscale-0 hover:opacity-100 transition"></i>
                            <i class="fa-brands fa-fedex text-2xl hover:grayscale-0 hover:opacity-100 transition"></i>
                        </div>
                    </div>
                </div>

                <!-- Right Image / Tracking Result -->
                <div class="relative">
                    <?php if(!empty($searchedAWB)): ?>
                        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8 transform transition hover:-translate-y-1">
                            <h3 class="text-2xl font-bold text-slate-900 mb-6 flex items-center"><i class="fa-solid fa-location-crosshairs text-primary mr-3"></i> Tracking Details</h3>
                            <?php if(!empty($trackResult)): $r = $trackResult[1]; ?>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 flex items-start">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex justify-center items-center text-green-600 mr-4 shrink-0"><i class="fa-solid fa-check"></i></div>
                                    <div>
                                        <h4 class="text-green-800 font-bold text-lg">Shipment Found!</h4>
                                        <p class="text-green-700 text-sm">AWB: <span class="font-bold"><?php echo htmlspecialchars($searchedAWB); ?></span></p>
                                    </div>
                                </div>
                                
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center py-3 border-b border-slate-100">
                                        <span class="text-slate-500 text-sm"><i class="fa-regular fa-calendar mr-2"></i>Booking Date</span>
                                        <span class="text-slate-800 font-semibold"><?php echo $r['BookingDate']; ?></span>
                                    </div>
                                    <div class="flex justify-between items-center py-3 border-b border-slate-100">
                                        <span class="text-slate-500 text-sm"><i class="fa-solid fa-map-pin mr-2"></i>Destination</span>
                                        <span class="text-slate-800 font-semibold"><?php echo $r['Destination_Name']; ?> (<?php echo $r['State_Name']; ?>)</span>
                                    </div>
                                    <div class="flex justify-between items-center py-3 border-b border-slate-100">
                                        <span class="text-slate-500 text-sm"><i class="fa-solid fa-building mr-2"></i>Booking Branch</span>
                                        <span class="text-slate-800 font-semibold"><?php echo $r['Branch_Name']; ?></span>
                                    </div>
                                    <div class="flex justify-between items-center py-3">
                                        <span class="text-slate-500 text-sm"><i class="fa-solid fa-user mr-2"></i>Client</span>
                                        <span class="text-slate-800 font-semibold"><?php echo $r['Client_Name'] ? $r['Client_Name'] : 'Retail'; ?></span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                                    <div class="w-16 h-16 bg-red-100 rounded-full flex justify-center items-center text-red-500 mx-auto mb-4 text-2xl"><i class="fa-solid fa-xmark"></i></div>
                                    <h4 class="text-red-800 font-bold text-xl mb-2">No Record Found</h4>
                                    <p class="text-red-700 text-sm">We couldn't find any shipment matching AWB: <span class="font-bold"><?php echo htmlspecialchars($searchedAWB); ?></span></p>
                                    <a href="index.php" class="inline-block mt-4 bg-white border border-red-200 text-red-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-50 transition">Clear Search</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <!-- Decorative Hero Image -->
                        <div class="relative bg-slate-100 rounded-2xl overflow-hidden shadow-2xl aspect-square md:aspect-auto md:h-[500px]">
                            <img src="https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?auto=format&fit=crop&w=1000&q=80" alt="Logistics Warehouse" class="w-full h-full object-cover opacity-90">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 to-transparent"></div>
                            
                            <!-- Floating Stat Card -->
                            <div class="absolute bottom-8 left-8 right-8 bg-white/10 backdrop-blur-md border border-white/20 p-5 rounded-xl flex items-center shadow-lg">
                                <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center text-white text-xl mr-4 shrink-0 shadow-lg"><i class="fa-solid fa-truck-fast"></i></div>
                                <div class="text-white">
                                    <div class="text-2xl font-bold"><?php echo number_format($totCount); ?>+</div>
                                    <div class="text-white/80 text-sm font-medium">Successful Deliveries</div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-12 bg-white border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 divide-x divide-slate-100">
                <div class="text-center px-4">
                    <div class="text-3xl font-bold text-slate-900 mb-1"><?php echo number_format($totCount); ?></div>
                    <div class="text-sm font-medium text-slate-500 uppercase tracking-wide">Total Bookings</div>
                </div>
                <div class="text-center px-4">
                    <div class="text-3xl font-bold text-slate-900 mb-1"><?php echo $totBranches; ?></div>
                    <div class="text-sm font-medium text-slate-500 uppercase tracking-wide">Branches</div>
                </div>
                <div class="text-center px-4">
                    <div class="text-3xl font-bold text-slate-900 mb-1"><?php echo $totDistricts; ?></div>
                    <div class="text-sm font-medium text-slate-500 uppercase tracking-wide">Districts Covered</div>
                </div>
                <div class="text-center px-4">
                    <div class="text-3xl font-bold text-slate-900 mb-1"><?php echo $totVehicles; ?></div>
                    <div class="text-sm font-medium text-slate-500 uppercase tracking-wide">Fleet Vehicles</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-slate-900 mb-4">Comprehensive <span class="text-primary">Logistics</span> Services</h2>
            <p class="text-slate-600 max-w-2xl mx-auto mb-16">From small parcels to full truckloads, our network spans across every district ensuring safe and timely delivery.</p>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Service 1 -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 text-left group">
                    <div class="w-14 h-14 bg-blue-50 text-primary rounded-xl flex items-center justify-center text-2xl mb-6 group-hover:bg-primary group-hover:text-white transition-colors">
                        <i class="fa-solid fa-box"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">B2B Parcel Services</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-4">Dedicated parcel delivery for businesses across the state with end-to-end tracking.</p>
                    <a href="#" class="text-primary font-semibold text-sm inline-flex items-center hover:text-secondary">Learn more <i class="fa-solid fa-arrow-right ml-1"></i></a>
                </div>

                <!-- Service 2 -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 text-left group">
                    <div class="w-14 h-14 bg-blue-50 text-primary rounded-xl flex items-center justify-center text-2xl mb-6 group-hover:bg-primary group-hover:text-white transition-colors">
                        <i class="fa-solid fa-truck"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Full Truck Load (FTL)</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-4">Secure and direct transportation for bulk goods requiring dedicated vehicles.</p>
                    <a href="#" class="text-primary font-semibold text-sm inline-flex items-center hover:text-secondary">Learn more <i class="fa-solid fa-arrow-right ml-1"></i></a>
                </div>

                <!-- Service 3 -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 text-left group">
                    <div class="w-14 h-14 bg-blue-50 text-primary rounded-xl flex items-center justify-center text-2xl mb-6 group-hover:bg-primary group-hover:text-white transition-colors">
                        <i class="fa-solid fa-warehouse"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Warehousing Solutions</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-4">Strategic warehousing and storage facilities to optimize your inventory distribution.</p>
                    <a href="#" class="text-primary font-semibold text-sm inline-flex items-center hover:text-secondary">Learn more <i class="fa-solid fa-arrow-right ml-1"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid md:grid-cols-4 gap-8">
            <div class="col-span-2">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-8 h-8 bg-gradient-to-br from-primary to-secondary rounded flex items-center justify-center text-white font-bold shadow">K</div>
                    <span class="text-lg font-bold text-white">KeshriExpress</span>
                </div>
                <p class="text-sm max-w-sm mb-6">India's leading logistics and shipping solution. Manage orders, track shipments, and optimize your supply chain.</p>
                <div class="flex space-x-4">
                    <a href="#" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-primary hover:text-white transition"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-primary hover:text-white transition"><i class="fa-brands fa-twitter"></i></a>
                </div>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-4">Company</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-primary transition">About Us</a></li>
                    <li><a href="#" class="hover:text-primary transition">Careers</a></li>
                    <li><a href="#" class="hover:text-primary transition">Privacy Policy</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-4">Support</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-primary transition">Contact</a></li>
                    <li><a href="#" class="hover:text-primary transition">Help Center</a></li>
                    <li><a href="#" class="hover:text-primary transition">Track Order</a></li>
                </ul>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 pt-8 border-t border-slate-800 text-center text-sm">
            &copy; 2026 Keshri Express. All rights reserved. Redesigned to perfection.
        </div>
    </footer>

    <!-- Branch Login Modal -->
    <div id="modalBranch" class="fixed inset-0 z-[100] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden relative">
            <button onclick="$('#modalBranch').addClass('hidden')" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-500 transition"><i class="fa-solid fa-xmark"></i></button>
            <div class="p-8">
                <div class="w-12 h-12 bg-blue-50 text-primary rounded-xl flex items-center justify-center text-xl mb-4"><i class="fa-solid fa-store"></i></div>
                <h3 class="text-2xl font-bold text-slate-900 mb-1">Branch Portal</h3>
                <p class="text-slate-500 text-sm mb-6">Sign in to manage your branch operations.</p>
                
                <div id="modalBMsg" class="hidden rounded-lg p-3 text-sm font-medium mb-4"></div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                        <input type="email" id="modalBUser" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition" placeholder="branch@keshri.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                        <input type="password" id="modalBPass" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition" placeholder="••••••••">
                    </div>
                    <button id="modalBLoginBtn" class="w-full bg-primary hover:bg-secondary text-white font-semibold py-3 rounded-lg shadow-md shadow-primary/20 transition flex items-center justify-center">
                        Sign In <i class="fa-solid fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Login Modal -->
    <div id="modalAdmin" class="fixed inset-0 z-[100] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden relative">
            <button onclick="$('#modalAdmin').addClass('hidden')" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-500 transition"><i class="fa-solid fa-xmark"></i></button>
            <div class="p-8">
                <div class="w-12 h-12 bg-slate-900 text-white rounded-xl flex items-center justify-center text-xl mb-4"><i class="fa-solid fa-shield-halved"></i></div>
                <h3 class="text-2xl font-bold text-slate-900 mb-1">Admin Console</h3>
                <p class="text-slate-500 text-sm mb-6">Restricted access for system administrators.</p>
                
                <div id="modalAMsg" class="hidden rounded-lg p-3 text-sm font-medium mb-4"></div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                        <input type="text" id="modalAUser" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition" placeholder="admin">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                        <input type="password" id="modalAPass" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition" placeholder="••••••••">
                    </div>
                    <button id="modalALoginBtn" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold py-3 rounded-lg shadow-md transition flex items-center justify-center">
                        Secure Sign In <i class="fa-solid fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Scripts -->
    <script>
        $(document).ready(function() {
            // Branch Ajax Authentication
            $('#modalBLoginBtn').click(function() {
                var btn = $(this);
                var user = $('#modalBUser').val().trim();
                var password = $('#modalBPass').val().trim();
                var msg = $('#modalBMsg');
                msg.hide();

                if (!user || !password) {
                    msg.css({'background':'#fee2e2','color':'#991b1b'}).html('<i class="fa-solid fa-circle-exclamation mr-2"></i> Please enter email and password.').slideDown();
                    return;
                }
                btn.prop('disabled', true).html('<i class="fa-solid fa-circle-notch fa-spin"></i>');

                $.ajax({
                    url: 'branchpanel/check_login.php',
                    type: 'POST',
                    data: { user: user, password: password },
                    success: function(data) {
                        if (data.trim() === 'true') {
                            btn.html('<i class="fa-solid fa-check"></i>');
                            window.location.href = 'branchpanel/home.php';
                        } else {
                            btn.prop('disabled', false).html('Sign In <i class="fa-solid fa-arrow-right ml-2"></i>');
                            msg.css({'background':'#fee2e2','color':'#991b1b'}).html('<i class="fa-solid fa-circle-xmark mr-2"></i> Invalid Branch Email or Password.').slideDown();
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html('Sign In <i class="fa-solid fa-arrow-right ml-2"></i>');
                        msg.css({'background':'#fee2e2','color':'#991b1b'}).html('<i class="fa-solid fa-triangle-exclamation mr-2"></i> Network error. Please try again.').slideDown();
                    }
                });
            });

            // Admin Ajax Authentication
            $('#modalALoginBtn').click(function() {
                var btn = $(this);
                var user = $('#modalAUser').val().trim();
                var password = $('#modalAPass').val().trim();
                var msg = $('#modalAMsg');
                msg.hide();

                if (!user || !password) {
                    msg.css({'background':'#fee2e2','color':'#991b1b'}).html('<i class="fa-solid fa-circle-exclamation mr-2"></i> Please enter username and password.').slideDown();
                    return;
                }
                btn.prop('disabled', true).html('<i class="fa-solid fa-circle-notch fa-spin"></i>');

                $.ajax({
                    url: 'adminpanel/check_login.php',
                    type: 'POST',
                    data: { user: user, password: password },
                    success: function(data) {
                        if (data.trim() === 'true') {
                            btn.html('<i class="fa-solid fa-check"></i>');
                            window.location.href = 'adminpanel/home.php';
                        } else {
                            btn.prop('disabled', false).html('Secure Sign In <i class="fa-solid fa-arrow-right ml-2"></i>');
                            msg.css({'background':'#fee2e2','color':'#991b1b'}).html('<i class="fa-solid fa-circle-xmark mr-2"></i> Invalid Admin Username or Password.').slideDown();
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html('Secure Sign In <i class="fa-solid fa-arrow-right ml-2"></i>');
                        msg.css({'background':'#fee2e2','color':'#991b1b'}).html('<i class="fa-solid fa-triangle-exclamation mr-2"></i> Network error. Please try again.').slideDown();
                    }
                });
            });
        });
    </script>
</body>
</html>
