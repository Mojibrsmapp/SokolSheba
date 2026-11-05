<?php
include_once("header.php");

// ১. একক বা বাল্ক আপডেট হ্যান্ডলিং
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    
    // নতুন নোটিশ যুক্ত করা
    if ($_POST['action'] === 'add_notice') {
        $page_name = trim($_POST['page_name'] ?? '');
        $notice_text = trim($_POST['notice_text'] ?? '');

        if (!empty($page_name) && !empty($notice_text)) {
            $encoded_text = base64_encode($notice_text);
            $stmt =$conn->prepare("INSERT INTO marquee (page_name, marquee_text) VALUES (?, ?)");
            $stmt->bind_param("ss", $page_name,$encoded_text);
            if ($stmt->execute()) {$status_msg = ['type' => 'success', 'title' => 'সফল!', 'text' => 'নতুন নোটিশ সফলভাবে যুক্ত করা হয়েছে।'];
            } else {
                $status_msg = ['type' => 'error', 'title' => 'ত্রুটি!', 'text' => 'নোটিশ যুক্ত করা সম্ভব হয়নি।'];
            }
            $stmt->close();
        }
    }

    // নির্দিষ্ট একটি নোটিশ আপডেট করা
    if ($_POST['action'] === 'single_update') {
        $id = intval($_POST['notice_id'] ?? 0);
        $text = trim($_POST['notice_text'] ?? '');
        
        if ($id > 0) {
            $encoded_text = base64_encode($text);
            $stmt =$conn->prepare("UPDATE marquee SET marquee_text = ? WHERE id = ?");
            $stmt->bind_param("si", $encoded_text,$id);
            if ($stmt->execute()) {$status_msg = ['type' => 'success', 'title' => 'আপডেট সম্পন্ন!', 'text' => 'নোটিশটি সফলভাবে সংরক্ষণ করা হয়েছে।'];
            } else {
                $status_msg = ['type' => 'error', 'title' => 'ত্রুটি!', 'text' => 'আপডেট ব্যর্থ হয়েছে।'];
            }
            $stmt->close();
        }
    }

    // বাল্ক আপডেট (সবগুলো একসাথে)
    if ($_POST['action'] === 'bulk_update' && isset($_POST['numbers'])) {$all_success = true;
        foreach ($_POST['numbers'] as $id =>$raw_text) {
            $encoded = base64_encode(trim($raw_text));
            $stmt =$conn->prepare("UPDATE marquee SET marquee_text = ? WHERE id = ?");
            if ($stmt) {$stmt->bind_param("si", $encoded,$id);
                if (!$stmt->execute()) {$all_success = false;
                }
                $stmt->close();
            } else {
                $all_success = false;
            }
        }

        if ($all_success) {$status_msg = ['type' => 'success', 'title' => 'সফল!', 'text' => 'সকল নোটিশ একযোগে আপডেট করা হয়েছে।'];
        } else {
            $status_msg = ['type' => 'warning', 'title' => 'আংশিক ত্রুটি!', 'text' => 'কিছু নোটিশ আপডেট করা সম্ভব হয়নি।'];
        }
    }

    // নোটিশ ডিলিট করা
    if ($_POST['action'] === 'delete_notice') {
        $id = intval($_POST['delete_id'] ?? 0);
        if ($id > 0) {
            $stmt =$conn->prepare("DELETE FROM marquee WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {$status_msg = ['type' => 'success', 'title' => 'মুছে ফেলা হয়েছে!', 'text' => 'নোটিশটি তালিকা থেকে সরানো হয়েছে।'];
            } else {
                $status_msg = ['type' => 'error', 'title' => 'ত্রুটি!', 'text' => 'মুছে ফেলা ব্যর্থ হয়েছে।'];
            }
            $stmt->close();
        }
    }
}

// সকল নোটিশ ফেচ করা
$notices = [];
$res =$conn->query("SELECT id, page_name, marquee_text, create_at FROM marquee ORDER BY id ASC");
if ($res &&$res->num_rows > 0) {
    while ($row =$res->fetch_assoc()) {
        $notices[] = $row;
    }
}
$total_notices = count($notices);
?>

