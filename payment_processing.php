<?php
/**
 * Sokol Sheba — payment processing / verification waiting screen.
 *
 * The TrxID submitted on payment_gateway.php is waiting for the admin. This page
 * polls payment_status.php and jumps to the receipt as soon as it is verified.
 */
session_start();
include_once("includes/configuration.php");
include_once("includes/payment_flow.php");

$user = soko_current_user($conn);
if (!$user) {
    header("Location: index.php");
    exit;
}

$order_id = isset($_GET['order']) ? (int)$_GET['order'] : 0;
$order = soko_order_by_id($conn, $order_id, (int)$user['id']);
if (!$order) {
    header("Location: work_history.php");
    exit;
}

$state = soko_order_payment_state($conn, $order);
if ($state === 'wallet' || $state === 'verified') {
    header("Location: receipt.php?order=" . (int)$order['id']);
    exit;
}

$payment = soko_pay_latest($conn, (int)$order['id']);
if (!$payment) {
    header("Location: payment_gateway.php?order=" . (int)$order['id']);
    exit;
}

$method = soko_pay_method_meta($payment['method']);
$amount = (float)$order['price'];
$service_title = trim((string)$order['order_type']);
$receipt_no = soko_receipt_no($order);
$sender = (string)$payment['sender_number'];
$trx = (string)$payment['trx_id'];

