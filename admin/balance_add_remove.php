<?php 
include_once("header.php");

// ব্যালেন্স যোগ/কর্তন হিস্টোরি ফেচ করা
$sql = "SELECT * FROM add_remove_balance ORDER BY id DESC";
$result = $conn->query($sql);

$transactions = [];
$total_added = 0;
$total_removed = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
        if (($row['action'] ?? '') === 'add') {
            $total_added += floatval($row['amount'] ?? 0);
        } elseif (($row['action'] ?? '') === 'remove') {
            $total_removed += floatval($row['amount'] ?? 0);
        }
    }
}
$total_count = count($transactions);
?>

<div class="space-y-6">

    <!-- হেডার ও সারাংশ কার্ড -->
    <div class="bg-white p-5 md:p-6 rounded-3xl border border-slate-200/90 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Manual Fund Management Log</span>
            </div>
            <h3 class="text-xl font-black text-slate-800">ব্যালেন্স যুক্ত / কর্তন হিস্টোরি</h3>
            <p class="text-xs text-slate-500 font-medium">এডমিন কর্তৃক ইউজার একাউন্টে সরাসরি ফান্ড ক্রেডিট বা ডেবিটের পূর্ণাঙ্গ অডিট ট্রেইল</p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="dashboard.php" class="text-xs font-bold text-slate-600 bg-slate-50 border border-slate-200 hover:bg-slate-100 px-3.5 py-2.5 rounded-xl transition">
                <i class="fa-solid fa-arrow-left me-1"></i> ড্যাশবোর্ড
            </a>
            <a href="user_list.php" class="text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2.5 rounded-xl transition shadow-md shadow-indigo-600/20 flex items-center gap-1.5">
                <i class="fa-solid fa-users text-xs"></i> ইউজার তালিকা
            </a>
        </div>
    </div>

    <!-- টপ স্ট্যাটাস মেট্রিক্স -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200/90 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider block">সর্বমোট ট্রানজেকশন</span>
                <h4 class="text-2xl font-black text-slate-800 mt-1"><?php echo $total_count; ?> টি</h4>
                <span class="text-[11px] text-slate-500 font-medium">ম্যানুয়াল এন্ট্রি রেকর্ড</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
        </div>

        <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200/90 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider block">মোট ফান্ড প্রদান (যোগ)</span>
                <h4 class="text-2xl font-black text-emerald-600 mt-1">৳ <?php echo number_format($total_added, 2); ?></h4>
                <span class="text-[11px] text-slate-500 font-medium">ইউজার ব্যালেন্সে ইনপুট</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-circle-plus"></i>
            </div>
        </div>

        <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-200/90 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider block">মোট ফান্ড কর্তন (বাদ)</span>
                <h4 class="text-2xl font-black text-rose-600 mt-1">৳ <?php echo number_format($total_removed, 2); ?></h4>
                <span class="text-[11px] text-slate-500 font-medium">সরাসরি ডেবিট বা রিমুভ</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-circle-minus"></i>
            </div>
        </div>
    </div>

    <!-- ট্রানজেকশন টেবিল কার্ড -->
    <div class="bg-white p-5 md:p-6 rounded-3xl border border-slate-200/90 shadow-sm">
        
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <h4 class="text-sm font-extrabold text-slate-800">লেনদেন হিস্টোরি টেবিল</h4>
            </div>
            <span class="text-xs font-semibold text-slate-400">রিয়েল-টাইম ডাটা ট্র্যাকিং</span>
        </div>

        <div class="overflow-x-auto">
            <table id="balanceLogTable" class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10.5px]">
                    <tr>
                        <th class="p-3.5 text-center w-14">#</th>
                        <th class="p-3.5">ইউজার বিবরণ</th>
                        <th class="p-3.5 text-center">অ্যাকশন টাইপ</th>
                        <th class="p-3.5 text-right">টাকার পরিমাণ</th>
                        <th class="p-3.5 text-center">তারিখ ও সময়</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    <?php if (!empty($transactions)): ?>
                        <?php foreach ($transactions as $index => $item): 
                            $act = strtolower(trim($item['action'] ?? ''));
                            $is_add = ($act === 'add');
                            $amount = floatval($item['amount'] ?? 0);
                        ?>
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3.5 text-center font-bold text-slate-400">
                                <?php echo $index + 1; ?>
                            </td>

                            <td class="p-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-xs font-black shrink-0">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-800 block text-xs"><?php echo htmlspecialchars($item['user'] ?? 'Unknown'); ?></span>
                                        <span class="text-[10px] text-slate-400">লগ আইডি: #<?php echo htmlspecialchars($item['id'] ?? ''); ?></span>
                                    </div>
                                </div>
                            </td>

                            <td class="p-3.5 text-center">
                                <?php if ($is_add): ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-arrow-down-left text-[10px]"></i> যোগ করা হয়েছে
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i class="fa-solid fa-arrow-up-right text-[10px]"></i> কেটে নেয়া হয়েছে
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="p-3.5 text-right">
                                <span class="text-sm font-black <?php echo $is_add ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                    <?php echo $is_add ? '+ ৳ ' : '- ৳ '; ?><?php echo number_format($amount, 2); ?>
                                </span>
                            </td>

                            <td class="p-3.5 text-center">
                                <span class="text-[11px] font-semibold text-slate-500 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-100">
                                    <i class="fa-regular fa-calendar-days me-1 text-[10px]"></i><?php echo htmlspecialchars($item['update_at'] ?? ''); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

<?php include_once("footer.php"); ?>

<!-- DataTables Scripts & Styling -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<style>
    /* DataTables Tailwind Integration */
    .dataTables_wrapper .dataTables_length select {
        padding: 5px 25px 5px 10px;
        border-radius: 10px;
        border: 1px solid #E2E8F0;
        background-color: #F8FAFC;
        font-size: 11.5px;
        font-weight: 700;
        outline: none;
    }
    .dataTables_wrapper .dataTables_filter input {
        padding: 6px 14px;
        border-radius: 12px;
        border: 1px solid #CBD5E1;
        background-color: #F8FAFC;
        font-size: 11.5px;
        outline: none;
        transition: all 0.2s;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #4F46E5;
        background-color: #FFFFFF;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        font-size: 11.5px;
        color: #64748B !important;
        margin-top: 14px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        padding: 4px 10px !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        border: 1px solid #E2E8F0 !important;
        background: #FFFFFF !important;
        color: #334155 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #4F46E5 !important;
        color: #FFFFFF !important;
        border-color: #4F46E5 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #EEF2FF !important;
        color: #4F46E5 !important;
        border-color: #C7D2FE !important;
    }
</style>

<script>
    $(document).ready(function() {
        $('#balanceLogTable').DataTable({
            "ordering": false,
            "pageLength": 15,
            "language": {
                "search": "খুঁজুন:",
                "lengthMenu": "প্রতি পেজে _MENU_ টি রেকর্ড",
                "info": "সর্বমোট _TOTAL_ টির মধ্যে _START_ থেকে _END_ দেখানো হচ্ছে",
                "infoEmpty": "কোনো রেকর্ড পাওয়া যায়নি",
                "zeroRecords": "খুঁজে পাওয়া যায়নি",
                "paginate": {
                    "first": "প্রথম",
                    "last": "শেষ",
                    "next": "পরবর্তী",
                    "previous": "পূর্ববর্তী"
                }
            }
        });
    });
</script>