<div class="space-y-6">

    <!-- হেডার ও অ্যাকশন বার -->
    <div class="bg-white p-5 md:p-6 rounded-3xl border border-slate-200/90 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-pink-500 animate-pulse"></span>
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Notice Announcement Center</span>
            </div>
            <h3 class="text-xl font-black text-slate-800">স্ক্রলিং নোটিশ ও মারকুই টেক্সট ম্যানেজার</h3>
            <p class="text-xs text-slate-500 font-medium">ওয়েবসাইটের সকল পেইজের চলমান স্ক্রলিং নোটিশ সহজে পরিবর্তন ও নিয়ন্ত্রণ করুন</p>
        </div>

        <div class="flex items-center gap-2.5 w-full md:w-auto">
            <button type="button" onclick="openAddNoticeModal()" class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition shadow-md shadow-indigo-600/20 flex items-center justify-center gap-2">
                <i class="fa-solid fa-plus text-xs"></i> নতুন নোটিশ যোগ করুন
            </button>
            <button type="submit" form="bulkUpdateForm" class="w-full md:w-auto bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition shadow-md shadow-emerald-600/20 flex items-center justify-center gap-2">
                <i class="fa-solid fa-floppy-disk text-xs"></i> সব সংরক্ষণ করুন
            </button>
        </div>
    </div>

    <!-- ফিল্টারিং ও স্ট্যাটাস বার -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="relative w-full sm:w-80">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input type="text" id="searchNoticeInput" placeholder="পেইজের নাম দিয়ে খুঁজুন..." class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-xl text-xs font-semibold outline-none focus:border-indigo-500 bg-slate-50 transition">
        </div>
        <div class="flex items-center gap-2 self-end sm:self-auto text-xs font-bold text-slate-500">
            <span>সর্বমোট নোটিশ: <span class="text-indigo-600 font-black" id="noticeCounter"><?php echo $total_notices; ?></span> টি</span>
        </div>
    </div>

    <!-- নোটিশ কার্ড গ্রিড -->
    <form id="bulkUpdateForm" method="POST" action="">
        <input type="hidden" name="action" value="bulk_update">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5" id="noticeCardsContainer">
            <?php if (!empty($notices)): ?>
                <?php foreach ($notices as $notice): 
                    $decoded_text = base64_decode($notice['marquee_text'] ?? '');
                ?>
                <div class="notice-card bg-white rounded-3xl p-5 border border-slate-200/90 shadow-sm hover:shadow-md transition flex flex-col justify-between" data-page="<?php echo htmlspecialchars(mb_strtolower($notice['page_name'], 'UTF-8')); ?>">
                    
                    <div>
                        <!-- কার্ড হেডার -->
                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-pink-50 text-pink-600 font-black text-xs flex items-center justify-center border border-pink-100">
                                    #<?php echo $notice['id']; ?>
                                </span>
                                <div>
                                    <h4 class="text-xs font-black text-slate-800"><?php echo htmlspecialchars($notice['page_name']); ?></h4>
                                    <span class="text-[10px] text-slate-400 font-mono"><?php echo htmlspecialchars($notice['create_at'] ?? 'Auto Setup'); ?></span>
                                </div>
                            </div>

                            <button type="button" onclick="confirmDelete(<?php echo $notice['id']; ?>, '<?php echo htmlspecialchars($notice['page_name']); ?>')" class="text-slate-400 hover:text-rose-600 text-xs p-1.5 rounded-lg transition" title="মুছে ফেলুন">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>

                        <!-- লাইভ প্রিভিউ স্ক্রলার -->
                        <div class="mb-3 bg-slate-900 rounded-xl p-2.5 overflow-hidden">
                            <div class="flex items-center gap-2">
                                <span class="bg-pink-500 text-white text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wider flex-shrink-0">Live Preview</span>
                                <div class="overflow-hidden whitespace-nowrap w-full">
                                    <div class="inline-block text-xs text-white font-medium marquee-preview-text" id="preview_<?php echo $notice['id']; ?>">
                                        <?php echo nl2br(htmlspecialchars($decoded_text)); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- টেক্সট এরিয়া -->
                        <div class="space-y-1 mb-4">
                            <label class="text-[11px] font-bold text-slate-500">নোটিশ কনটেন্ট (সরাসরি এডিট করুন)</label>
                            <textarea name="numbers[<?php echo $notice['id']; ?>]" 
                                      rows="4" 
                                      class="w-full border border-slate-200 rounded-2xl p-3 text-xs font-semibold text-slate-700 bg-slate-50 outline-none focus:border-indigo-500 focus:bg-white transition leading-relaxed resize-y notice-textarea" 
                                      data-target="preview_<?php echo $notice['id']; ?>" 
                                      required><?php echo htmlspecialchars($decoded_text); ?></textarea>
                        </div>
                    </div>

                    <!-- একক আপডেট বাটন -->
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400">অটো বেস৬৪ এনকোডেড</span>
                        <button type="button" onclick="saveSingleNotice(<?php echo $notice['id']; ?>, this)" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-[11px] font-bold px-3 py-1.5 rounded-xl transition flex items-center gap-1.5">
                            <i class="fa-solid fa-check"></i> শুধু এটি সেভ করুন
                        </button>
                    </div>

                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full bg-white p-10 rounded-3xl border text-center text-slate-400 text-xs">
                    কোনো নোটিশ পাওয়া যায়নি। নতুন নোটিশ যুক্ত করতে উপরের বাটনে চাপুন।
                </div>
            <?php endif; ?>
        </div>
    </form>

    <div id="noSearchAlert" class="hidden bg-white p-8 rounded-3xl border text-center text-slate-400 text-xs">
        অনুসন্ধানকৃত পেইজের নামে কোনো নোটিশ খুঁজে পাওয়া যায়নি।
    </div>

