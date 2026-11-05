<?php 
include_once("header.php");

$save_msg = "";
$save_error = "";

$selected_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_balance'])) {
    $target_user_id = intval($_POST['user_id'] ?? 0);
    $action = trim($_POST['action'] ?? 'add');
    $amount = floatval($_POST['amount'] ?? 0);
    $note = trim($_POST['note'] ?? '');

    if ($target_user_id <= 0 || $amount <= 0) {
        $save_error = "সঠিক ব্যবহারকারী এবং ব্যালেন্সের পরিমাণ উল্লেখ করুন।";
    } else {
        $check_stmt = $conn->prepare("SELECT id, name, balance FROM users WHERE id = ? LIMIT 1");
        $check_stmt->bind_param("i", $target_user_id);
        $check_stmt->execute();
        $user_res = $check_stmt->get_result();

        if ($user_res->num_rows === 0) {
            $save_error = "ব্যবহারকারী খুঁজে পাওয়া যায়নি!";
        } else {
            $user_data = $user_res->fetch_assoc();
            $current_bal = floatval($user_data['balance']);
            
            if ($action === 'deduct') {
                if ($current_bal < $amount) {
                    $save_error = "ব্যবহারকারীর পর্যাপ্ত ব্যালেন্স নেই! বর্তমান ব্যালেন্স: ৳ " . number_format($current_bal, 2);
                } else {
                    $new_bal = $current_bal - $amount;
                }
            } else {
                $new_bal = $current_bal + $amount;
            }

            if (empty($save_error)) {
                $up_stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
                $up_stmt->bind_param("di", $new_bal, $target_user_id);
                if ($up_stmt->execute()) {
                    $selected_user_id = $target_user_id;
                    $save_msg = "ব্যালেন্স সফলভাবে আপডেট করা হয়েছে! নতুন ব্যালেন্স: ৳ " . number_format($new_bal, 2);
                } else {
                    $save_error = "ব্যালেন্স আপডেট করতে ব্যর্থ হয়েছে।";
                }
                $up_stmt->close();
            }
        }
        $check_stmt->close();
    }
}

$users_list = [];
$u_query = $conn->query("SELECT id, name, mobile, balance FROM users ORDER BY name ASC");
if ($u_query && $u_query->num_rows > 0) {
    while ($urow = $u_query->fetch_assoc()) {
        $users_list[] = $urow;
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>

<style>
    body {
        background: #f1f5f9 !important;
        font-family: 'Plus Jakarta Sans', 'Hind Siliguri', sans-serif !important;
        color: #1e293b;
    }
    .balance-box {
        max-width: 680px;
        margin: 0 auto 30px;
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        padding: 30px 24px;
    }
    .form-input-box {
        width: 100%;
        border-radius: 14px;
        border: 1.5px solid #cbd5e1;
        padding: 11px 16px;
        font-size: 14px;
        outline: none;
        transition: all 0.2s ease;
        background: #ffffff;
    }
    .form-input-box:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }
    .action-card {
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s ease;
    }
    .action-card.active-add {
        border-color: #10b981;
        background: #ecfdf5;
        color: #065f46;
        font-weight: 700;
    }
    .action-card.active-deduct {
        border-color: #f43f5e;
        background: #fff1f2;
        color: #9f1239;
        font-weight: 700;
    }
    .btn-submit-balance {
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        color: #ffffff;
        font-weight: 700;
        padding: 13px 20px;
        border-radius: 14px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.25);
        cursor: pointer;
        transition: transform 0.2s;
    }
    .btn-submit-balance:hover {
        transform: translateY(-2px);
    }
</style>

