<?php include_once("header.php"); ?>

<div class="space-y-6">

    <!-- Header & Filter Bar -->
    <div class="bg-white p-5 md:p-6 rounded-3xl border border-slate-200/90 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-violet-500 animate-pulse"></span>
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Work & Order Analytics</span>
            </div>
            <h3 class="text-xl font-black text-slate-800">কাজের সামগ্রিক হিস্টোরি</h3>
            <p class="text-xs text-slate-500 font-medium">ইউজারদের সম্পন্নকৃত সার্ভিস অর্ডার ও লেজার ব্যালেন্স ট্র্যাকিং</p>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative w-full sm:w-56">
                <select id="dateFilter" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-700 outline-none focus:border-indigo-500 transition appearance-none cursor-pointer">
                    <option value="1">আজকের কাজ (Today)</option>
                    <option value="3">বিগত ৩ দিন (Last 3 Days)</option>
                    <option value="7">বিগত ৭ দিন (Last 7 Days)</option>
                    <option value="10">বিগত ১০ দিন (Last 10 Days)</option>
                    <option value="15">বিগত ১৫ দিন (Last 15 Days)</option>
                    <option value="20">বিগত ২০ দিন (Last 20 Days)</option>
                    <option value="30">বিগত ৩০ দিন (Last 30 Days)</option>
                    <option value="all">সর্বমোট হিস্টোরি (All Days)</option>
                </select>
                <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
            </div>

            <button type="button" id="refreshWorkBtn" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-600 border border-indigo-200 px-3.5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shrink-0" title="রিফ্রেশ">
                <i class="fa-solid fa-rotate-right"></i>
            </button>
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex items-center justify-between relative overflow-hidden group hover:shadow-md transition">
            <div>
                <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider block">মোট অর্ডারের সংখ্যা</span>
                <h4 class="text-2xl font-black text-slate-800 mt-1"><span id="totalOrders">0</span> টি</h4>
                <span class="text-[11px] text-slate-500 font-medium">ফিল্টার অনুযায়ী সম্পন্ন কাজ</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold group-hover:scale-110 transition">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-indigo-300"></div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex items-center justify-between relative overflow-hidden group hover:shadow-md transition">
            <div>
                <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider block">মোট চার্জ / বিল</span>
                <h4 class="text-2xl font-black text-emerald-600 mt-1">৳ <span id="totalPrice">0.00</span></h4>
                <span class="text-[11px] text-slate-500 font-medium">অর্ডার বাবদ কর্তনকৃত মোট টাকা</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold group-hover:scale-110 transition">
                <i class="fa-solid fa-coins"></i>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-300"></div>
        </div>

    </div>

    <!-- Table Container Card -->
    <div class="bg-white p-5 md:p-6 rounded-3xl border border-slate-200/90 shadow-sm">
        
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <h4 class="text-sm font-extrabold text-slate-800">লেনদেন ও অর্ডারের অডিট টেবিল</h4>
            </div>
            <span class="text-xs font-semibold text-slate-400">ফিল্টার অনুযায়ী সরাসরি ডাটাবেজ থেকে আপডেট</span>
        </div>

        <div class="overflow-x-auto">
            <table id="myTable" class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10.5px]">
                    <tr>
                        <th class="p-3.5 text-center w-12">#</th>
                        <th class="p-3.5">ইউজার ইমেইল</th>
                        <th class="p-3.5 text-center">কাজের ধরন</th>
                        <th class="p-3.5 text-right">পূর্ববর্তী ব্যালেন্স</th>
                        <th class="p-3.5 text-center">কাজের চার্জ</th>
                        <th class="p-3.5 text-right">পরবর্তী ব্যালেন্স</th>
                        <th class="p-3.5 text-center">কাজের সময়</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium"></tbody>
            </table>
        </div>

    </div>

</div>

<?php include_once("footer.php"); ?>

<!-- DataTables Scripts & Styling -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<style>
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
    $(document).ready(function () {
        const table = $('#myTable').DataTable({
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

        function updateSummaryCards(days) {
            $.ajax({
                url: 'get_work_summary.php',
                method: 'GET',
                data: { days: days },
                dataType: 'json',
                success: function(data) {
                    $('#totalOrders').text(data.totalOrders || 0);
                    $('#totalPrice').text(parseFloat(data.totalPrice || 0).toFixed(2));
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching summary data:", error);
                }
            });
        }

        function fetchAndUpdateData(days) {
            $.ajax({
                url: 'get_work_history.php',
                method: 'GET',
                data: { days: days },
                dataType: 'json',
                success: function(data) {
                    table.clear();
                    let counter = 1;
                    if (data && data.length > 0) {
                        data.forEach(function(row) {
                            table.row.add([
                                `<span class="font-bold text-slate-400">${counter++}</span>`,
                                `<span class="font-bold text-slate-800 text-xs">${row.email || 'N/A'}</span>`,
                                `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">${row.orderType || 'N/A'}</span>`,
                                `<span class="font-bold text-slate-600">৳ ${parseFloat(row.beforeBalance || 0).toFixed(2)}</span>`,
                                `<span class="font-black text-rose-600 bg-rose-50 border border-rose-200 px-2.5 py-0.5 rounded-lg text-xs">৳ ${parseFloat(row.price || 0).toFixed(2)}</span>`,
                                `<span class="font-bold text-emerald-600">৳ ${parseFloat(row.afterBalance || 0).toFixed(2)}</span>`,
                                `<span class="text-[11px] font-semibold text-slate-500 bg-slate-50 px-2 py-0.5 rounded border border-slate-100">${row.orderTime || ''}</span>`
                            ]);
                        });
                    }
                    table.draw(false);
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching table data:", error);
                }
            });
        }

        const initialDays = $('#dateFilter').val() || 1;
        fetchAndUpdateData(initialDays);
        updateSummaryCards(initialDays);

        $('#dateFilter').on('change', function() {
            const selectedDays = $(this).val();
            fetchAndUpdateData(selectedDays);
            updateSummaryCards(selectedDays);
        });

        $('#refreshWorkBtn').on('click', function() {
            const btn = $(this);
            btn.find('i').addClass('fa-spin');
            const selectedDays = $('#dateFilter').val();
            fetchAndUpdateData(selectedDays);
            updateSummaryCards(selectedDays);
            setTimeout(() => {
                btn.find('i').removeClass('fa-spin');
            }, 600);
        });
    });
</script>