</div>

<!-- নতুন নোটিশ যোগ করার মডাল -->
<div id="addNoticeModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 relative">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h4 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-bullhorn text-indigo-600"></i> নতুন পেইজের নোটিশ তৈরি করুন
            </h4>
            <button type="button" onclick="closeAddNoticeModal()" class="text-slate-400 hover:text-slate-600 text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="" class="space-y-4">
            <input type="hidden" name="action" value="add_notice">

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">পেইজের নাম (Page / Identifier Name)</label>
                <input type="text" name="page_name" placeholder="যেমন: অর্ডার ১, রিচার্জ পেইজ" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold bg-slate-50 outline-none focus:border-indigo-500 focus:bg-white transition" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">নোটিশের বিবরণ</label>
                <textarea name="notice_text" rows="4" placeholder="গ্রাহকদের দেখানোর জন্য নোটিশ টেক্সট লিখুন..." class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold bg-slate-50 outline-none focus:border-indigo-500 focus:bg-white transition leading-relaxed resize-y" required></textarea>
            </div>

            <div class="pt-2 flex gap-2">
                <button type="button" onclick="closeAddNoticeModal()" class="w-1/3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs py-2.5 rounded-xl transition">
                    বাতিল
                </button>
                <button type="submit" class="w-2/3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-2.5 rounded-xl shadow-md shadow-indigo-600/20 transition">
                    যুক্ত করুন
                </button>
            </div>
        </form>
    </div>
</div>

<!-- একক আপডেট করার হিডেন ফর্ম -->
<form id="singleNoticeForm" method="POST" action="" class="hidden">
    <input type="hidden" name="action" value="single_update">
    <input type="hidden" name="notice_id" id="single_notice_id">
    <input type="hidden" name="notice_text" id="single_notice_text">
</form>

<!-- ডিলিট করার হিডেন ফর্ম -->
<form id="deleteNoticeForm" method="POST" action="" class="hidden">
    <input type="hidden" name="action" value="delete_notice">
    <input type="hidden" name="delete_id" id="delete_notice_id">
</form>

<style>
    /* স্মুথ স্ক্রলিং মারকুই প্রিভিউ অ্যানিমেশন */
    .marquee-preview-text {
        display: inline-block;
        padding-left: 100%;
        animation: scrollPreview 18s linear infinite;
    }
    @keyframes scrollPreview {
        0% { transform: translate(0, 0); }
        100% { transform: translate(-100%, 0); }
    }
</style>

<script>
    // লাইভ প্রিভিউ টাইপিং আপডেট
    $('.notice-textarea').on('input', function() {
        const targetId = $(this).data('target');
        const text = $(this).val();$('#' + targetId).text(text);
    });

    // নোটিশ ফিল্টারিং সার্চ
    $('#searchNoticeInput').on('input', function() {
        const query = $(this).val().trim().toLowerCase();
        let matchCount = 0;

        $('.notice-card').each(function() {
            const page = $(this).data('page') || '';
            if (page.includes(query)) {
                $(this).removeClass('hidden');
                matchCount++;
            } else {
                $(this).addClass('hidden');
            }
        });

        $('#noticeCounter').text(matchCount);

        if (matchCount === 0) {
            $('#noSearchAlert').removeClass('hidden');
        } else {
            $('#noSearchAlert').addClass('hidden');
        }
    });

    // একক নোটিশ সেভ করার মেথড
    function saveSingleNotice(id, btn) {
        const card = $(btn).closest('.notice-card');
        const text = card.find('textarea').val();

        $('#single_notice_id').val(id);
        $('#single_notice_text').val(text);
        $('#singleNoticeForm').submit();
    }

    // নোটিশ ডিলিট কনফার্মেশন
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'মুছে ফেলতে চান?',
            text: `"${name}" পেইজের নোটিশটি পুরোপুরি মুছে ফেলা হবে!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'হ্যাঁ, মুছুন!',
            cancelButtonText: 'বাতিল'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#delete_notice_id').val(id);
                $('#deleteNoticeForm').submit();
            }
        });
    }

    // মডাল কন্ট্রোল
    function openAddNoticeModal() {
        $('#addNoticeModal').removeClass('hidden');
    }
    function closeAddNoticeModal() {
        $('#addNoticeModal').addClass('hidden');
    }

    // পিএইচপি থেকে স্ট্যাটাস অ্যালার্ট রেন্ডার
    <?php if (isset($status_msg)): ?>
        Swal.fire({
            icon: '<?php echo $status_msg['type']; ?>',
            title: '<?php echo $status_msg['title']; ?>',
            text: '<?php echo $status_msg['text']; ?>',
            confirmButtonColor: '#4f46e5'
        });
    <?php endif; ?>
</script>

<?php 
include_once("footer.php"); 
$conn->close();
?>