<div class="container-fluid px-3 px-md-4 py-4">

    <div class="balance-box">
        
        <div class="flex items-center justify-between border-b pb-4 mb-5">
            <div>
                <h3 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                    <span class="text-emerald-600"><i class="fa-solid fa-money-bill-transfer"></i></span> ব্যালেন্স ম্যানেজমেন্ট
                </h3>
                <p class="text-xs text-slate-500 mt-1">ব্যবহারকারীর অ্যাকাউন্টে ব্যালেন্স যোগ অথবা কর্তন করুন</p>
            </div>
            <a href="users.php" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 px-3.5 py-2 rounded-xl font-bold flex items-center gap-1.5 transition">
                <i class="fa-solid fa-users"></i> গ্রাহক তালিকা
            </a>
        </div>

        <?php if(!empty($save_msg)): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl mb-4 text-xs font-bold flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i> <?php echo $save_msg; ?>
            </div>
        <?php elseif(!empty($save_error)): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl mb-4 text-xs font-bold flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm"></i> <?php echo $save_error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">ব্যবহারকারী নির্বাচন করুন <span class="text-rose-500">*</span></label>
                <select name="user_id" id="userSelect" class="form-input-box" required onchange="updateUserDisplay()">
                    <option value="">-- গ্রাহক সিলেক্ট করুন --</option>
                    <?php foreach ($users_list as $u): ?>
                        <option value="<?php echo $u['id']; ?>" data-balance="<?php echo htmlspecialchars($u['balance']); ?>" <?php echo ($selected_user_id == $u['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($u['name']); ?> (<?php echo htmlspecialchars($u['mobile']); ?>) — ব্যালেন্স: ৳ <?php echo number_format($u['balance'], 2); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="userBalanceDisplayCard" class="bg-indigo-50/60 border border-indigo-100 rounded-2xl p-3.5 mb-4 flex items-center justify-between" style="display: none;">
                <div>
                    <span class="text-[11px] font-bold text-slate-500">বর্তমান ব্যালেন্স</span>
                    <h4 class="text-lg font-black text-indigo-700" id="currentBalanceText">৳ 0.00</h4>
                </div>
                <div class="text-indigo-500 text-2xl">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">অ্যাকশন টাইপ</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="action-card active-add" id="actionAddCard">
                        <input type="radio" name="action" value="add" checked class="hidden" onchange="switchAction('add')">
                        <i class="fa-solid fa-plus-circle"></i>
                        <span class="text-xs">ব্যালেন্স যোগ (+)</span>
                    </label>
                    <label class="action-card" id="actionDeductCard">
                        <input type="radio" name="action" value="deduct" class="hidden" onchange="switchAction('deduct')">
                        <i class="fa-solid fa-minus-circle"></i>
                        <span class="text-xs">ব্যালেন্স কর্তন (-)</span>
                    </label>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">টাকার পরিমাণ <span class="text-rose-500">*</span></label>
                <input type="number" step="0.01" min="1" name="amount" class="form-input-box text-lg font-black text-slate-800" placeholder="0.00" required>
            </div>

            <div class="mb-5">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">কারণ বা রেফারেন্স নোট (ঐচ্ছিক)</label>
                <input type="text" name="note" class="form-input-box" placeholder="যেমন: ম্যানুয়াল রিচার্জ / পেমেন্ট সমন্বয়">
            </div>

            <button type="submit" name="update_balance" class="btn-submit-balance w-full">
                <i class="fa-solid fa-circle-check"></i> ব্যালেন্স আপডেট নিশ্চিত করুন
            </button>
        </form>
    </div>

</div>

<script>
function switchAction(type) {
    const addCard = document.getElementById('actionAddCard');
    const deductCard = document.getElementById('actionDeductCard');

    if (type === 'add') {
        addCard.className = 'action-card active-add';
        deductCard.className = 'action-card';
    } else {
        addCard.className = 'action-card';
        deductCard.className = 'action-card active-deduct';
    }
}

function updateUserDisplay() {
    const select = document.getElementById('userSelect');
    const displayCard = document.getElementById('userBalanceDisplayCard');
    const balanceText = document.getElementById('currentBalanceText');
    const selectedOption = select.options[select.selectedIndex];

    if (selectedOption && selectedOption.value) {
        const bal = parseFloat(selectedOption.getAttribute('data-balance') || 0);
        balanceText.innerText = '৳ ' + bal.toFixed(2);
        displayCard.style.display = 'flex';
    } else {
        displayCard.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    updateUserDisplay();
});
</script>

<?php include_once("footer.php"); ?>