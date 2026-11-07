<?php
/**
 * Sokol Sheba — payment gateway (manual send money + TrxID).
 *
 * The user picks bKash / Nagad / Rocket / Upay, sends the money to our number,
 * then submits the sender number and the TrxID here. The claim goes to
 * order_payments as "pending" and the admin verifies it.
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
if ($state === 'pending') {
    header("Location: payment_processing.php?order=" . (int)$order['id']);
    exit;
}

$methods = soko_pay_methods();
$numbers = soko_pay_merchant_numbers($conn);
$amount = (float)$order['price'];
$service_title = trim((string)$order['order_type']);
$service_row = soko_get_service($conn, 0, $service_title);
$category = ($service_row && trim((string)$service_row['category']) !== '') ? trim((string)$service_row['category']) : 'সেবা';
$receipt_no = soko_receipt_no($order);

$errors = array();
$selected_method = 'bkash';
$sender_number = '';
$trx_id = '';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_now'])) {
    $selected_method = isset($_POST['method']) ? strtolower(trim($_POST['method'])) : 'bkash';
    $sender_number = isset($_POST['sender_number']) ? trim($_POST['sender_number']) : '';
    $trx_id = isset($_POST['trx_id']) ? trim($_POST['trx_id']) : '';

    if (!isset($_POST['confirm_paid'])) {
        $errors[] = 'পেমেন্ট সম্পন্ন করার পর "পেমেন্টের তথ্য যাচাই করেছি" বক্সে টিক দিন।';
    }

    $slip_path = '';
    if (isset($_FILES['slip']) && is_array($_FILES['slip']) && $_FILES['slip']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload = soko_handle_upload($_FILES['slip'], (int)$user['id'], 'slip');
        if ($upload['error'] !== '') {
            $errors[] = 'স্ক্রিনশট: ' . $upload['error'];
        } else {
            $slip_path = $upload['path'];
        }
    }

    if (count($errors) === 0) {
        $result = soko_pay_create($conn, (int)$order['id'], (int)$user['id'], $selected_method, $sender_number, $trx_id, $amount, $slip_path);
        if ($result['ok']) {
            header("Location: payment_processing.php?order=" . (int)$order['id']);
            exit;
        }
        $errors[] = $result['error'];
    }
}

$active_meta = soko_pay_method_meta($selected_method);
$active_number = isset($numbers[$selected_method]) ? $numbers[$selected_method] : '';
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>পেমেন্ট গেটওয়ে — অর্ডার #<?php echo (int)$order['id']; ?> | সকল সেবা</title>
<link rel="shortcut icon" href="assets/logo.jpg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{--green:#0B7A3B;--green-dark:#07612E;--green-soft:#EAF6EE;--green-line:#D6EBDC;--red:#E51B23;--ink:#123225;--muted:#5E7466;--line:#E3EDE6;--page:#F5FAF6}
*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
body{font-family:'Hind Siliguri',system-ui,-apple-system,'Segoe UI',sans-serif;background:var(--page);color:var(--ink);font-size:15px;line-height:1.55;padding-bottom:24px}
a{text-decoration:none;color:inherit}
button{font-family:inherit}

/* header */
.topbar{background:#fff;border-bottom:1px solid var(--line)}
.topbar-inner{max-width:820px;margin:0 auto;display:flex;align-items:center;gap:12px;padding:10px 16px}
.brand{display:flex;align-items:center;gap:10px;flex:1;min-width:0}
.brand-logo{width:48px;height:48px;border-radius:50%;overflow:hidden;flex:0 0 48px;border:2px solid var(--green);background:#fff}
.brand-logo img{width:100%;height:100%;object-fit:cover}
.brand-name{font-size:23px;font-weight:700;color:var(--green);line-height:1.1;display:block;letter-spacing:-.3px}
.brand-name em{color:var(--red);font-style:normal}
.brand-tag{display:block;font-size:12.5px;color:var(--muted);font-weight:500;margin-top:1px}
.top-meta{display:flex;align-items:center;gap:14px;font-size:12.5px;color:var(--muted);font-weight:600}
.top-meta .tm{display:flex;align-items:center;gap:6px}
.top-meta i{color:var(--green);font-size:14px}

/* band */
.band{background:linear-gradient(135deg,#0E8C4B 0%,#0B7A3B 55%,#075F2D 100%);color:#fff;position:relative;overflow:hidden}
.band-inner{max-width:820px;margin:0 auto;display:flex;align-items:center;gap:14px;padding:16px}
.band .bi-ic{width:46px;height:46px;border-radius:14px;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.32);display:flex;align-items:center;justify-content:center;font-size:20px;flex:0 0 46px}
.band h1{font-size:22px;font-weight:700;letter-spacing:-.2px}
.band p{font-size:13.5px;opacity:.92}
.band .shield{position:absolute;right:-10px;top:-6px;font-size:120px;opacity:.10}
.band .barcode{position:absolute;right:0;bottom:0;width:200px;height:5px;background:repeating-linear-gradient(90deg,#fff 0 10px,rgba(255,255,255,.25) 10px 14px,#E51B23 14px 20px);opacity:.55}

.shell{max-width:820px;margin:0 auto;padding:14px}

/* stepper */
.steps{background:#fff;border:1px solid var(--green-line);border-radius:14px;padding:13px 8px;display:flex;position:relative;margin-bottom:12px}
.steps:before{content:"";position:absolute;left:16%;right:16%;top:29px;height:2px;background:#DCE7E0}
.step{position:relative;z-index:1;flex:1;display:flex;flex-direction:column;align-items:center;gap:5px;text-align:center}
.step .dot{width:32px;height:32px;border-radius:50%;background:#C9D6CD;color:#fff;font-weight:700;font-size:13.5px;display:flex;align-items:center;justify-content:center;border:3px solid #fff}
.step .lbl{font-size:12.5px;font-weight:600;color:var(--muted);line-height:1.2}
.step.done .dot{background:#1E9C55}
.step.now .dot{background:var(--green-dark);box-shadow:0 0 0 3px rgba(11,122,59,.15)}
.step.done .lbl,.step.now .lbl{color:var(--ink);font-weight:700}

/* cards */
.card{background:#fff;border:1px solid var(--green-line);border-radius:14px;margin-bottom:12px;box-shadow:0 4px 14px rgba(11,122,59,.04)}
.card-head{display:flex;align-items:center;gap:9px;padding:12px 15px;border-bottom:1px solid var(--line)}
.card-head .ch-ic{width:28px;height:28px;border-radius:9px;background:var(--green);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12.5px}
.card-head h2{font-size:16px;font-weight:700}
.card-body{padding:14px 15px}

/* merchant box */
.merchant{display:flex;align-items:center;gap:11px;background:#F6FBF7;border:1px solid #DCEFE2;border-radius:11px;padding:10px 12px;margin-bottom:12px;flex-wrap:wrap}
.merchant .m-logo{width:42px;height:42px;border-radius:10px;background:#fff;border:1px solid var(--line);display:flex;align-items:center;justify-content:center;overflow:hidden;flex:0 0 42px}
.merchant .m-logo img{width:100%;height:100%;object-fit:cover}
.merchant .m-txt b{display:block;font-size:14.5px}
.merchant .m-txt small{display:block;font-size:11.8px;color:var(--muted)}
.merchant .m-meta{margin-left:auto;text-align:right;font-size:12px;color:var(--muted);line-height:1.5}
.merchant .m-meta b{display:block;color:var(--ink);font-size:12.8px}

.total-row{display:flex;align-items:center;justify-content:space-between;background:#F3F8F4;border-radius:10px;padding:11px 13px;font-size:14.5px;font-weight:600;color:var(--muted)}
.total-row b{font-size:21px;color:var(--green-dark);font-weight:700}

/* send money box */
.send-box{border:1px solid #DCEFE2;background:#FBFEFC;border-radius:12px;padding:12px;margin-bottom:14px}
.send-box .sb-title{font-size:13px;font-weight:700;color:var(--green-dark);display:flex;align-items:center;gap:7px;margin-bottom:6px}
.send-box .sb-num{display:flex;align-items:center;justify-content:space-between;gap:10px;background:#fff;border:1px dashed #BFDCC8;border-radius:9px;padding:9px 12px}
.send-box .sb-num b{font-size:19px;letter-spacing:1px}
.send-box .sb-num small{display:block;font-size:11px;color:var(--muted);font-weight:600}
.copy-btn{border:0;background:var(--green-soft);color:var(--green-dark);font-size:12px;font-weight:700;padding:7px 12px;border-radius:8px;cursor:pointer;display:flex;align-items:center;gap:5px}
.copy-btn:hover{background:#DDEFE3}
.send-box ol{margin:8px 0 0;padding-left:18px}
.send-box li{font-size:12.5px;color:var(--muted);margin-bottom:3px}

/* gateway grid */
.pay-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:14px}
.pay-card{position:relative;border:1.6px solid var(--line);border-radius:13px;background:#fff;padding:14px 10px 11px;text-align:center;cursor:pointer;transition:.15s}
.pay-card:hover{border-color:#BEDCC8}
.pay-card input{position:absolute;opacity:0;pointer-events:none}
.pay-card .tick{position:absolute;top:9px;right:9px;width:19px;height:19px;border-radius:50%;border:1.8px solid #CBD8D0;display:flex;align-items:center;justify-content:center;color:transparent;font-size:10px;background:#fff}
.pay-card.is-on{border-color:var(--green);background:#F7FCF8;box-shadow:0 4px 14px rgba(11,122,59,.10)}
.pay-card.is-on .tick{background:var(--green);border-color:var(--green);color:#fff}
.pay-card .mark{height:44px;display:flex;align-items:center;justify-content:center;gap:4px;font-weight:800;font-size:19px;letter-spacing:-.3px}
.pay-card .mark .glyph{font-size:15px}
.pay-card .mark img{max-height:40px;max-width:96px;object-fit:contain}
.pay-card .mk-name{letter-spacing:.2px}
.pay-card .mk-name{display:block;font-size:12.8px;font-weight:600;color:var(--ink);margin-top:4px}
.field .opt-tag{font-size:10.5px;font-weight:700;color:#0B7A3B;background:#EAF6EE;border-radius:99px;padding:2px 7px;vertical-align:middle}
.slip-up{display:flex;flex-direction:column;align-items:center;gap:2px;border:1.6px dashed #C9DED2;border-radius:12px;padding:14px 10px;background:#FAFDFB;cursor:pointer;text-align:center;transition:.15s}
.slip-up:hover{border-color:#0B7A3B;background:#F4FBF6}
.slip-up i{font-size:20px;color:#0B7A3B}
.slip-up b{font-size:13px;color:#123225}
.slip-up small{font-size:11px;color:#5E7466}
.slip-up .slip-picked{font-size:11px;color:#8AA396;margin-top:2px;word-break:break-all}
.slip-up.has-file{border-color:#0B7A3B;border-style:solid;background:#EFF8F2}
.slip-up.has-file .slip-picked{color:#0B7A3B;font-weight:600}
.mk-bkash{color:#E2136E}
.mk-nagad{color:#F58220}
.mk-rocket{color:#8C3494}
.mk-upay .u1{color:#1C4E9E}
.mk-upay .u2{color:#F5A623}

/* labels + inputs */
.field{margin-bottom:12px}
.field label{display:block;font-size:13.5px;font-weight:600;margin-bottom:5px}
.field label .star{color:var(--red)}
.field .hint{font-size:11.8px;color:var(--muted);margin-top:4px}
.inp{position:relative}
.inp > i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#8AA396;font-size:13.5px}
.inp input{width:100%;height:46px;border:1px solid var(--line);border-radius:10px;padding:0 12px 0 38px;font-size:14.5px;font-family:inherit;outline:none;color:var(--ink)}
.inp input:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(11,122,59,.09)}
.inp input::placeholder{color:#9DB0A4}
.check-row{display:flex;align-items:flex-start;gap:9px;font-size:13px;color:var(--ink);font-weight:500;margin:4px 0 14px;cursor:pointer}
.check-row input{appearance:none;width:20px;height:20px;border-radius:5px;border:1.8px solid #C3D5C9;flex:0 0 20px;cursor:pointer;position:relative;margin-top:1px}
.check-row input:checked{background:var(--green);border-color:var(--green)}
.check-row input:checked:after{content:"\f00c";font-family:"Font Awesome 6 Free";font-weight:900;font-size:11px;color:#fff;position:absolute;left:4px;top:1px}

.btn-pay{width:100%;height:52px;border:0;border-radius:12px;background:var(--green);color:#fff;font-size:16.5px;font-weight:700;display:flex;align-items:center;justify-content:center;gap:9px;cursor:pointer;box-shadow:0 6px 16px rgba(11,122,59,.24)}
.btn-pay:hover{background:var(--green-dark)}
.btn-pay:disabled{opacity:.7;cursor:not-allowed}

.alert{border-radius:11px;padding:10px 12px;font-size:13.5px;font-weight:600;margin-bottom:12px;display:flex;gap:8px;align-items:flex-start;background:#FEF1F1;border:1px solid #F7D2D2;color:#A81616}

.note{display:flex;gap:10px;background:#F4F9F6;border:1px solid #E1EFE6;border-radius:12px;padding:12px 13px}
.note i{color:var(--green);font-size:14px;margin-top:2px}
.note b{display:block;font-size:13.5px;margin-bottom:4px}
.note ul{margin:0;padding-left:16px}
.note li{font-size:12.4px;color:var(--muted);margin-bottom:3px}

/* footer */
.page-foot{max-width:820px;margin:0 auto;padding:0 14px}
.foot-strip{background:linear-gradient(135deg,#0E8C4B,#075F2D);color:#fff;border-radius:14px;padding:13px 15px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:14px}
.foot-strip .fs{display:flex;align-items:center;gap:9px;font-size:13px;font-weight:600}
.foot-strip i{font-size:16px;opacity:.95}
.foot-strip small{display:block;font-size:11px;opacity:.8;font-weight:500}
.foot-note{text-align:center;font-size:11.8px;color:var(--muted);padding:12px 0 4px;display:flex;align-items:center;justify-content:center;gap:8px;flex-wrap:wrap}
.foot-note i{color:var(--green)}

@media (max-width:640px){
  .brand-name{font-size:20px}
  .brand-tag{font-size:11px}
  .top-meta{display:none}
  .band h1{font-size:19px}
  .band p{font-size:12.5px}
  .shell{padding:12px}
  .pay-card .mark{font-size:17px}
  .steps:before{top:27px}
}
@media print{.no-print{display:none!important}}

/* ============ compact, one-screen layout ============ */
body{font-size:14px;line-height:1.44;padding-bottom:12px}
.topbar-inner{padding:8px 14px;gap:10px}
.brand-logo{width:40px;height:40px;flex:0 0 40px;border-width:2px}
.brand-name{font-size:20px}
.brand-tag{font-size:11.5px;margin-top:0}
.top-meta{font-size:11.5px;gap:12px}
.band-inner{padding:11px 14px;gap:11px}
.band .bi-ic{width:38px;height:38px;flex:0 0 38px;font-size:16px;border-radius:11px}
.band h1{font-size:18px}
.band p{font-size:12px}
.band .shield{font-size:86px}
.shell{padding:11px 12px;max-width:760px}
.alert{padding:9px 11px;font-size:12.5px;margin-bottom:9px}

/* method cards */
.pay-grid{grid-template-columns:repeat(2,1fr);gap:8px;margin-bottom:10px}
.pay-card{padding:9px 8px 7px;border-radius:11px}
.pay-card .mark{height:30px}
.pay-card .mark img{max-height:28px;max-width:82px}
.pay-card .mark .glyph{display:none}
.pay-card .mk-name{font-size:11px;margin-top:2px;color:var(--muted)}
.pay-card .tick{top:6px;right:6px;width:16px;height:16px;font-size:9px}
.pay-card.is-on .mk-name{color:var(--green-dark);font-weight:700}

/* order summary */
.sum{background:#fff;border:1px solid var(--green-line);border-radius:12px;padding:9px 12px;margin-bottom:10px}
.sum-line{display:flex;align-items:center;justify-content:space-between;font-size:12.5px;color:var(--muted);padding:2.5px 0}
.sum-line b{color:var(--ink);font-weight:700;font-size:13px}
.sum-line.is-total{border-top:1px dashed #DCEFE2;margin-top:4px;padding-top:6px;font-size:13.5px;font-weight:700;color:var(--ink)}
.sum-line.is-total b{color:var(--green-dark);font-size:17px}

/* merchant number strip */
.send-line{display:flex;align-items:center;justify-content:space-between;gap:8px;background:#F3F9F5;border:1px dashed #BFE0CC;border-radius:11px;padding:8px 11px;margin-bottom:10px}
.send-line .sl-txt{display:flex;align-items:baseline;gap:7px;flex-wrap:wrap;min-width:0}
.send-line .sl-txt i{color:var(--green);font-size:12px;align-self:center}
.send-line .sl-txt b{font-size:16px;letter-spacing:.4px;color:var(--ink)}
.send-line .sl-txt small{font-size:11px;color:var(--muted)}
.copy-btn{font-size:11.5px;padding:5px 10px;border-radius:8px;flex:0 0 auto}

/* inputs */
.field{margin-bottom:9px}
.field label{font-size:12.5px;margin-bottom:4px}
.inp input{height:40px;font-size:13.5px;border-radius:10px;padding:0 12px 0 35px}
.inp > i{left:11px;font-size:12.5px}
.field .opt-tag{font-size:10px;padding:1.5px 6px}
.field label .star{color:var(--red)}

/* screenshot row */
.slip-row{position:relative;display:flex;align-items:center;gap:9px;border:1px dashed #CDE3D6;border-radius:11px;padding:8px 11px;background:#FAFDFB;cursor:pointer;margin-bottom:10px;transition:.15s}
.slip-row:hover{border-color:var(--green);background:#F4FBF6}
.slip-row.has-file{border-style:solid;border-color:var(--green);background:#EFF8F2}
.slip-row .sr-ic{color:var(--green);font-size:14px;flex:0 0 auto}
.slip-row .sr-txt{flex:1;min-width:0;display:flex;flex-direction:column;line-height:1.3}
.slip-row .sr-txt b{font-size:12.5px;font-weight:600;color:var(--ink)}
.slip-row .slip-picked{font-size:10.8px;color:#8AA396;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.slip-row.has-file .slip-picked{color:var(--green);font-weight:600}
.slip-row .sr-btn{font-size:11.5px;font-weight:700;color:var(--green-dark);background:#EAF6EE;border-radius:8px;padding:5px 11px;flex:0 0 auto}
.slip-row input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}

/* check + button */
.check-row{font-size:12.5px;margin:2px 0 10px;align-items:center}
.check-row input{width:18px;height:18px;flex:0 0 18px;margin-top:0}
.btn-pay{height:46px;font-size:15px;border-radius:11px;box-shadow:0 5px 14px rgba(11,122,59,.22)}
.foot-note{display:flex;align-items:center;justify-content:center;gap:14px;flex-wrap:wrap;font-size:11.3px;color:var(--muted);padding:9px 0 2px}
.foot-note a{color:var(--muted)}
.foot-note i{color:var(--green);margin-right:3px}
@media (max-width:640px){
  .brand-name{font-size:18px}
  .brand-tag{display:none}
  .band h1{font-size:16.5px}
  .band p{font-size:11.3px}
  .band-inner{padding:9px 12px}
  .shell{padding:9px}
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
      <span class="tm"><i class="fa-solid fa-shield-halved"></i> নিরাপদ লেনদেন</span>
      <span class="tm"><i class="fa-solid fa-headset"></i> ২৪/৭ সাপোর্ট</span>
    </div>
  </div>
</header>

<div class="band">
  <div class="band-inner">
    <span class="bi-ic"><i class="fa-solid fa-wallet"></i></span>
    <span>
      <h1>পেমেন্ট গেটওয়ে</h1>
      <p>মোবাইল ব্যাংকিং এর মাধ্যমে নিরাপদ পেমেন্ট করুন</p>
    </span>
  </div>
  <i class="fa-solid fa-shield-halved shield"></i>
  <span class="barcode"></span>
</div>

<main class="shell">

  <?php if (count($errors) > 0): ?>
    <div class="alert">
      <i class="fa-solid fa-circle-exclamation" style="margin-top:2px"></i>
      <div>
        <?php foreach ($errors as $error): ?>
          <div><?php echo soko_h($error); ?></div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <form method="post" action="payment_gateway.php?order=<?php echo (int)$order['id']; ?>" id="payForm" enctype="multipart/form-data">
    <input type="hidden" name="pay_now" value="1">

    <div class="pay-grid">
      <?php foreach ($methods as $key => $meta): ?>
        <label class="pay-card <?php echo $key === $selected_method ? 'is-on' : ''; ?>" data-method="<?php echo soko_h($key); ?>" data-name="<?php echo soko_h($meta['name']); ?>" data-number="<?php echo soko_h(isset($numbers[$key]) ? $numbers[$key] : ''); ?>">
          <input type="radio" name="method" value="<?php echo soko_h($key); ?>" <?php echo $key === $selected_method ? 'checked' : ''; ?>>
          <span class="tick"><i class="fa-solid fa-check"></i></span>
          <span class="mark <?php echo soko_h($meta['css']); ?>">
            <?php $logo = soko_pay_method_logo($key); ?>
            <?php if ($logo !== ''): ?>
              <img src="<?php echo soko_h($logo); ?>" alt="<?php echo soko_h($meta['english']); ?>">
            <?php else: ?>
              <?php echo soko_h($meta['name']); ?>
            <?php endif; ?>
          </span>
          <span class="mk-name"><?php echo soko_h($meta['english']); ?></span>
        </label>
      <?php endforeach; ?>
    </div>

    <div class="sum">
      <div class="sum-line"><span>সেবা মূল্য</span><b><?php echo soko_money($amount); ?></b></div>
      <div class="sum-line"><span>সার্ভিস চার্জ</span><b>৳ ০</b></div>
      <div class="sum-line is-total"><span>মোট পরিশোধ</span><b><?php echo soko_money($amount); ?></b></div>
    </div>

    <div class="send-line">
      <span class="sl-txt">
        <i class="fa-solid fa-arrow-right-arrow-left"></i>
        <b id="merchantNumber"><?php echo soko_bn_digits(soko_h($active_number !== '' ? $active_number : 'সাপোর্টে যোগাযোগ করুন')); ?></b>
        <small id="merchantLabel"><?php echo soko_h($active_meta['name']); ?> — Personal</small>
      </span>
      <button type="button" class="copy-btn" id="copyBtn" data-number="<?php echo soko_h($active_number); ?>"><i class="fa-regular fa-copy"></i> কপি</button>
    </div>

    <div class="field">
      <label for="senderNumber">আপনার <span id="senderMethodLabel"><?php echo soko_h($active_meta['name']); ?></span> নম্বর <span class="opt-tag">ঐচ্ছিক</span></label>
      <div class="inp">
        <i class="fa-solid fa-mobile-screen"></i>
        <input type="tel" id="senderNumber" name="sender_number" value="<?php echo soko_h($sender_number); ?>" placeholder="01XXXXXXXXX" maxlength="14" inputmode="numeric">
      </div>
    </div>

    <div class="field">
      <label for="trxId">ট্রানজেকশন আইডি (TrxID) <span class="star">*</span></label>
      <div class="inp">
        <i class="fa-solid fa-receipt"></i>
        <input type="text" id="trxId" name="trx_id" value="<?php echo soko_h($trx_id); ?>" placeholder="যেমন: 9F3A7B6C2D" maxlength="40" required>
      </div>
    </div>

    <label class="slip-row" for="slipFile">
      <i class="fa-solid fa-image sr-ic"></i>
      <span class="sr-txt">
        <b>পেমেন্টের স্ক্রিনশট <span class="opt-tag">ঐচ্ছিক</span></b>
        <span class="slip-picked" id="slipPicked">কোনো ফাইল নির্বাচিত হয়নি</span>
      </span>
      <span class="sr-btn">আপলোড</span>
      <input type="file" id="slipFile" name="slip" accept="image/*,application/pdf" data-slip="1">
    </label>

    <label class="check-row">
      <input type="checkbox" name="confirm_paid" value="1">
      <span>আমি <?php echo soko_money($amount); ?> সেন্ড মানি করেছি</span>
    </label>

    <button type="submit" class="btn-pay" id="payBtn">
      পেমেন্ট করতে এগিয়ে যান <i class="fa-solid fa-arrow-right" style="font-size:13px"></i>
    </button>
  </form>

  <div class="foot-note">
    <span><i class="fa-solid fa-lock"></i> নিরাপদ পেমেন্ট</span>
    <span><i class="fa-solid fa-headset"></i> ২৪/৭ সাপোর্ট</span>
    <a href="work_history.php"><i class="fa-solid fa-clock-rotate-left"></i> অর্ডার হিস্টরি</a>
  </div>
</main>



<script>
(function () {
  var cards = document.querySelectorAll('.pay-card');
  var merchantNumber = document.getElementById('merchantNumber');
  var merchantLabel = document.getElementById('merchantLabel');
  var senderLabel = document.getElementById('senderMethodLabel');
  var copyBtn = document.getElementById('copyBtn');

  function bnDigits(value) {
    var en = ['0','1','2','3','4','5','6','7','8','9'];
    var bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    var out = String(value);
    for (var i = 0; i < 10; i++) { out = out.split(en[i]).join(bn[i]); }
    return out;
  }

  Array.prototype.forEach.call(cards, function (card) {
    card.addEventListener('click', function () {
      Array.prototype.forEach.call(cards, function (c) { c.classList.remove('is-on'); });
      card.classList.add('is-on');
      card.querySelector('input').checked = true;

      var number = card.getAttribute('data-number');
      var label = card.getAttribute('data-name');
      if (merchantNumber) { merchantNumber.textContent = number ? bnDigits(number) : 'সাপোর্টে যোগাযোগ করুন'; }
      if (merchantLabel) { merchantLabel.textContent = label + ' — Personal'; }
      if (senderLabel) { senderLabel.textContent = label; }
      if (copyBtn) { copyBtn.setAttribute('data-number', number || ''); }
    });
  });

  if (copyBtn) {
    copyBtn.addEventListener('click', function () {
      var number = copyBtn.getAttribute('data-number');
      if (!number) { return; }
      if (navigator.clipboard) {
        navigator.clipboard.writeText(number);
      }
      var old = copyBtn.innerHTML;
      copyBtn.innerHTML = '<i class="fa-solid fa-check"></i> কপি হয়েছে';
      setTimeout(function () { copyBtn.innerHTML = old; }, 1600);
    });
  }

  var slip = document.getElementById('slipFile');
  if (slip) {
    slip.addEventListener('change', function () {
      var wrap = slip.closest('.slip-row');
      var badge = document.getElementById('slipPicked');
      if (slip.files && slip.files.length > 0) {
        badge.textContent = slip.files[0].name + ' (' + Math.round(slip.files[0].size / 1024) + ' KB)';
        wrap.classList.add('has-file');
      } else {
        badge.textContent = 'কোনো ফাইল নির্বাচন করা হয়নি';
        wrap.classList.remove('has-file');
      }
    });
  }

  var form = document.getElementById('payForm');
  if (form) {
    form.addEventListener('submit', function () {
      var btn = document.getElementById('payBtn');
      if (btn) {
        setTimeout(function () {
          btn.disabled = true;
          btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> জমা হচ্ছে...';
        }, 0);
      }
    });
  }
})();
</script>
</body>
</html>
