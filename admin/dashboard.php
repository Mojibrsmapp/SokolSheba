<?php
include_once("includes/configuration.php");

$pending_deposits = 0;
$dep_q = $conn->query("SELECT COUNT(*) AS total FROM deposit_requests WHERE status = 'pending'");
if ($dep_q && $row_dep = $dep_q->fetch_assoc()) {
    $pending_deposits = $row_dep['total'];
}

// Extra Feature: Server Info
$php_version = phpversion();
date_default_timezone_set('Asia/Dhaka');
$server_time = date('h:i A');
?>

<?php include_once("header.php"); ?>

<!-- Chart.js CDN for 7-Days Order Graph -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* Clean Light UI Inspired by Reference Image */
    .sokol-wrapper {
        background-color: #f4f7f6;
        color: #1e293b;
        border-radius: 16px;
        padding: 16px;
        font-family: 'Hind Siliguri', sans-serif;
    }

    .white-panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);
        transition: all 0.25s ease;
    }

    .white-panel:hover {
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
    }

    /* 4 Top Pastel Stat Cards */
    .stat-box-green {
        background: linear-gradient(135deg, #eafaf1 0%, #d5f5e3 100%);
        border: 1px solid #a3e4d7;
        border-radius: 16px;
    }
    .stat-box-blue {
        background: linear-gradient(135deg, #ebf5fb 0%, #d6eaf8 100%);
        border: 1px solid #aed6f1;
        border-radius: 16px;
    }
    .stat-box-orange {
        background: linear-gradient(135deg, #fef9e7 0%, #fcf3cf 100%);
        border: 1px solid #f9e79f;
        border-radius: 16px;
    }
    .stat-box-purple {
        background: linear-gradient(135deg, #f5eef8 0%, #ebdef0 100%);
        border: 1px solid #d7bde2;
        border-radius: 16px;
    }

    /* Vibrant Action Buttons */
    .action-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 16px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 13px;
        color: #ffffff;
        text-decoration: none;
        transition: all 0.2s ease;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    }
    .action-btn:hover {
        transform: translateY(-2px);
        filter: brightness(1.06);
        color: #ffffff;
    }
    .btn-emerald { background: linear-gradient(135deg, #0d7a49 0%, #10b981 100%); }
    .btn-blue    { background: linear-gradient(135deg, #1d4ed8 0%, #0ea5e9 100%); }
    .btn-orange  { background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%); }
    .btn-purple  { background: linear-gradient(135deg, #6d28d9 0%, #8b5cf6 100%); }

    /* Master Grid Tiles */
    .service-tile {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .service-tile:hover {
        background: #ecfdf5;
        border-color: #10b981;
        transform: translateY(-2px);
    }
    .service-tile .icon-circle {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    /* Clean Light Table */
    .clean-table th {
        background-color: #f1f5f9;
        color: #475569;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        padding: 10px 12px;
    }
    .clean-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 12.5px;
    }
    .clean-table tr:hover td {
        background-color: #f8fafc;
    }
    .clean-table tfoot th {
        background-color: #ecfdf5;
        color: #065f46;
        padding: 10px 12px;
        font-size: 12.5px;
    }

    /* Custom Scrollbar */
    .custom-scroll::-webkit-scrollbar { width: 5px; }
    .custom-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 8px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
</style>

<div class="sokol-wrapper space-y-5">

    <!-- Top Welcome Header & Live Date/Time Banner -->
    <div class="white-panel p-4 md:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-l-4 border-l-emerald-600">
        <div>
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-house-chimney text-emerald-700 text-lg"></i>
                <h2 class="text-xl font-black text-slate-800">ড্যাশবোর্ড</h2>
            </div>
            <p class="text-sm font-bold text-slate-700 mt-1">স্বাগতম, Admin! 👋</p>
            <p class="text-xs text-slate-500">সকল সেবার প্রশাসনিক প্যানেলে আপনাকে স্বাগতম।</p>
        </div>

        <div class="flex items-center gap-3 bg-slate-50 border border-slate-200 px-3.5 py-2.5 rounded-xl self-start sm:self-auto">
            <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm">
                <i class="fa-regular fa-calendar-check"></i>
            </div>
            <div class="text-xs">
                <div class="font-bold text-slate-700" id="liveBanglaDate">আজ: লোড হচ্ছে...</div>
                <div class="text-slate-500 font-semibold mt-0.5">সময়: <span id="liveBanglaTime"><?php echo $server_time; ?></span></div>
            </div>
        </div>
    </div>

    <!-- 4 Pastel Stat Cards (Matching Screenshot Style) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        
        <!-- Card 1: Total Users -->
        <div class="stat-box-blue p-4 flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-full bg-blue-500 text-white flex items-center justify-center text-lg shrink-0 shadow-md">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-600 block">মোট গ্রাহক (ইউজার)</span>
                <h3 class="text-2xl font-black text-slate-900 tracking-tight" id="totalUsers">0</h3>
                <span class="text-[11px] font-bold text-emerald-700 block mt-0.5">
                    <i class="fa-solid fa-arrow-trend-up"></i> সক্রিয় নিবন্ধিত একাউন্ট
                </span>
            </div>
        </div>

        <!-- Card 2: Total User Balance -->
        <div class="stat-box-orange p-4 flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-full bg-amber-500 text-white flex items-center justify-center text-lg shrink-0 shadow-md">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-600 block">ইউজার ব্যালেন্স</span>
                <h3 class="text-2xl font-black text-slate-900 tracking-tight">৳ <span id="totalBalance">0.00</span></h3>
                <span class="text-[11px] font-bold text-amber-800 block mt-0.5">
                    <i class="fa-solid fa-coins"></i> ইউজার কারেন্ট ফান্ড
                </span>
            </div>
        </div>

        <!-- Card 3: Zero Balance Users -->
        <div class="stat-box-green p-4 flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-full bg-emerald-600 text-white flex items-center justify-center text-lg shrink-0 shadow-md">
                <i class="fa-solid fa-user-clock"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-600 block">শূন্য ব্যালেন্স</span>
                <h3 class="text-2xl font-black text-slate-900 tracking-tight" id="zeroBalanceUsers">0</h3>
                <span class="text-[11px] font-bold text-rose-600 block mt-0.5">
                    <i class="fa-solid fa-triangle-exclamation"></i> ব্যালেন্সহীন একাউন্ট
                </span>
            </div>
        </div>

        <!-- Card 4: Pending Deposits -->
        <a href="manage_deposits.php" class="stat-box-purple p-4 flex items-center gap-3.5 no-underline hover:opacity-95 transition">
            <div class="w-12 h-12 rounded-full bg-purple-600 text-white flex items-center justify-center text-lg shrink-0 shadow-md">
                <i class="fa-solid fa-money-bill-transfer"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-600 block">পেন্ডিং রিচার্জ</span>
                <h3 class="text-2xl font-black text-slate-900 tracking-tight"><?php echo $pending_deposits; ?> টি</h3>
                <span class="text-[11px] font-bold text-purple-800 block mt-0.5">
                    <i class="fa-solid fa-clock"></i> অপেক্ষমান ডিপোজিট
                </span>
            </div>
        </a>

    </div>

    <!-- Main 12-Column Layout (Left/Center Content + Right Sidebar Widgets) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        <!-- Left & Center Area (8 Columns) -->
        <div class="lg:col-span-8 space-y-5">

            <!-- 7-Days Order Analytics Graph + Quick Filter -->
            <div class="white-panel p-4 md:p-5">
                <div class="flex flex-wrap items-center justify-between gap-2 pb-3 mb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-chart-line text-emerald-600 text-base"></i>
                        <h4 class="text-sm font-extrabold text-slate-800">অর্ডার ও কাজের পরিসংখ্যান গ্রাফ</h4>
                    </div>
                    <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-lg">
                        লাইভ চার্ট ভিউ
                    </span>
                </div>
                <div class="h-[210px] w-full">
                    <canvas id="orderAnalyticsChart"></canvas>
                </div>
            </div>

            <!-- Date-Wise Order & Service Summary Section -->
            <div class="white-panel p-4 md:p-5 space-y-5">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-3 border-b border-slate-200">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></span>
                        <h5 class="font-extrabold text-sm text-slate-800" id="selectedDateTitle">তারিখ অনুযায়ী কাজের হিসাব</h5>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" onclick="setQuickDate(0)" class="text-[11px] font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-lg transition">আজ</button>
                        <button type="button" onclick="setQuickDate(1)" class="text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 px-2.5 py-1 rounded-lg transition">গতকাল</button>
                        <input type="date" id="start_date" name="start_date" class="border border-slate-300 rounded-lg px-2.5 py-1 text-xs font-bold text-slate-700 bg-white outline-none focus:border-emerald-600">
                    </div>
                </div>

                <!-- Table 1: Overall Work Summary -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h6 class="text-xs font-extrabold text-slate-600 flex items-center gap-1.5">
                            <i class="fa-solid fa-layer-group text-emerald-600"></i> কাজের সামগ্রিক সারাংশ
                        </h6>
                        <a href="all_orders_history.php" class="text-[11px] font-bold text-emerald-700 hover:underline">সব অর্ডার দেখুন →</a>
                    </div>
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left clean-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>কাজের ধরন (সেবা)</th>
                                    <th class="text-center">অর্ডার সংখ্যা</th>
                                    <th class="text-right">মোট চার্জ</th>
                                </tr>
                            </thead>
                            <tbody id="historyWorkSummary">
                                <tr><td colspan="4" class="p-4 text-center text-slate-400">ডাটা লোড হচ্ছে...</td></tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2">সর্বমোট হিসাব</th>
                                    <th class="text-center" id="totalHistoryOrders">0 টি</th>
                                    <th class="text-right text-emerald-700 font-black">৳ <span id="totalHistoryPrice">0.00</span></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Table 2: Service Order Status & Counts -->
                <div>
                    <h6 class="text-xs font-extrabold text-slate-600 mb-2 flex items-center gap-1.5">
                        <i class="fa-solid fa-list-check text-blue-600"></i> সার্ভিস অর্ডার স্ট্যাটাস ও কাউন্ট
                    </h6>
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left clean-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>সার্ভিস নাম</th>
                                    <th class="text-center text-amber-600">পেন্ডিং</th>
                                    <th class="text-center text-emerald-600">সফল</th>
                                    <th class="text-center text-rose-600">অন্যান্য</th>
                                </tr>
                            </thead>
                            <tbody id="orderListCountsSummary">
                                <tr><td colspan="5" class="p-4 text-center text-slate-400">ডাটা লোড হচ্ছে...</td></tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-b border-emerald-200">
                                    <th colspan="2">মোট কাউন্ট</th>
                                    <th class="text-center text-amber-700" id="totalPendingCount">0</th>
                                    <th class="text-center text-emerald-700" id="totalSuccessCount">0</th>
                                    <th class="text-center text-rose-700" id="totalOtherCount">0</th>
                                </tr>
                                <tr>
                                    <th colspan="2">মোট চার্জ</th>
                                    <th class="text-center text-amber-700">৳ <span id="totalPendingPrice">0.00</span></th>
                                    <th class="text-center text-emerald-700">৳ <span id="totalSuccessPrice">0.00</span></th>
                                    <th class="text-center text-rose-700">৳ <span id="totalOtherPrice">0.00</span></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- All 12 Master Control Tools -->
            <div class="white-panel p-4 md:p-5">
                <div class="flex items-center justify-between mb-3.5 pb-2.5 border-b border-slate-200">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-grip"></i>
                        </div>
                        <h4 class="text-sm font-extrabold text-slate-800">মাস্টার সার্ভিস ও ম্যানেজমেন্ট কন্ট্রোল</h4>
                    </div>
                    <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded-full">১২ টি মডিউল</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5">
                    <a href="manage_services.php" class="service-tile">
                        <div class="icon-circle bg-rose-100 text-rose-600"><i class="fa-solid fa-power-off"></i></div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block truncate">সার্ভিস সুইচ</span>
                            <span class="text-[10px] text-slate-500 block">অন / অফ</span>
                        </div>
                    </a>

                    <a href="all_pricing.php" class="service-tile">
                        <div class="icon-circle bg-amber-100 text-amber-600"><i class="fa-solid fa-coins"></i></div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block truncate">মাস্টার রেট</span>
                            <span class="text-[10px] text-slate-500 block">প্রাইসিং তালিকা</span>
                        </div>
                    </a>

                    <a href="all_orders_history.php" class="service-tile">
                        <div class="icon-circle bg-sky-100 text-sky-600"><i class="fa-solid fa-boxes-stacked"></i></div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block truncate">সকল অর্ডার</span>
                            <span class="text-[10px] text-slate-500 block">লগ ও ডাটা</span>
                        </div>
                    </a>

                    <a href="manage_deposits.php" class="service-tile">
                        <div class="icon-circle bg-emerald-100 text-emerald-600"><i class="fa-solid fa-money-bill-transfer"></i></div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block truncate">পেন্ডিং ফান্ড</span>
                            <span class="text-[10px] text-slate-500 block">রিচার্জ অনুমোদন</span>
                        </div>
                    </a>

                    <a href="settings.php" class="service-tile">
                        <div class="icon-circle bg-purple-100 text-purple-600"><i class="fa-solid fa-sliders"></i></div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block truncate">সাইট সেটিংস</span>
                            <span class="text-[10px] text-slate-500 block">লিংক ও নম্বর</span>
                        </div>
                    </a>

                    <a href="work_history.php" class="service-tile">
                        <div class="icon-circle bg-indigo-100 text-indigo-600"><i class="fa-solid fa-clock-rotate-left"></i></div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block truncate">কাজের হিস্টোরি</span>
                            <span class="text-[10px] text-slate-500 block">ইউজার সামারি</span>
                        </div>
                    </a>

                    <a href="add_service.php" class="service-tile">
                        <div class="icon-circle bg-teal-100 text-teal-600"><i class="fa-solid fa-circle-plus"></i></div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block truncate">নতুন সার্ভিস</span>
                            <span class="text-[10px] text-slate-500 block">সার্ভিস তৈরি</span>
                        </div>
                    </a>

                    <a href="user_list.php" class="service-tile">
                        <div class="icon-circle bg-cyan-100 text-cyan-600"><i class="fa-solid fa-users-gear"></i></div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block truncate">ইউজার তালিকা</span>
                            <span class="text-[10px] text-slate-500 block">ম্যানেজ একাউন্ট</span>
                        </div>
                    </a>

                    <a href="balance_add_remove.php" class="service-tile">
                        <div class="icon-circle bg-pink-100 text-pink-600"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block truncate">ব্যালেন্স এড/কাট</span>
                            <span class="text-[10px] text-slate-500 block">ইউজার ফান্ড</span>
                        </div>
                    </a>

                    <a href="createLink.php" class="service-tile">
                        <div class="icon-circle bg-blue-100 text-blue-600"><i class="fa-solid fa-link"></i></div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block truncate">ইনভাইট লিংক</span>
                            <span class="text-[10px] text-slate-500 block">রেজিস্ট্রেশন URL</span>
                        </div>
                    </a>

                    <a href="update_marquee.php" class="service-tile">
                        <div class="icon-circle bg-orange-100 text-orange-600"><i class="fa-solid fa-bullhorn"></i></div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block truncate">নোটিশ মডিউল</span>
                            <span class="text-[10px] text-slate-500 block">মারকুই টেক্সট</span>
                        </div>
                    </a>

                    <a href="profile.php" class="service-tile">
                        <div class="icon-circle bg-violet-100 text-violet-600"><i class="fa-solid fa-user-shield"></i></div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800 block truncate">এডমিন প্রোফাইল</span>
                            <span class="text-[10px] text-slate-500 block">সিকিউরিটি ও একাউন্ট</span>
                        </div>
                    </a>
                </div>
            </div>

        </div>

        <!-- Right Column Widgets (4 Columns - Exact Match to Screenshot) -->
        <div class="lg:col-span-4 space-y-5">

            <!-- Widget 1: Quick Action Coloured Buttons (দ্রুত অ্যাকশন) -->
            <div class="white-panel p-4 md:p-5">
                <div class="flex items-center gap-2 pb-3 mb-3 border-b border-slate-100">
                    <i class="fa-solid fa-bolt text-emerald-600"></i>
                    <h5 class="font-extrabold text-sm text-slate-800">দ্রুত অ্যাকশন</h5>
                </div>
                <div class="space-y-2.5">
                    <a href="add_service.php" class="action-btn btn-emerald">
                        <i class="fa-solid fa-circle-plus text-base"></i>
                        <span>নতুন সেবা যোগ করুন</span>
                    </a>
                    <a href="all_orders_history.php" class="action-btn btn-blue">
                        <i class="fa-solid fa-cart-shopping text-base"></i>
                        <span>অর্ডার ম্যানেজ করুন</span>
                    </a>
                    <a href="balance_add_remove.php" class="action-btn btn-orange">
                        <i class="fa-solid fa-wallet text-base"></i>
                        <span>ইউজার ব্যালেন্স রিচার্জ</span>
                    </a>
                    <a href="update_marquee.php" class="action-btn btn-purple">
                        <i class="fa-solid fa-paper-plane text-base"></i>
                        <span>গ্রাহক নোটিশ আপডেট</span>
                    </a>
                </div>
            </div>

            <!-- Widget 2: Today's Live Statistics (আজকের পরিসংখ্যান) -->
            <div class="white-panel p-4 md:p-5">
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                    <span class="text-xs font-bold text-slate-500">সাইট অবস্থা</span>
                    <span class="text-xs font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> সক্রিয়
                    </span>
                </div>

                <h6 class="text-xs font-extrabold text-slate-800 mb-2.5">আজকের পরিসংখ্যান</h6>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50">
                        <span class="flex items-center gap-2 font-semibold text-slate-700">
                            <i class="fa-solid fa-file-circle-plus text-emerald-600"></i> মোট অর্ডার
                        </span>
                        <span class="font-black text-slate-900" id="widgetTotalOrders">0</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50">
                        <span class="flex items-center gap-2 font-semibold text-slate-700">
                            <i class="fa-solid fa-circle-check text-blue-600"></i> সম্পন্ন অর্ডার
                        </span>
                        <span class="font-black text-emerald-600" id="widgetSuccessOrders">0</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50">
                        <span class="flex items-center gap-2 font-semibold text-slate-700">
                            <i class="fa-solid fa-clock text-amber-500"></i> মূলতুবি অর্ডার
                        </span>
                        <span class="font-black text-amber-600" id="widgetPendingOrders">0</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50">
                        <span class="flex items-center gap-2 font-semibold text-slate-700">
                            <i class="fa-solid fa-ban text-rose-500"></i> ক্যান্সেল / অন্যান্য
                        </span>
                        <span class="font-black text-rose-600" id="widgetOtherOrders">0</span>
                    </div>
                </div>
            </div>

            <!-- Widget 3: Zero Balance Users with Search Filter -->
            <div class="white-panel p-4 md:p-5 flex flex-col">
                <div class="flex items-center justify-between pb-3 mb-2.5 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation text-rose-500 text-sm"></i>
                        <h5 class="font-extrabold text-sm text-slate-800">শূন্য ব্যালেন্স ইউজার</h5>
                    </div>
                    <span class="text-[11px] font-black bg-rose-100 text-rose-700 px-2.5 py-0.5 rounded-full" id="zeroBalanceCount">0</span>
                </div>

                <!-- Extra Feature: Instant Search Box -->
                <div class="mb-3">
                    <input type="text" id="searchZeroUser" placeholder="নাম বা আইডি দিয়ে খুঁজুন..." class="w-full border border-slate-200 bg-slate-50 rounded-lg px-3 py-1.5 text-xs text-slate-700 outline-none focus:border-emerald-500">
                </div>

                <div id="zeroBalanceList" class="custom-scroll overflow-y-auto space-y-2 max-h-[290px] pr-1">
                    <div class="text-center py-8 text-slate-400 text-xs font-medium">লোড হচ্ছে...</div>
                </div>
            </div>

            <!-- Widget 4: System Info (সিস্টেম তথ্য) -->
            <div class="white-panel p-4 md:p-5">
                <div class="flex items-center gap-2 pb-3 mb-3 border-b border-slate-100">
                    <i class="fa-solid fa-gear text-emerald-700"></i>
                    <h5 class="font-extrabold text-sm text-slate-800">সিস্টেম তথ্য</h5>
                </div>
                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 flex items-center gap-2"><i class="fa-solid fa-code-branch text-emerald-600"></i> ওয়েবসাইট ভার্সন</span>
                        <span class="font-bold text-slate-800">2.5.0 (Pro)</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 flex items-center gap-2"><i class="fa-brands fa-php text-indigo-600"></i> PHP ভার্সন</span>
                        <span class="font-bold text-slate-800"><?php echo $php_version; ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 flex items-center gap-2"><i class="fa-regular fa-clock text-amber-600"></i> সার্ভার টাইম</span>
                        <span class="font-bold text-slate-800"><?php echo $server_time; ?> (Dhaka)</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<?php include_once("footer.php"); ?>

<script>
    let orderChartInstance = null;
    let allZeroUsersData = [];

    $(document).ready(function() {
        updateLiveClock();
        setInterval(updateLiveClock, 1000);

        fetchUserData();
        fetchZeroBalanceUsers();

        let today = new Date().toISOString().split('T')[0];
        $('#start_date').val(today);
        fetchData(today);

        $('#start_date').change(function() {
            let startDate = $(this).val();
            fetchData(startDate);
        });

        // Search filter for Zero Balance Users
        $('#searchZeroUser').on('keyup', function() {
            let keyword = $(this).val().toLowerCase();
            let filtered = allZeroUsersData.filter(u => 
                (u.name && u.name.toLowerCase().includes(keyword)) ||
                (u.id && String(u.id).toLowerCase().includes(keyword)) ||
                (u.phone && u.phone.toLowerCase().includes(keyword))
            );
            renderZeroBalanceUsers(filtered);
        });
    });

    // Extra Feature: Quick Today / Yesterday Date Switcher
    function setQuickDate(daysAgo) {
        let d = new Date();
        d.setDate(d.getDate() - daysAgo);
        let dateStr = d.toISOString().split('T')[0];
        $('#start_date').val(dateStr);
        fetchData(dateStr);
    }

    // Extra Feature: Live Bangla Date & Clock
    function updateLiveClock() {
        let now = new Date();
        let dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        let timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
        $('#liveBanglaDate').text('আজ: ' + now.toLocaleDateString('bn-BD', dateOptions));
        $('#liveBanglaTime').text(now.toLocaleTimeString('bn-BD', timeOptions));
    }

    function fetchData(startDate) {
        let formattedDate = new Date(startDate).toLocaleDateString('bn-BD', {
            weekday: 'short', year: 'numeric', month: 'short', day: 'numeric'
        });
        $('#selectedDateTitle').text("কাজের হিসাব: " + formattedDate);

        $.ajax({
            url: 'order/list/fetch_total_order_summery.php',
            type: 'GET',
            data: { start_date: startDate },
            dataType: 'json',
            success: function(response) {
                let historyWorkSummaryHtml = '';
                let orderListCountsHtml = '';
                let serial = 1;

                let totalPendingCount = 0, totalSuccessCount = 0, totalOtherCount = 0;
                let totalPendingPrice = 0, totalSuccessPrice = 0, totalOtherPrice = 0;
                let totalHistoryOrders = 0, totalHistoryPrice = 0;

                let chartLabels = [];
                let chartValues = [];

                if (response.historyWorkSummary && response.historyWorkSummary.length > 0) {
                    response.historyWorkSummary.forEach(order => {
                        historyWorkSummaryHtml += `
                            <tr>
                                <td class="font-bold text-slate-500">#${serial}</td>
                                <td class="font-bold text-slate-800">${order.order_type}</td>
                                <td class="text-center">
                                    <span class="bg-emerald-100 text-emerald-800 font-bold px-2.5 py-0.5 rounded-full text-[11px]">${order.totalOrders} টি</span>
                                </td>
                                <td class="text-right font-black text-slate-900">৳ ${parseFloat(order.totalPrice).toFixed(2)}</td>
                            </tr>
                        `;
                        totalHistoryOrders += parseInt(order.totalOrders) || 0;
                        totalHistoryPrice += parseFloat(order.totalPrice) || 0;

                        chartLabels.push(order.order_type);
                        chartValues.push(parseInt(order.totalOrders) || 0);
                        serial++;
                    });
                } else {
                    historyWorkSummaryHtml = '<tr><td colspan="4" class="p-4 text-center text-slate-400 text-xs">কোনো রেকর্ড পাওয়া যায়নি</td></tr>';
                }

                let orderTypeSerial = 1;
                if (response.orderListCounts) {
                    for (let orderType in response.orderListCounts) {
                        let order = response.orderListCounts[orderType];
                        orderListCountsHtml += `
                            <tr>
                                <td class="font-bold text-slate-500">#${orderTypeSerial}</td>
                                <td class="font-bold text-slate-800">${orderType}</td>
                                <td class="text-center">
                                    <span class="bg-amber-100 text-amber-800 font-bold px-2 py-0.5 rounded text-xs">${order.pending_count}</span>
                                    <span class="text-[10px] text-slate-400 block">৳${order.pending_price}</span>
                                </td>
                                <td class="text-center">
                                    <span class="bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded text-xs">${order.success_count}</span>
                                    <span class="text-[10px] text-slate-400 block">৳${order.success_price}</span>
                                </td>
                                <td class="text-center">
                                    <span class="bg-rose-100 text-rose-800 font-bold px-2 py-0.5 rounded text-xs">${order.other_count}</span>
                                    <span class="text-[10px] text-slate-400 block">৳${order.other_price}</span>
                                </td>
                            </tr>
                        `;
                        totalPendingCount += parseInt(order.pending_count) || 0;
                        totalSuccessCount += parseInt(order.success_count) || 0;
                        totalOtherCount += parseInt(order.other_count) || 0;

                        totalPendingPrice += parseFloat(order.pending_price) || 0;
                        totalSuccessPrice += parseFloat(order.success_price) || 0;
                        totalOtherPrice += parseFloat(order.other_price) || 0;

                        orderTypeSerial++;
                    }
                } else {
                    orderListCountsHtml = '<tr><td colspan="5" class="p-4 text-center text-slate-400 text-xs">কোনো অর্ডার পাওয়া যায়নি</td></tr>';
                }

                $('#historyWorkSummary').html(historyWorkSummaryHtml);
                $('#orderListCountsSummary').html(orderListCountsHtml);

                $('#totalHistoryOrders').text(totalHistoryOrders + ' টি');
                $('#totalHistoryPrice').text(totalHistoryPrice.toFixed(2));

                $('#totalPendingCount').text(totalPendingCount);
                $('#totalSuccessCount').text(totalSuccessCount);
                $('#totalOtherCount').text(totalOtherCount);

                $('#totalPendingPrice').text(totalPendingPrice.toFixed(2));
                $('#totalSuccessPrice').text(totalSuccessPrice.toFixed(2));
                $('#totalOtherPrice').text(totalOtherPrice.toFixed(2));

                // Update Right Sidebar Today's Statistics
                let allCount = totalPendingCount + totalSuccessCount + totalOtherCount;
                $('#widgetTotalOrders').text(allCount > 0 ? allCount : totalHistoryOrders);
                $('#widgetSuccessOrders').text(totalSuccessCount);
                $('#widgetPendingOrders').text(totalPendingCount);
                $('#widgetOtherOrders').text(totalOtherCount);

                // Update Analytics Line Graph
                renderAnalyticsChart(chartLabels, chartValues);
            },
            error: function() {
                $('#historyWorkSummary').html('<tr><td colspan="4" class="p-4 text-center text-rose-500 text-xs">ডাটা লোড করতে সমস্যা হয়েছে</td></tr>');
                $('#orderListCountsSummary').html('<tr><td colspan="5" class="p-4 text-center text-rose-500 text-xs">ডাটা লোড করতে সমস্যা হয়েছে</td></tr>');
            }
        });
    }

    function renderAnalyticsChart(labels, dataPoints) {
        const ctx = document.getElementById('orderAnalyticsChart').getContext('2d');
        if (orderChartInstance) {
            orderChartInstance.destroy();
        }

        if (labels.length === 0) {
            labels = ['NID সেবা', 'পাসপোর্ট', 'সাইন কপি', 'সার্ভার কপি', 'জন্ম নিবন্ধন'];
            dataPoints = [0, 0, 0, 0, 0];
        }

        orderChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'অর্ডার সংখ্যা',
                    data: dataPoints,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.14)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#059669',
                    pointRadius: 4,
                    tension: 0.35,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { color: '#64748b', font: { size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#475569', font: { size: 11, weight: 'bold' } }
                    }
                }
            }
        });
    }

    function fetchUserData() {
        $.ajax({
            url: 'order/list/fetch_user_data.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#totalUsers').text(response.totalUsers || 0);
                $('#totalBalance').text(response.totalBalance || '0.00');
            },
            error: function() {
                $('#totalUsers').text("0");
                $('#totalBalance').text("0.00");
            }
        });
    }

    function fetchZeroBalanceUsers() {
        $.ajax({
            url: 'order/list/fetch_zero_balance_users.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                allZeroUsersData = response.zeroBalanceUsers || [];
                let zeroBalanceCount = allZeroUsersData.length;
                $('#zeroBalanceUsers').text(zeroBalanceCount);
                $('#zeroBalanceCount').text(zeroBalanceCount);
                renderZeroBalanceUsers(allZeroUsersData);
            },
            error: function() {
                $('#zeroBalanceList').html('<div class="text-center py-6 text-rose-500 text-xs">লোড করা সম্ভব হয়নি</div>');
            }
        });
    }

    function renderZeroBalanceUsers(usersArray) {
        let zeroBalanceHtml = '';
        if (usersArray && usersArray.length > 0) {
            usersArray.forEach((user) => {
                zeroBalanceHtml += `
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 flex items-center justify-between gap-2 hover:bg-emerald-50/50 transition">
                        <div class="flex-1 truncate">
                            <h6 class="font-bold text-xs text-slate-800 truncate">${user.name || 'অজানা ইউজার'}</h6>
                            <p class="text-[10px] text-slate-500 font-mono truncate">#${user.id || ''} | ${user.phone || 'ফোন নেই'}</p>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="text-[10px] font-black text-rose-600 bg-rose-100 px-2 py-0.5 rounded-md">৳0.00</span>
                            <button class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg px-2.5 py-1 text-xs font-bold transition flex items-center gap-1" onclick="addBalanceToUser('${user.id}')">
                                <i class="fa-solid fa-plus text-[9px]"></i> ফান্ড
                            </button>
                        </div>
                    </div>
                `;
            });
        } else {
            zeroBalanceHtml = '<div class="text-center py-8 text-emerald-600 font-bold text-xs"><i class="fa-solid fa-circle-check text-base block mb-1"></i>কোনো শূন্য ব্যালেন্স ইউজার নেই</div>';
        }
        $('#zeroBalanceList').html(zeroBalanceHtml);
    }

    function addBalanceToUser(userId) {
        window.location.href = 'balance_add_remove.php?user_id=' + encodeURIComponent(userId);
    }
</script>