if ($state === 'rejected') {
    $error_note = trim((string)$payment['admin_note']);
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>পেমেন্ট যাচাই চলছে — অর্ডার #<?php echo (int)$order['id']; ?> | সকল সেবা</title>
<link rel="shortcut icon" href="assets/logo.jpg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{--green:#0B7A3B;--green-dark:#07612E;--green-soft:#EAF6EE;--green-line:#D6EBDC;--red:#E51B23;--ink:#123225;--muted:#5E7466;--line:#E3EDE6;--page:#F5FAF6}
*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
body{font-family:'Hind Siliguri',system-ui,-apple-system,'Segoe UI',sans-serif;background:var(--page);color:var(--ink);font-size:15px;line-height:1.55;padding-bottom:24px}
a{text-decoration:none;color:inherit}
.topbar{background:#fff;border-bottom:1px solid var(--line)}
.topbar-inner{max-width:820px;margin:0 auto;display:flex;align-items:center;gap:12px;padding:10px 16px}
.brand{display:flex;align-items:center;gap:10px;flex:1;min-width:0}
.brand-logo{width:48px;height:48px;border-radius:50%;overflow:hidden;flex:0 0 48px;border:2px solid var(--green);background:#fff}
.brand-logo img{width:100%;height:100%;object-fit:cover}
.brand-name{font-size:23px;font-weight:700;color:var(--green);line-height:1.1;display:block;letter-spacing:-.3px}
.brand-name em{color:var(--red);font-style:normal}
.brand-tag{display:block;font-size:12.5px;color:var(--muted);font-weight:500;margin-top:1px}
.top-meta{display:flex;align-items:center;gap:14px;font-size:12.5px;color:var(--muted);font-weight:600}
.top-meta i{color:var(--green);font-size:14px}

.band{background:linear-gradient(135deg,#0E8C4B 0%,#0B7A3B 55%,#075F2D 100%);color:#fff;position:relative;overflow:hidden}
.band-inner{max-width:820px;margin:0 auto;display:flex;align-items:center;gap:14px;padding:16px}
.band .bi-ic{width:46px;height:46px;border-radius:14px;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.32);display:flex;align-items:center;justify-content:center;font-size:20px;flex:0 0 46px}
.band h1{font-size:22px;font-weight:700}
.band p{font-size:13.5px;opacity:.92}
.band .shield{position:absolute;right:6px;top:-4px;font-size:110px;opacity:.10}
.band .barcode{position:absolute;right:0;bottom:0;width:200px;height:5px;background:repeating-linear-gradient(90deg,#fff 0 10px,rgba(255,255,255,.25) 10px 14px,#E51B23 14px 20px);opacity:.55}

.shell{max-width:820px;margin:0 auto;padding:14px}
.card{background:#fff;border:1px solid var(--green-line);border-radius:14px;margin-bottom:12px;box-shadow:0 4px 14px rgba(11,122,59,.04)}
.card-head{display:flex;align-items:center;gap:9px;padding:12px 15px;border-bottom:1px solid var(--line)}
.card-head .ch-ic{width:28px;height:28px;border-radius:9px;background:var(--green);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12.5px}
.card-head h2{font-size:16px;font-weight:700}
.card-body{padding:14px 15px}

.sum-grid{display:flex;gap:12px;flex-wrap:wrap;align-items:center}
.sum-rows{flex:1;min-width:210px}
.sum-line{display:flex;align-items:center;gap:8px;font-size:14px;padding:5px 0}
.sum-line .k{color:var(--muted);min-width:88px}
.sum-line .v{font-weight:600}
.amount-box{margin-left:auto;background:var(--green-soft);border:1px solid #D9EDDF;border-radius:12px;padding:11px 18px;text-align:center}
.amount-box small{display:block;font-size:12px;color:var(--green-dark);font-weight:600}
.amount-box b{display:block;font-size:22px;color:var(--green-dark);font-weight:700}

.mfs-line{display:flex;align-items:center;gap:11px;border:1px solid var(--line);border-radius:11px;padding:10px 12px}
.mfs-line .ml-logo{width:56px;height:38px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:15px;background:#FDF1F6;flex:0 0 56px;overflow:hidden;padding:3px}
.mfs-line .ml-logo img{max-width:100%;max-height:100%;object-fit:contain}
.mfs-line .ml-txt b{display:block;font-size:14px}
.mfs-line .ml-txt small{display:block;font-size:12px;color:var(--muted)}
.mfs-line .ml-num{margin-left:auto;font-size:14px;font-weight:700;display:flex;align-items:center;gap:6px}
.mfs-line .ml-num i{color:var(--green);font-size:12.5px}
.mk-bkash{color:#E2136E;background:#FDF1F6}
.mk-nagad{color:#F58220;background:#FFF5EC}
.mk-rocket{color:#8C3494;background:#F8F1F9}
.mk-upay{color:#1C4E9E;background:#EEF4FD}

.spin-box{text-align:center;padding:26px 16px 22px}
.spinner{width:64px;height:64px;border-radius:50%;border:6px solid #E4F1E8;border-top-color:var(--green);margin:0 auto 14px;animation:spin 1s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.spin-box h3{font-size:20px;font-weight:700;color:var(--green-dark)}
.spin-box p{font-size:13.5px;color:var(--muted);margin-top:4px}
.secure-line{display:inline-flex;align-items:center;gap:8px;background:#F5FAF6;border:1px solid #E1EFE6;border-radius:10px;padding:8px 14px;margin-top:14px;font-size:12.5px;font-weight:600;color:var(--green-dark)}

.steps4{display:flex;position:relative;padding:8px 4px 0}
.steps4:before{content:"";position:absolute;left:13%;right:13%;top:26px;height:2px;background:#DCE7E0}
.step{position:relative;z-index:1;flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;text-align:center}
.step .dot{width:30px;height:30px;border-radius:50%;background:#C9D6CD;color:#fff;font-size:12.5px;font-weight:700;display:flex;align-items:center;justify-content:center;border:3px solid #fff}
.step.done .dot{background:#1E9C55}
.step.now .dot{background:var(--green-dark);box-shadow:0 0 0 3px rgba(11,122,59,.15)}
.step .lbl{font-size:12.3px;font-weight:600;color:var(--muted);line-height:1.2}
.step .sub{font-size:11px;color:#98AAA0}
.step.done .lbl,.step.now .lbl{color:var(--ink);font-weight:700}

.note{display:flex;gap:10px;background:#F4F9F6;border:1px solid #E1EFE6;border-radius:12px;padding:12px 13px}
.note i{color:var(--green);font-size:14px;margin-top:2px}
.note b{display:block;font-size:13.5px;margin-bottom:4px}
.note ul{margin:0;padding-left:16px}
.note li{font-size:12.4px;color:var(--muted);margin-bottom:3px}

.alert{border-radius:11px;padding:11px 13px;font-size:13.5px;font-weight:600;margin-bottom:12px;display:flex;gap:9px;align-items:flex-start}
.alert.bad{background:#FEF1F1;border:1px solid #F7D2D2;color:#A81616}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;height:48px;padding:0 20px;border-radius:12px;border:0;font-size:15px;font-weight:700;cursor:pointer}
.btn.primary{background:var(--green);color:#fff}
.btn.ghost{background:#fff;border:1.5px solid var(--green-line);color:var(--green-dark)}

.foot-strip{background:linear-gradient(135deg,#0E8C4B,#075F2D);color:#fff;border-radius:14px;padding:13px 15px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:14px}
.foot-strip .fs{display:flex;align-items:center;gap:9px;font-size:13px;font-weight:600}
.foot-strip i{font-size:16px}
.foot-strip small{display:block;font-size:11px;opacity:.8;font-weight:500}
.foot-note{text-align:center;font-size:11.8px;color:var(--muted);padding:12px 0 4px;display:flex;align-items:center;justify-content:center;gap:8px}
.foot-note i{color:var(--green)}
@media (max-width:640px){
  .brand-name{font-size:20px}.brand-tag{font-size:11px}.top-meta{display:none}
  .band h1{font-size:19px}.shell{padding:12px}
  .amount-box{margin-left:0;width:100%}
}
</style>
</head>
<body>

<header class="topbar">
  <div class="topbar-inner">
    <a href="dashboard.php" class="brand">
      <span class="brand-logo"><img src="assets/logo.jpg" alt="সকল সেবা"></span>
      <span>
        <span class="brand-name">সকল <em>সেবা</em></span>
        <span class="brand-tag">সব কাজ, এক প্ল্যাটফর্ম</span>
      </span>
    </a>
    <div class="top-meta">
      <span><i class="fa-solid fa-shield-halved"></i> নিরাপদ লেনদেন</span>
      <span><i class="fa-solid fa-headset"></i> ২৪/৭ সাপোর্ট</span>
    </div>
  </div>
</header>

<div class="band">
  <div class="band-inner">
    <span class="bi-ic"><i class="fa-solid fa-mobile-screen"></i></span>
    <span>
      <h1><?php echo $state === 'rejected' ? 'পেমেন্ট যাচাই করা যায়নি' : 'পেমেন্ট যাচাই চলছে'; ?></h1>
      <p><?php echo $state === 'rejected' ? 'আপনার জমা দেওয়া তথ্যে সমস্যা পাওয়া গেছে।' : 'আপনার পেমেন্ট সফলভাবে প্রসেস হচ্ছে...'; ?></p>
    </span>
  </div>
  <i class="fa-solid fa-shield-halved shield"></i>
  <span class="barcode"></span>
</div>

<main class="shell">

  <?php if ($state === 'rejected'): ?>
    <div class="alert bad">
      <i class="fa-solid fa-circle-xmark" style="margin-top:2px"></i>
      <div>
        <div>আপনার TrxID <b><?php echo soko_h($trx); ?></b> যাচাই করা যায়নি।</div>
        <?php if ($error_note !== ''): ?>
          <div>কারণ: <?php echo soko_h($error_note); ?></div>
        <?php else: ?>
          <div>টাকা কেটে নেওয়া হয়ে থাকলে সাপোর্টে যোগাযোগ করুন।</div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <section class="card">
    <div class="card-head">
      <span class="ch-ic"><i class="fa-solid fa-file-invoice"></i></span>
      <h2>অর্ডার বিবরণ</h2>
    </div>
    <div class="card-body">
      <div class="sum-grid">
        <div class="sum-rows">
          <div class="sum-line"><span class="k">সেবা নাম</span><span class="v"><?php echo soko_h($service_title); ?></span></div>
          <div class="sum-line"><span class="k">অর্ডার নং</span><span class="v">#<?php echo soko_h($receipt_no); ?></span></div>
          <div class="sum-line"><span class="k">তারিখ</span><span class="v"><?php echo soko_bn_date($order['created_at']); ?></span></div>
        </div>
        <div class="amount-box">
          <small>মোট পরিমাণ</small>
          <b><?php echo soko_money($amount); ?></b>
        </div>
      </div>
    </div>
  </section>

  <section class="card">
    <div class="card-head">
      <span class="ch-ic"><i class="fa-solid fa-mobile-screen-button"></i></span>
      <h2>মোবাইল ব্যাংকিং</h2>
    </div>
    <div class="card-body">
      <div class="mfs-line">
        <?php $proc_logo = function_exists('soko_pay_method_logo') ? soko_pay_method_logo(isset($payment['method']) ? $payment['method'] : '') : ''; ?>
        <span class="ml-logo <?php echo soko_h($method['css']); ?>">
          <?php if ($proc_logo !== ''): ?>
            <img src="<?php echo soko_h($proc_logo); ?>" alt="<?php echo soko_h($method['name']); ?>">
          <?php else: ?>
            <?php echo soko_h($method['name']); ?>
          <?php endif; ?>
        </span>
        <span class="ml-txt">
          <b>আপনি <?php echo soko_h($method['name']); ?> এর মাধ্যমে পেমেন্ট করছেন</b>
          <small>TrxID: <?php echo soko_h($trx); ?></small>
        </span>
        <span class="ml-num"><i class="fa-solid fa-phone"></i> <?php echo soko_bn_digits(soko_h($sender)); ?></span>
      </div>
    </div>
  </section>

  <section class="card">
    <div class="card-body">
      <?php if ($state === 'rejected'): ?>
        <div class="spin-box" style="padding-bottom:14px">
          <div style="font-size:52px;color:#D94A4A;margin-bottom:8px"><i class="fa-solid fa-circle-xmark"></i></div>
          <h3 style="color:#A81616">পেমেন্ট বাতিল হয়েছে</h3>
          <p>সঠিক TrxID দিয়ে আবার পেমেন্ট তথ্য জমা দিন।</p>
          <div style="margin-top:14px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
            <a class="btn primary" href="payment_gateway.php?order=<?php echo (int)$order['id']; ?>"><i class="fa-solid fa-rotate-right"></i> আবার পেমেন্ট করুন</a>
            <a class="btn ghost" href="support.php"><i class="fa-solid fa-headset"></i> সাপোর্ট</a>
          </div>
        </div>
      <?php else: ?>
        <div class="spin-box">
          <div class="spinner"></div>
          <h3>পেমেন্ট প্রসেসিং...</h3>
          <p>দয়া করে অপেক্ষা করুন। আপনার পেমেন্ট তথ্য নিরাপদে যাচাই করা হচ্ছে।</p>
          <div class="secure-line"><i class="fa-solid fa-shield-halved"></i> এই পেমেন্ট সম্পূর্ণ নিরাপদ এবং এনক্রিপ্টেড</div>
        </div>
      <?php endif; ?>

      <ol class="steps4">
        <li class="step done"><span class="dot"><i class="fa-solid fa-check"></i></span><span class="lbl">পেমেন্ট তথ্য</span><span class="sub">সম্পন্ন</span></li>
        <li class="step <?php echo $state === 'rejected' ? '' : 'now'; ?>"><span class="dot"><?php echo $state === 'rejected' ? '!' : '২'; ?></span><span class="lbl">পেমেন্ট যাচাই</span><span class="sub"><?php echo $state === 'rejected' ? 'ব্যর্থ' : 'চলছে'; ?></span></li>
        <li class="step"><span class="dot">৩</span><span class="lbl">কনফার্মেশন</span><span class="sub">অপেক্ষমাণ</span></li>
        <li class="step"><span class="dot">৪</span><span class="lbl">সফল</span><span class="sub">যথা সময়ে</span></li>
      </ol>
    </div>
  </section>

  <div class="note">
    <i class="fa-solid fa-circle-info"></i>
    <div>
      <b>গুরুত্বপূর্ণ নোট</b>
      <ul>
        <li>পেমেন্ট যাচাই হতে কয়েক সেকেন্ড থেকে কয়েক মিনিট সময় লাগতে পারে।</li>
        <li>পেমেন্ট সফল হলে আপনি একটি SMS/নোটিফিকেশন পাবেন।</li>
        <li>যদি ১৫ মিনিট পরও কনফার্মেশন না পান, তাহলে আমাদের সাথে যোগাযোগ করুন।</li>
      </ul>
    </div>
  </div>

  <div class="foot-strip">
    <span class="fs"><i class="fa-solid fa-headset"></i><span>সাপোর্ট হেল্পলাইন<small>যোগাযোগ করুন যেকোনো সময়</small></span></span>
    <span class="fs"><i class="fa-solid fa-shield-halved"></i><span>নিরাপদ লেনদেন<small>আপনার তথ্য ১০০% নিরাপদ</small></span></span>
  </div>
  <div class="foot-note"><i class="fa-solid fa-lock"></i> Powered by সকল সেবা</div>

</main>

<?php if ($state !== 'rejected'): ?>
<script>
(function () {
  var endpoint = 'payment_status.php?order=<?php echo (int)$order['id']; ?>';
  var tries = 0;

  function check() {
    tries++;
    fetch(endpoint, { headers: { 'Accept': 'application/json' } })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data && data.state === 'verified') {
          window.location.href = 'receipt.php?order=<?php echo (int)$order['id']; ?>&paid=1';
          return;
        }
        if (data && data.state === 'rejected') {
          window.location.reload();
          return;
        }
        if (tries < 120) { setTimeout(check, 6000); }
      })
      .catch(function () {
        if (tries < 120) { setTimeout(check, 9000); }
      });
  }

  setTimeout(check, 5000);
})();
</script>
<?php endif; ?>
</body>
</html>
