<?php
include_once("includes/configuration.php");

$pending_deposits = 0;
$dep_q = $conn->query("SELECT COUNT(*) AS total FROM deposit_requests WHERE status = 'pending'");
if ($dep_q && $row_dep = $dep_q->fetch_assoc()) {
    $pending_deposits = $row_dep['total'];
}
?>

<?php include_once("header.php"); ?>

<style>
    .hub-card {
        background: radial-gradient(130% 130% at 0% 0%, rgba(30, 16, 61, 0.5) 0%, rgba(13, 8, 27, 0.7) 100%);
        border: 1px solid rgba(168, 85, 247, 0.16);
        border-radius: 20px;
        position: relative;
        overflow: hidden;
        transition: all 0.25s ease;
    }
    .hub-card:hover {
        border-color: rgba(192, 132, 252, 0.35);
        box-shadow: 0 10px 30px -10px rgba(124, 58, 237, 0.25);
    }
    .quick-pill-tile {
        background: rgba(22, 13, 44, 0.6);
        border: 1px solid rgba(168, 85, 247, 0.14);
        border-radius: 16px;
        padding: 12px 14px;
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .quick-pill-tile:hover {
        background: rgba(124, 58, 237, 0.18);
        border-color: rgba(192, 132, 252, 0.4);
        transform: translateY(-2px);
    }
    .quick-pill-tile .icon-box {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }
</style>

<div class="space-y-5">

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
        
        <div class="hub-card p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold uppercase tracking-wider text-purple-300/80">মোট ইউজার</span>
                <div class="w-8 h-8 rounded-xl bg-purple-500/15 text-purple-400 border border-purple-500/20 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div>
                <h3 class="text-2xl md:text-3xl font-black text-white tracking-tight" id="totalUsers">0</h3>
                <span class="text-[10px] text-slate-400 mt-0.5 block">সর্বমোট নিবন্ধিত একাউন্ট</span>
            </div>
        </div>

        <div class="hub-card p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400/80">ইউজার ব্যালেন্স</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
            <div>
                <h3 class="text-2xl md:text-3xl font-black text-emerald-400 tracking-tight">৳ <span id="totalBalance">0.00</span></h3>
                <span class="text-[10px] text-slate-400 mt-0.5 block">ইউজার কারেন্ট ফান্ড</span>
            </div>
        </div>

        <div class="hub-card p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold uppercase tracking-wider text-rose-400/80">শূন্য ব্যালেন্স</span>
                <div class="w-8 h-8 rounded-xl bg-rose-500/15 text-rose-400 border border-rose-500/20 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div>
                <h3 class="text-2xl md:text-3xl font-black text-rose-400 tracking-tight" id="zeroBalanceUsers">0</h3>
                <span class="text-[10px] text-slate-400 mt-0.5 block">ব্যালেন্সহীন একাউন্ট</span>
            </div>
        </div>

        <div class="hub-card p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400/80">পেন্ডিং রিচার্জ</span>
                <div class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-400 border border-amber-500/20 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                </div>
            </div>
            <div>
                <h3 class="text-2xl md:text-3xl font-black text-amber-400 tracking-tight"><?php echo $pending_deposits; ?> টি</h3>
                <span class="text-[10px] text-slate-400 mt-0.5 block">অপেক্ষমান ডিপোজিট</span>
            </div>
        </div>

    </div>

    <div class="hub-card p-4 md:p-5">
        <div class="flex items-center justify-between mb-3.5 pb-2.5 border-b border-purple-500/15">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-purple-500/20 text-purple-300 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <h4 class="text-sm font-extrabold text-white">মাস্টার সার্ভিস ও ম্যানেজমেন্ট কন্ট্রোল</h4>
            </div>
            <span class="text-[10px] font-bold text-purple-300/70 bg-purple-950/60 border border-purple-500/20 px-2.5 py-0.5 rounded-full">Quick Grid</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2.5">
            
            <a href="manage_services.php" class="quick-pill-tile">
                <div class="icon-box bg-rose-500/15 text-rose-400 border border-rose-500/25"><i class="fa-solid fa-power-off"></i></div>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-white block truncate">সার্ভিস সুইচ</span>
                    <span class="text-[10px] text-purple-300/60 block">অন / অফ</span>
                </div>
            </a>

            <a href="all_pricing.php" class="quick-pill-tile">
                <div class="icon-box bg-amber-500/15 text-amber-400 border border-amber-500/25"><i class="fa-solid fa-coins"></i></div>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-white block truncate">মাস্টার রেট</span>
                    <span class="text-[10px] text-purple-300/60 block">প্রাইসিং তালিকা</span>
                </div>
            </a>

            <a href="all_orders_history.php" class="quick-pill-tile">
                <div class="icon-box bg-sky-500/15 text-sky-400 border border-sky-500/25"><i class="fa-solid fa-boxes-stacked"></i></div>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-white block truncate">সকল অর্ডার</span>
                    <span class="text-[10px] text-purple-300/60 block">লগ ও ডাটা</span>
                </div>
            </a>

            <a href="manage_deposits.php" class="quick-pill-tile">
                <div class="icon-box bg-emerald-500/15 text-emerald-400 border border-emerald-500/25"><i class="fa-solid fa-money-bill-transfer"></i></div>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-white block truncate">পেন্ডিং ফান্ড</span>
                    <span class="text-[10px] text-purple-300/60 block">রিচার্জ অনুমোদন</span>
                </div>
            </a>

            <a href="settings.php" class="quick-pill-tile">
                <div class="icon-box bg-purple-500/15 text-purple-300 border border-purple-500/25"><i class="fa-solid fa-sliders"></i></div>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-white block truncate">সাইট সেটিংস</span>
                    <span class="text-[10px] text-purple-300/60 block">লিংক ও নম্বর</span>
                </div>
            </a>

            <a href="work_history.php" class="quick-pill-tile">
                <div class="icon-box bg-indigo-500/15 text-indigo-400 border border-indigo-500/25"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-white block truncate">কাজের হিস্টোরি</span>
                    <span class="text-[10px] text-purple-300/60 block">ইউজার সামারি</span>
                </div>
            </a>

            <a href="add_service.php" class="quick-pill-tile">
                <div class="icon-box bg-teal-500/15 text-teal-400 border border-teal-500/25"><i class="fa-solid fa-circle-plus"></i></div>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-white block truncate">নতুন সার্ভিস</span>
                    <span class="text-[10px] text-purple-300/60 block">সার্ভিস তৈরি</span>
                </div>
            </a>

            <a href="user_list.php" class="quick-pill-tile">
                <div class="icon-box bg-cyan-500/15 text-cyan-400 border border-cyan-500/25"><i class="fa-solid fa-users-gear"></i></div>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-white block truncate">ইউজার তালিকা</span>
                    <span class="text-[10px] text-purple-300/60 block">ম্যানেজ একাউন্ট</span>
                </div>
            </a>

            <a href="balance_add_remove.php" class="quick-pill-tile">
                <div class="icon-box bg-pink-500/15 text-pink-400 border border-pink-500/25"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-white block truncate">ব্যালেন্স এড/কাট</span>
                    <span class="text-[10px] text-purple-300/60 block">ইউজার ফান্ড</span>
                </div>
            </a>

            <a href="createLink.php" class="quick-pill-tile">
                <div class="icon-box bg-blue-500/15 text-blue-400 border border-blue-500/25"><i class="fa-solid fa-link"></i></div>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-white block truncate">ইনভাইট লিংক</span>
                    <span class="text-[10px] text-purple-300/60 block">রেজিস্ট্রেশন URL</span>
                </div>
            </a>

            <a href="update_marquee.php" class="quick-pill-tile">
                <div class="icon-box bg-rose-500/15 text-rose-400 border border-rose-500/25"><i class="fa-solid fa-bullhorn"></i></div>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-white block truncate">নোটিশ মডিউল</span>
                    <span class="text-[10px] text-purple-300/60 block">মারকুই টেক্সট</span>
                </div>
            </a>

            <a href="profile.php" class="quick-pill-tile">
                <div class="icon-box bg-violet-500/15 text-violet-300 border border-violet-500/25"><i class="fa-solid fa-user-shield"></i></div>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-white block truncate">এডমিন প্রোফাইল</span>
                    <span class="text-[10px] text-purple-300/60 block">সিকিউরিটি ও একাউন্ট</span>
                </div>
            </a>

        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        <div class="lg:col-span-4 hub-card p-4 md:p-5 flex flex-col">
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-purple-500/15">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-400 text-sm"></i>
                    <h5 class="font-extrabold text-sm text-white">শূন্য ব্যালেন্স ইউজার</h5>
                </div>
                <span class="text-[11px] font-black bg-rose-500/15 text-rose-400 border border-rose-500/25 px-2 py-0.5 rounded-full" id="zeroBalanceCount">0</span>
            </div>

            <div id="zeroBalanceList" class="flex-1 overflow-y-auto space-y-2 max-h-[460px] pr-1">
                <div class="text-center py-8 text-purple-300/40 text-xs font-medium">লোড হচ্ছে...</div>
            </div>
        </div>

        <div class="lg:col-span-8 hub-card p-4 md:p-5 space-y-4">
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-3 border-b border-purple-500/15">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <h5 class="font-extrabold text-sm text-white" id="selectedDateTitle">তারিখ অনুযায়ী কাজের হিসাব</h5>
                </div>
                <div>
                    <input type="date" id="start_date" name="start_date" class="border border-purple-500/25 rounded-xl px-3 py-1.5 text-xs font-bold text-purple-200 bg-[#0A0517] outline-none focus:border-purple-400 transition">
                </div>
            </div>

            <div>
                <h6 class="text-[10.5px] font-extrabold uppercase tracking-wider text-purple-300/60 mb-2">কাজের সামগ্রিক সারাংশ</h6>
                <div class="overflow-x-auto rounded-xl border border-purple-500/15">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-purple-950/40 text-purple-300 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="p-2.5">#</th>
                                <th class="p-2.5">কাজের ধরন</th>
                                <th class="p-2.5 text-center">অর্ডার সংখ্যা</th>
                                <th class="p-2.5 text-right">মোট চার্জ</th>
                            </tr>
                        </thead>
                        <tbody id="historyWorkSummary" class="divide-y divide-purple-500/10 text-slate-300">
                            <tr><td colspan="4" class="p-4 text-center text-purple-300/40">ডাটা লোড হচ্ছে...</td></tr>
                        </tbody>
                        <tfoot class="bg-purple-950/60 font-black text-white border-t border-purple-500/20">
                            <tr>
                                <th colspan="2" class="p-2.5">সর্বমোট</th>
                                <th class="p-2.5 text-center" id="totalHistoryOrders">0 টি</th>
                                <th class="p-2.5 text-right text-emerald-400">৳ <span id="totalHistoryPrice">0.00</span></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div>
                <h6 class="text-[10.5px] font-extrabold uppercase tracking-wider text-purple-300/60 mb-2">সার্ভিস অর্ডার স্ট্যাটাস ও কাউন্ট</h6>
                <div class="overflow-x-auto rounded-xl border border-purple-500/15">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-purple-950/40 text-purple-300 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="p-2.5">#</th>
                                <th class="p-2.5">সার্ভিস নাম</th>
                                <th class="p-2.5 text-center text-amber-400">পেন্ডিং</th>
                                <th class="p-2.5 text-center text-emerald-400">সফল</th>
                                <th class="p-2.5 text-center text-rose-400">অন্যান্য</th>
                            </tr>
                        </thead>
                        <tbody id="orderListCountsSummary" class="divide-y divide-purple-500/10 text-slate-300">
                            <tr><td colspan="5" class="p-4 text-center text-purple-300/40">ডাটা লোড হচ্ছে...</td></tr>
                        </tbody>
                        <tfoot class="bg-purple-950/60 font-black text-white border-t border-purple-500/20">
                            <tr>
                                <th colspan="2" class="p-2.5">মোট কাউন্ট</th>
                                <th class="p-2.5 text-center text-amber-400" id="totalPendingCount">0</th>
                                <th class="p-2.5 text-center text-emerald-400" id="totalSuccessCount">0</th>
                                <th class="p-2.5 text-center text-rose-400" id="totalOtherCount">0</th>
                            </tr>
                            <tr>
                                <th colspan="2" class="p-2.5">মোট চার্জ</th>
                                <th class="p-2.5 text-center text-amber-400">৳ <span id="totalPendingPrice">0.00</span></th>
                                <th class="p-2.5 text-center text-emerald-400">৳ <span id="totalSuccessPrice">0.00</span></th>
                                <th class="p-2.5 text-center text-rose-400">৳ <span id="totalOtherPrice">0.00</span></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>

    </div>

</div>

<?php include_once("footer.php"); ?>

<script>
    $(document).ready(function() {
        fetchUserData();
        fetchZeroBalanceUsers();

        let today = new Date().toISOString().split('T')[0];
        $('#start_date').val(today);
        fetchData(today);

        $('#start_date').change(function() {
            let startDate = $(this).val();
            fetchData(startDate);
        });
    });

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

                if (response.historyWorkSummary && response.historyWorkSummary.length > 0) {
                    response.historyWorkSummary.forEach(order => {
                        historyWorkSummaryHtml += `
                            <tr class="hover:bg-purple-900/15 transition">
                                <td class="p-2.5 font-bold text-purple-400">${serial}</td>
                                <td class="p-2.5 font-bold text-white">${order.order_type}</td>
                                <td class="p-2.5 text-center"><span class="bg-purple-500/15 text-purple-300 border border-purple-500/20 font-bold px-2 py-0.5 rounded-full text-[10.5px]">${order.totalOrders} টি</span></td>
                                <td class="p-2.5 text-right font-black text-white">৳ ${parseFloat(order.totalPrice).toFixed(2)}</td>
                            </tr>
                        `;
                        totalHistoryOrders += parseInt(order.totalOrders) || 0;
                        totalHistoryPrice += parseFloat(order.totalPrice) || 0;
                        serial++;
                    });
                } else {
                    historyWorkSummaryHtml = '<tr><td colspan="4" class="p-4 text-center text-purple-300/40 text-xs">কোনো রেকর্ড পাওয়া যায়নি</td></tr>';
                }

                let orderTypeSerial = 1;
                if (response.orderListCounts) {
                    for (let orderType in response.orderListCounts) {
                        let order = response.orderListCounts[orderType];
                        orderListCountsHtml += `
                            <tr class="hover:bg-purple-900/15 transition">
                                <td class="p-2.5 font-bold text-purple-400">${orderTypeSerial}</td>
                                <td class="p-2.5 font-bold text-white">${orderType}</td>
                                <td class="p-2.5 text-center"><span class="text-amber-400 font-bold">${order.pending_count}</span> <span class="text-[9.5px] text-purple-300/50">(৳${order.pending_price})</span></td>
                                <td class="p-2.5 text-center"><span class="text-emerald-400 font-bold">${order.success_count}</span> <span class="text-[9.5px] text-purple-300/50">(৳${order.success_price})</span></td>
                                <td class="p-2.5 text-center"><span class="text-rose-400 font-bold">${order.other_count}</span> <span class="text-[9.5px] text-purple-300/50">(৳${order.other_price})</span></td>
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
                    orderListCountsHtml = '<tr><td colspan="5" class="p-4 text-center text-purple-300/40 text-xs">কোনো অর্ডার পাওয়া যায়নি</td></tr>';
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
            },
            error: function() {
                $('#historyWorkSummary').html('<tr><td colspan="4" class="p-4 text-center text-rose-400 text-xs">ডাটা লোড করতে সমস্যা হয়েছে</td></tr>');
                $('#orderListCountsSummary').html('<tr><td colspan="5" class="p-4 text-center text-rose-400 text-xs">ডাটা লোড করতে সমস্যা হয়েছে</td></tr>');
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
                let zeroBalanceHtml = '';
                let zeroBalanceCount = 0;

                if (response.zeroBalanceUsers && response.zeroBalanceUsers.length > 0) {
                    zeroBalanceCount = response.zeroBalanceUsers.length;
                    response.zeroBalanceUsers.forEach((user) => {
                        zeroBalanceHtml += `
                            <div class="bg-purple-950/30 border border-purple-500/15 rounded-xl p-2.5 flex items-center justify-between gap-2 hover:bg-purple-900/25 transition">
                                <div class="flex-1 truncate">
                                    <h6 class="font-bold text-xs text-white truncate">${user.name || 'অজানা ইউজার'}</h6>
                                    <p class="text-[9.5px] text-purple-300/50 font-mono truncate">#${user.id || ''} | ${user.phone || 'ফোন নেই'}</p>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="text-[10px] font-black text-rose-400 bg-rose-500/15 border border-rose-500/25 px-2 py-0.5 rounded-lg">৳0.00</span>
                                    <button class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white rounded-lg px-2.5 py-1 text-xs font-bold transition flex items-center gap-1" onclick="addBalanceToUser('${user.id}')">
                                        <i class="fa-solid fa-plus text-[9px]"></i> ফান্ড
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    zeroBalanceHtml = '<div class="text-center py-8 text-emerald-400 font-bold text-xs"><i class="fa-solid fa-circle-check text-base block mb-1"></i>কোনো শূন্য ব্যালেন্স ইউজার নেই</div>';
                }

                $('#zeroBalanceList').html(zeroBalanceHtml);
                $('#zeroBalanceUsers').text(zeroBalanceCount);
                $('#zeroBalanceCount').text(zeroBalanceCount);
            },
            error: function() {
                $('#zeroBalanceList').html('<div class="text-center py-6 text-rose-400 text-xs">লোড করা সম্ভব হয়নি</div>');
            }
        });
    }

    function addBalanceToUser(userId) {
        window.location.href = 'balance_add_remove.php?user_id=' + encodeURIComponent(userId);
    }
</script>