<?php include_once("header.php");?>

<div class="card">
    <div class="card-body">
        <div class="alert alert-info text-center mb-4 fw-semibold" style="background: linear-gradient(90deg,#e0f2fe,#eff6ff); border-color: #7dd3fc; color: #0369a1;">
            ⚡ অটোমেটিক ডিপোজিট — সাথে সাথে টাকা অ্যাড হয়ে যাবে আপনার অ্যাকাউন্টে, ২৪ ঘণ্টা ওপেন
        </div>
        <div class="card">
            <div class="card-body">
                <form>
                    <?php
                    if(isset($_SESSION['Error'])){
                    ?>
                        <div class="alert alert-danger" role="alert">
                            <?php 
                            echo $_SESSION['Error'];
                            $_SESSION['Error'] = null;
                            ?>
                        </div>
                    <?php
                    } elseif (isset($_SESSION['Success'])) {
                    ?>
                        <div class="alert alert-success" role="alert">
                            <?php 
                            echo $_SESSION['Success'];
                            $_SESSION['Success'] = null;
                            ?>
                        </div>
                    <?php
                    }
                    ?>
                    
<style>
/* Full page background */
body{
    background:linear-gradient(135deg,#eef2ff 0%,#e0f2fe 45%,#f0fdf4 100%) !important;
    min-height:100vh;
}
#main-wrapper,
.page-wrapper{
    background:transparent !important;
}
.card{
    background:rgba(255,255,255,.94) !important;
    border:1px solid rgba(99,102,241,.12) !important;
    box-shadow:0 10px 30px rgba(30,41,59,.08) !important;
}
</style>

<style>
                        .recharge-box{max-width:520px;margin:auto}
                        .recharge-title{
                            font-weight:800;
                            font-size:18px;
                            background:linear-gradient(90deg,#6d28d9,#2563eb,#06b6d4,#16a34a);
                            -webkit-background-clip:text;
                            background-clip:text;
                            color:transparent;
                            margin-bottom:14px
                        }
                        .amount-input{
                            height:52px!important;
                            border:2px solid #dbeafe!important;
                            border-radius:14px!important;
                            text-align:center;
                            font-size:20px!important;
                            font-weight:700!important;
                            box-shadow:0 5px 18px rgba(37,99,235,.08)
                        }
                        .amount-input:focus{
                            border-color:#6366f1!important;
                            box-shadow:0 0 0 4px rgba(99,102,241,.12)!important
                        }
                        .quick-label{font-size:13px;font-weight:700;color:#64748b;margin:12px 0 8px}
                        .quick-amounts{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
                        .quick-btn{
                            border:0;border-radius:12px;padding:10px 6px;
                            font-weight:800;font-size:15px;cursor:pointer;
                            transition:.2s;box-shadow:0 4px 12px rgba(0,0,0,.07)
                        }
                        .quick-btn:hover{transform:translateY(-2px)}
                        .quick-100{background:#eff6ff;color:#2563eb}
                        .quick-200{background:#f0fdf4;color:#16a34a}
                        .quick-500{background:#fff7ed;color:#ea580c}
                        .pay-btn{
                            width:100%;height:48px;border:0;border-radius:13px;
                            font-weight:800;font-size:16px;color:#fff;
                            background:linear-gradient(90deg,#2563eb,#7c3aed);
                            box-shadow:0 7px 18px rgba(79,70,229,.25)
                        }
                        .method-label{font-size:13px;font-weight:700;color:#64748b;margin:14px 0 8px;text-align:center}
                        .method-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
                        .method-btn{
                            border:2px solid #dbeafe;border-radius:14px;padding:10px 8px;
                            background:#fff;cursor:pointer;transition:.2s;text-align:center;
                            display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px
                        }
                        .method-btn:hover{transform:translateY(-2px);border-color:#93c5fd;box-shadow:0 6px 14px rgba(37,99,235,.08)}
                        .method-btn.selected{border-color:#2563eb;background:#eff6ff;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
                        .method-btn img{height:28px;width:auto;object-fit:contain}
                        .method-btn span{font-size:12px;font-weight:700;color:#334155}
                    </style>

                    <div class="recharge-box">
                        <div class="recharge-title text-center">💰 দ্রুত রিচার্জ করুন</div>

                        <div class="mb-2 text-center">
                            <label for="amount" class="form-label fw-bold">টাকার পরিমাণ লিখুন</label>
                            <input type="number" min="100" step="1" class="form-control amount-input"
                                   id="amount" name="amount" placeholder="৳ 100">
                        </div>

                        <div class="quick-label text-center">দ্রুত Amount নির্বাচন করুন</div>
                        <div class="quick-amounts">
                            <button type="button" class="quick-btn quick-100" data-amount="100">৳ 100</button>
                            <button type="button" class="quick-btn quick-200" data-amount="200">৳ 200</button>
                            <button type="button" class="quick-btn quick-500" data-amount="500">৳ 500</button>
                        </div>

                        <div class="method-label">পেমেন্ট মেথড নির্বাচন করুন</div>
                        <div class="method-grid">
                            <button type="button" class="method-btn" data-method="bkash">
                                <img src="lib/img/bkash.svg" alt="bKash">
                                <span>bKash</span>
                            </button>
                            <button type="button" class="method-btn" data-method="nagad">
                                <img src="lib/img/nagad.png" alt="Nagad">
                                <span>Nagad</span>
                            </button>
                        </div>

                        <div class="mt-3">
                            <button type="button" id="payNow" class="pay-btn">⚡ এখনই রিচার্জ করুন</button>
                        </div>
                    </div>

                    <div class="alert alert-danger" style="display: none;" id="amountErr" role="alert">
                        A simple danger alert—check it out!
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card w-100">
    <div class="card-body p-4">
        <h5 class="card-title fw-semibold mb-4">পূর্ববর্তী লেনদেন</h5>
        <div class="table-responsive">
            <table class="table text-nowrap mb-0 align-middle">
                <thead class="text-dark fs-4">
                <tr>
                    <th class="border-bottom-0"><h6 class="fw-semibold mb-0">আইডি</h6></th>
                    <th class="border-bottom-0"><h6 class="fw-semibold mb-0">মেথড ও ট্রানজেকশন আইডি</h6></th>
                    <th class="border-bottom-0"><h6 class="fw-semibold mb-0">সময়</h6></th>
                    <th class="border-bottom-0"><h6 class="fw-semibold mb-0">পরিমাণ</h6></th>
                    <th class="border-bottom-0"><h6 class="fw-semibold mb-0">স্ট্যাটাস</h6></th>
                </tr>
                </thead>
                <tbody>
                    <?php
                    $history = [];
                    if (!empty($id)) {
                        $histStmt = $conn->prepare("SELECT id, method, trx_id, amount, status, created_at FROM deposit_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
                        $histStmt->bind_param("i", $id);
                        $histStmt->execute();
                        $histRes = $histStmt->get_result();
                        while ($row = $histRes->fetch_assoc()) {
                            $history[] = $row;
                        }
                        $histStmt->close();
                    }

                    if (empty($history)):
                    ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">কোনো লেনদেন পাওয়া যায়নি</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($history as $row):
                        $statusLabel = [
                            'pending'   => 'পেন্ডিং',
                            'completed' => 'সফল',
                            'failed'    => 'ব্যর্থ',
                            'cancelled' => 'বাতিল',
                        ][$row['status']] ?? ucfirst($row['status']);

                        $badgeClass = [
                            'pending'   => 'bg-warning text-dark',
                            'completed' => 'bg-success',
                            'failed'    => 'bg-danger',
                            'cancelled' => 'bg-secondary',
                        ][$row['status']] ?? 'bg-light text-dark';
                    ?>
                    <tr>
                        <td class="border-bottom-0"><h6 class="fw-semibold mb-0">#<?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?></h6></td>
                        <td class="border-bottom-0">
                            <h6 class="fw-semibold mb-1 text-uppercase"><?php echo htmlspecialchars($row['method'], ENT_QUOTES, 'UTF-8'); ?></h6>
                            <span class="fw-normal small text-muted"><?php echo htmlspecialchars($row['trx_id'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </td>
                        <td class="border-bottom-0">
                            <p class="mb-0 fw-normal"><?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($row['created_at'])), ENT_QUOTES, 'UTF-8'); ?></p>
                        </td>
                        <td class="border-bottom-0">
                            <h6 class="fw-semibold mb-0 fs-4"><?php echo htmlspecialchars($row['amount'], ENT_QUOTES, 'UTF-8'); ?> টাকা</h6>
                        </td>
                        <td class="border-bottom-0">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge <?php echo $badgeClass; ?> rounded-3 fw-semibold"><?php echo $statusLabel; ?></span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="assets/vendors/js/vendor.bundle.base.js"></script>
<script src="assets/js/off-canvas.js"></script>
<script src="assets/js/template.js"></script>
<script src="assets/js/settings.js"></script>
<script src="assets/js/hoverable-collapse.js"></script>
<script src="assets/js/todolist.js"></script>

<script>
$(document).ready(function () {
    var btn = $("#payNow");
    var amountErr = $("#amountErr");
    var selectedMethod = "";

    $(".quick-btn").click(function () {
        $("#amount").val($(this).data("amount")).focus();
        amountErr.hide();
    });

    $(".method-btn").click(function () {
        $(".method-btn").removeClass("selected");
        $(this).addClass("selected");
        selectedMethod = $(this).data("method");
        amountErr.hide();
    });

    btn.click(function () {
        var amount = $("#amount").val().trim();
        var isValid = true;

        if (amount === "") {
            amountErr.text("টাকার পরিমাণ লিখুন।").show();
            isValid = false;
        } else if (isNaN(amount)) {
            amountErr.text("টাকার পরিমাণ সঠিক নয়।").show();
            isValid = false;
        } else if (parseFloat(amount) < 100) {
            amountErr.text("সর্বনিম্ন ১০০ টাকা রিচার্জ করুন।").show();
            isValid = false;
        } else if (selectedMethod === "") {
            amountErr.text("bKash অথবা Nagad মেথড নির্বাচন করুন।").show();
            isValid = false;
        } else {
            amountErr.hide();
        }

        if (isValid) {
            var redirectUrl = "chaopao/pay.php?method=" + encodeURIComponent(selectedMethod) + "&amount=" + encodeURIComponent(amount);
            window.location.href = redirectUrl;
        }
    });
});
</script>
</body>
</html>
