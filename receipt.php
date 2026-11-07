<?php
/**
 * Sokol Sheba — payment receipt (A4 style, one page for every service).
 *
 * Receipt.php?order=<order_id>
 * Shows the order, the payment (wallet / bKash / Nagad / Rocket / Upay TrxID)
 * and prints cleanly on A4.
 */
session_start();
include_once("includes/configuration.php");
include_once("includes/payment_flow.php");

$user = soko_current_user($conn);
if (!$user) {
    header("Location: index.php");
    exit;
}

$is_deposit = false;
$dep_status_bn = '';
$support_number = '';
$service_row = null;
$deposit_id = isset($_GET['deposit']) ? (int)$_GET['deposit'] : 0;

if ($deposit_id > 0) {
    /* ---------- ওয়ালেট রিচার্জের রশিদ (deposit_requests) ---------- */
    $is_deposit = true;
    $dep = null;
    $d_stmt = $conn->prepare("SELECT id, method, trx_id, amount, status, created_at FROM deposit_requests WHERE id = ? AND user_id = ? LIMIT 1");
    $d_uid = (int)$user['id'];
    $d_stmt->bind_param("ii", $deposit_id, $d_uid);
    $d_stmt->execute();
    $d_res = $d_stmt->get_result();
    if ($d_res && $d_res->num_rows > 0) {
        $dep = $d_res->fetch_assoc();
    }
    $d_stmt->close();
    if (!$dep) {
        header("Location: recharge.php");
        exit;
    }

    $order = array(
        'id'         => (int)$dep['id'],
        'created_at' => $dep['created_at'],
        'status'     => (string)$dep['status'],
        'price'      => $dep['amount'],
        'order_type' => 'ব্যালেন্স রিচার্জ',
        'input_data' => '',
    );

    $is_new = isset($_GET['new']) && (string)$_GET['new'] === '1';
    $detail_rows = array();
    $payment_method = 'mobile';
    $amount = (float)$dep['amount'];
    $service_title = 'ব্যালেন্স রিচার্জ';
    $service_id = 0;
    $category = 'টপ-আপ সেবা';
    $receipt_no = soko_receipt_no($order);

    $dep_state = strtolower(trim((string)$dep['status']));
    if (in_array($dep_state, array('completed', 'approved', 'success', 'verified'), true)) {
        $state = 'verified';
        $dep_status_bn = 'সম্পন্ন';
    } elseif (in_array($dep_state, array('failed', 'cancelled', 'canceled', 'rejected'), true)) {
        $state = 'rejected';
        $dep_status_bn = 'বাতিল';
    } else {
        $state = 'pending';
        $dep_status_bn = 'অপেক্ষমাণ';
    }

    $meta = soko_pay_method_meta($dep['method']);
    $method_name = $meta['name'];
    $method_css = $meta['css'];
    $payer_number = (string)$user['mobile'];
    $trx_display = (string)$dep['trx_id'];
    $paid_at = $dep['created_at'];
    $payment = null;
    $method_logo = function_exists('soko_pay_method_logo') ? soko_pay_method_logo($dep['method']) : '';
    $is_paid = ($state === 'verified');
} else {
$order_id = isset($_GET['order']) ? (int)$_GET['order'] : 0;
$order = soko_order_by_id($conn, $order_id, (int)$user['id']);
if (!$order) {
    header("Location: work_history.php");
    exit;
}

$is_new = isset($_GET['new']) && (string)$_GET['new'] === '1';

$parsed = soko_read_payment_note($order['input_data']);
$payment_method = $parsed['payment'];          // wallet | mobile
$detail_rows = $parsed['rows'];

$state = soko_order_payment_state($conn, $order);   // wallet|verified|pending|rejected|unpaid
$payment = soko_pay_latest($conn, (int)$order['id']);
$amount = (float)$order['price'];
$service_title = trim((string)$order['order_type']);
$receipt_no = soko_receipt_no($order);

$service_row = soko_get_service($conn, 0, $service_title);
$category = 'সেবা';
$service_id = 0;
if ($service_row) {
    if (trim((string)$service_row['category']) !== '') {
        $category = trim((string)$service_row['category']);
    }
    $service_id = (int)$service_row['id'];
}

$support_number = '';
if (function_exists('get_site_setting')) {
    $support_number = trim((string)get_site_setting($conn, 'support_whatsapp_num', ''));
}

if ($state === 'wallet') {
    $method_name = 'ওয়ালেট ব্যালেন্স';
    $method_css = 'mk-wallet';
    $payer_number = isset($user['mobile']) ? (string)$user['mobile'] : '';
    $trx_display = 'WALLET-' . $receipt_no;
    $paid_at = $order['created_at'];
} elseif ($payment) {
    $meta = soko_pay_method_meta($payment['method']);
    $method_name = $meta['name'];
    $method_css = $meta['css'];
    $payer_number = (string)$payment['sender_number'];
    $trx_display = (string)$payment['trx_id'];
    $paid_at = $payment['created_at'];
} else {
    $method_name = 'মোবাইল ব্যাংকিং';
    $method_css = 'mk-bkash';
    $payer_number = '';
    $trx_display = '—';
    $paid_at = $order['created_at'];
}

$method_logo = ($state === 'wallet') ? '' : (function_exists('soko_pay_method_logo') && $payment ? soko_pay_method_logo($payment['method']) : '');

$is_paid = ($state === 'wallet' || $state === 'verified');
}


if ($state === 'pending') {
    $badge_title = 'পেমেন্ট যাচাই চলছে';
    $badge_text = 'আপনার পেমেন্ট তথ্য যাচাই করা হচ্ছে, কনফার্ম হতে কয়েক মিনিট সময় লাগতে পারে।';
    $badge_class = 'is-wait';
    $badge_icon = 'fa-clock';
} elseif ($state === 'rejected') {
    $badge_title = 'পেমেন্ট বাতিল হয়েছে';
    $badge_text = 'আপনার জমা দেওয়া TrxID যাচাই করা যায়নি। আবার পেমেন্ট করুন অথবা সাপোর্টে যোগাযোগ করুন।';
    $badge_class = 'is-bad';
    $badge_icon = 'fa-circle-xmark';
} elseif ($state === 'unpaid') {
    $badge_title = 'পেমেন্ট বাকি আছে';
    $badge_text = 'অর্ডারটি নিশ্চিত করতে পেমেন্ট সম্পন্ন করুন।';
    $badge_class = 'is-wait';
    $badge_icon = 'fa-circle-exclamation';
} else {
    $badge_title = 'পেমেন্ট সফল';
    $badge_text = 'আপনার পেমেন্ট সফলভাবে সম্পন্ন হয়েছে।';
    $badge_class = 'is-ok';
    $badge_icon = 'fa-check';
}

if ($is_deposit) {
    if ($state === 'verified') {
        $badge_title = 'রিচার্জ সফল';
        $badge_text = 'আপনার ওয়ালেটে ব্যালেন্স যোগ করা হয়েছে।';
        $badge_class = 'is-ok';
        $badge_icon = 'fa-check';
    } elseif ($state === 'rejected') {
        $badge_title = 'রিচার্জ বাতিল';
        $badge_text = 'আপনার দেওয়া TrxID যাচাই করা যায়নি। সাপোর্টে যোগাযোগ করুন।';
        $badge_class = 'is-bad';
        $badge_icon = 'fa-circle-xmark';
    } else {
        $badge_title = 'রিচার্জ যাচাই চলছে';
        $badge_text = 'TrxID যাচাই করে ৫–১৫ মিনিটের মধ্যে ব্যালেন্স যোগ করা হবে।';
        $badge_class = 'is-wait';
        $badge_icon = 'fa-clock';
    }
}

function soko_receipt_is_file($value)
{
    $lower = strtolower((string)$value);
    foreach (array('.jpg', '.jpeg', '.png', '.webp', '.pdf') as $extension) {
        $length = strlen($extension);
        if ($length < strlen($lower) && substr($lower, -$length) === $extension) {
            return true;
        }
    }
    return false;
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>পেমেন্ট রশিদ #<?php echo soko_h($receipt_no); ?> — <?php echo soko_h($service_title); ?></title>
<link rel="shortcut icon" href="<?php echo soko_e(soko_brand('site_favicon')); ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{--green:#0B7A3B;--green-dark:#075F2D;--green-soft:#EAF6EE;--green-line:#CFE6D7;--red:#E51B23;--ink:#123225;--muted:#5E7466;--line:#E3EDE6;--page:#EEF4F0}
*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
body{font-family:'Hind Siliguri',system-ui,-apple-system,'Segoe UI',sans-serif;background:var(--page);color:var(--ink);font-size:14.5px;line-height:1.55;padding:16px 10px 30px}
a{text-decoration:none;color:inherit}
button{font-family:inherit}
.sheet{max-width:820px;margin:0 auto;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 10px 34px rgba(11,122,59,.12)}

/* ---------- header ---------- */
.head{position:relative;padding:16px 18px;background:#fff;overflow:hidden}
.head:before{content:"";position:absolute;right:-60px;top:-90px;width:330px;height:230px;background:linear-gradient(135deg,#0E8C4B,#0B7A3B);border-radius:0 0 0 220px;opacity:.95}
.head:after{content:"";position:absolute;right:20px;top:0;width:120px;height:100%;background:repeating-linear-gradient(90deg,transparent 0 10px,rgba(229,27,35,.55) 10px 13px,transparent 13px 24px);opacity:.16;transform:skewX(-12deg)}
.head-in{position:relative;z-index:2;display:flex;align-items:center;gap:14px;flex-wrap:wrap}
.h-brand{display:flex;align-items:center;gap:11px}
.h-logo{width:64px;height:64px;border-radius:50%;overflow:hidden;background:#fff;border:3px solid var(--green);box-shadow:0 3px 10px rgba(11,122,59,.18);flex:0 0 64px}
.h-logo img{width:100%;height:100%;object-fit:cover}
.h-name{font-size:31px;font-weight:700;color:var(--green);line-height:1.05;letter-spacing:-.6px}
.h-name em{color:var(--red);font-style:normal}
.h-tag{font-size:14px;color:#33513F;font-weight:600;margin-top:2px}
.h-panel{margin-left:auto;background:var(--green);color:#fff;border-radius:10px;padding:9px 18px;font-size:14px;font-weight:600;text-align:center;line-height:1.35;box-shadow:0 4px 12px rgba(11,122,59,.2)}
.h-rule{height:4px;background:linear-gradient(90deg,var(--green) 0 62%,var(--red) 62% 74%,var(--green) 74% 100%)}

/* ---------- receipt title ---------- */
.title-bar{display:flex;align-items:center;gap:14px;margin:14px 16px 0;border:1.5px solid var(--green-line);background:#F7FCF8;border-radius:12px;padding:12px 16px;flex-wrap:wrap}
.title-left{display:flex;align-items:center;gap:12px}
.title-check{width:46px;height:46px;border-radius:50%;background:var(--green);color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;flex:0 0 46px}
.title-left b{display:block;font-size:24px;color:var(--green-dark);font-weight:700;line-height:1.1}
.title-left small{display:block;font-size:11px;letter-spacing:2.4px;color:#8AA396;font-weight:600;text-transform:uppercase;margin-top:2px}
.title-right{margin-left:auto;text-align:right;font-size:12.8px;color:var(--muted);line-height:1.7}
.title-right b{color:var(--ink);font-size:13.2px}

/* ---------- body ---------- */
.body{padding:14px 16px 16px}
.seg{margin-bottom:14px}
.seg-pill{display:inline-flex;align-items:center;gap:8px;background:var(--green);color:#fff;font-size:14px;font-weight:700;padding:6px 16px 6px 12px;border-radius:8px 18px 18px 8px;margin-bottom:8px}
.seg-pill i{font-size:12.5px}
.seg-cols{display:flex;gap:16px;align-items:flex-start;flex-wrap:wrap}
.seg-main{flex:1;min-width:240px}
.seg-side{flex:0 0 250px}

table.kv{width:100%;border-collapse:collapse}
table.kv td{padding:6px 0;font-size:14px;vertical-align:top}
table.kv td.k{color:#3C5B49;width:44%;font-weight:500}
table.kv td.k:after{content:":";float:right;color:#9BB0A3;margin-right:8px}
table.kv td.v{font-weight:600;word-break:break-word}
table.kv tr.total td.v{font-size:22px;color:var(--green-dark);font-weight:700}

.state-box{border-radius:12px;padding:16px 14px;text-align:center;border:1.5px solid}
.state-box .sb-ic{width:52px;height:52px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:22px;margin:0 auto 8px;color:#fff}
.state-box b{display:block;font-size:17px;font-weight:700;margin-bottom:3px}
.state-box small{display:block;font-size:12.3px;line-height:1.5}
.state-box.is-ok{background:#F1FAF4;border-color:#BFE3CC}
.state-box.is-ok .sb-ic{background:var(--green)}
.state-box.is-ok b{color:var(--green-dark)}
.state-box.is-ok small{color:#4B6B58}
.state-box.is-wait{background:#FFFBF1;border-color:#F1E1B4}
.state-box.is-wait .sb-ic{background:#E0A81B}
.state-box.is-wait b{color:#8A5A00}
.state-box.is-wait small{color:#8A6A25}
.state-box.is-bad{background:#FEF3F3;border-color:#F4D2D2}
.state-box.is-bad .sb-ic{background:#D94A4A}
.state-box.is-bad b{color:#A81616}
.state-box.is-bad small{color:#8E4A4A}

/* method card */
.mcard{border:1.5px solid #E2EFE6;border-radius:12px;overflow:hidden;background:#fff;box-shadow:0 4px 14px rgba(11,122,59,.06)}
.mcard-head{color:#fff;padding:8px 12px;display:flex;align-items:center;justify-content:space-between;font-weight:700;font-size:14.5px}
.mcard-head.has-logo{background:#fff;border-bottom:1px solid #EEF6F1;padding:9px 12px}
.mcard-head.has-logo img{height:24px;width:auto;max-width:110px;object-fit:contain}
.mcard-head .glyph{font-size:12px;opacity:.9}
.mcard-body{padding:12px}
.mcard-body .mc-check{width:38px;height:38px;border-radius:50%;background:var(--green);color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;margin:0 auto 6px}
.mcard-body .mc-title{text-align:center;font-weight:700;font-size:14.5px;color:var(--green-dark);margin-bottom:8px}
.mcard-body .mc-row{display:flex;justify-content:space-between;font-size:12.3px;color:var(--muted);padding:3px 0;border-bottom:1px dashed #EAF2EC}
.mcard-body .mc-row:last-child{border-bottom:0}
.mcard-body .mc-row b{color:var(--ink);font-weight:600}

.mk-bkash{background:#E2136E}
.mk-nagad{background:#F58220}
.mk-rocket{background:#8C3494}
.mk-upay{background:#1C4E9B}
.mk-wallet{background:var(--green)}

.wordmark{display:inline-flex;align-items:center;gap:5px;font-weight:800;font-size:16px}
.wordmark.ink-bkash{color:#E2136E}
.wordmark.ink-nagad{color:#F58220}
.wordmark.ink-rocket{color:#8C3494}
.wordmark.ink-upay{color:#1C4E9B}
.wordmark.ink-wallet{color:var(--green-dark)}

/* watermark */
.has-mark{position:relative}
.watermark{position:absolute;right:0;top:6px;width:150px;height:150px;border-radius:50%;border:6px solid #F0F7F2;display:flex;align-items:center;justify-content:center;opacity:.9;overflow:hidden}
.watermark img{width:100%;height:100%;object-fit:cover;opacity:.18}

/* instructions */
.instr{background:#F6FBF7;border:1px solid #E1EFE6;border-radius:12px;padding:12px 14px;display:flex;gap:10px}
.instr .ii{color:var(--green);font-size:15px;margin-top:2px}
.instr b{display:block;font-size:13.8px;margin-bottom:4px}
.instr ul{padding-left:17px;margin:0}
.instr li{font-size:12.6px;color:#4E6C5B;margin-bottom:3px}

/* details of submitted info */
.submitted{margin-top:6px;border-top:1px dashed #E4EFE8;padding-top:8px}
.submitted summary{cursor:pointer;font-size:12.8px;font-weight:700;color:var(--green-dark);list-style:none}
.submitted summary::-webkit-details-marker{display:none}
.submitted summary:before{content:"\f078";font-family:"Font Awesome 6 Free";font-weight:900;font-size:10px;margin-right:6px}
.submitted[open] summary:before{content:"\f077"}
.submitted table.kv td{padding:4px 0;font-size:13px}

/* ---------- footer ---------- */
.foot{background:linear-gradient(135deg,#0E8C4B 0%,#0B7A3B 45%,#075F2D 100%);color:#fff;padding:16px 18px;position:relative;overflow:hidden}
.foot:before{content:"";position:absolute;left:-40px;top:-60px;width:220px;height:180px;border-radius:50%;background:rgba(255,255,255,.07)}
.foot:after{content:"";position:absolute;right:-30px;bottom:-70px;width:200px;height:160px;border-radius:50%;background:rgba(255,255,255,.06)}
.foot-in{position:relative;z-index:2;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap}
.fs{display:flex;align-items:center;gap:9px;font-size:13.2px;font-weight:700}
.fs i{font-size:19px;opacity:.95}
.fs small{display:block;font-size:11px;opacity:.82;font-weight:500}
.fs.center{text-align:center;flex-direction:column;gap:2px}
.fs.center b{font-size:17px}
.fs.center small{font-size:11.5px}

/* actions */
.actions{max-width:820px;margin:14px auto 0;display:flex;gap:10px;flex-wrap:wrap}
.btn{flex:1;min-width:150px;height:48px;border-radius:12px;border:0;display:flex;align-items:center;justify-content:center;gap:8px;font-size:14.5px;font-weight:700;cursor:pointer}
.btn.primary{background:var(--green);color:#fff;box-shadow:0 6px 16px rgba(11,122,59,.22)}
.btn.ghost{background:#fff;border:1.5px solid var(--green-line);color:var(--green-dark)}

@media (max-width:760px){
  .h-name{font-size:23px}
  .h-tag{font-size:12px}
  .h-logo{width:52px;height:52px;flex:0 0 52px;border-width:2px}
  .h-panel{margin-left:0;width:100%;margin-top:8px}
  .head:before{width:220px;height:170px;right:-70px;top:-60px}
  .title-left b{font-size:20px}
  .title-right{margin-left:0;width:100%;text-align:left;border-top:1px dashed #D9E9DF;padding-top:8px;margin-top:6px}
  .seg-side{flex:1 1 100%}
  .watermark{display:none}
  body{padding:8px 6px 24px;font-size:14px}
  .body{padding:12px}
  .title-bar{margin:10px 12px 0;padding:10px 12px}
  .fs.center{width:100%;order:3}
}
@media print{
  body{background:#fff;padding:0;font-size:12.5px}
  .sheet{box-shadow:none;border-radius:0;max-width:100%}
  .actions,.no-print{display:none!important}
  .head:before{opacity:.9}
  @page{size:A4;margin:8mm}
}
</style>
<style>body{padding:0}.sheet{margin:16px auto}@media print{.sheet{margin:0}}</style>
</head>
<body>
<?php include __DIR__ . '/includes/site_header.php'; ?>

<div class="sheet">

  <div class="head">
    <div class="head-in">
      <div class="h-brand">
        <span class="h-logo"><img src="<?php echo soko_e(soko_brand('site_logo')); ?>" alt="সকল সেবা"></span>
        <span>
          <span class="h-name"><?php echo soko_e(soko_brand('site_name')); ?></span>
          <span class="h-tag"><?php echo soko_e(soko_brand('site_tagline')); ?></span>
        </span>
      </div>
      <div class="h-panel">আপনার বিশ্বস্ত<br>অনলাইন সেবা কেন্দ্র</div>
    </div>
  </div>
  <div class="h-rule"></div>

  <div class="title-bar">
    <div class="title-left">
      <span class="title-check"><i class="fa-solid fa-check"></i></span>
      <span>
        <b>পেমেন্ট রশিদ</b>
        <small>Payment Receipt</small>
      </span>
    </div>
    <div class="title-right">
      <div>রশিদ নং: <b>#<?php echo soko_h($receipt_no); ?></b></div>
      <div>তারিখ: <b><?php echo soko_bn_date($order['created_at']); ?></b></div>
    </div>
  </div>

  <div class="body">

    <!-- customer -->
    <div class="seg">
      <div class="seg-pill"><i class="fa-solid fa-user"></i> গ্রাহকের তথ্য</div>
      <div class="seg-cols">
        <div class="seg-main">
          <table class="kv">
            <tr><td class="k">নাম</td><td class="v"><?php echo soko_h($user['name']); ?></td></tr>
            <tr><td class="k">মোবাইল নম্বর</td><td class="v"><?php echo soko_h($user['mobile'] !== '' ? $user['mobile'] : '—'); ?></td></tr>
            <tr><td class="k">ইমেইল</td><td class="v"><?php echo soko_h($user['email'] !== '' ? $user['email'] : '—'); ?></td></tr>
          </table>
        </div>
        <div class="seg-side">
          <div class="state-box <?php echo soko_h($badge_class); ?>">
            <span class="sb-ic"><i class="fa-solid <?php echo soko_h($badge_icon); ?>"></i></span>
            <b><?php echo soko_h($badge_title); ?></b>
            <small><?php echo soko_h($badge_text); ?></small>
          </div>
        </div>
      </div>
    </div>

    <!-- service -->
    <div class="seg has-mark">
      <div class="seg-pill"><i class="fa-solid fa-layer-group"></i> সেবার বিবরণ</div>
      <div class="watermark"><img src="<?php echo soko_e(soko_brand('site_logo')); ?>" alt=""></div>
      <table class="kv" style="max-width:470px">
        <tr><td class="k">সেবার নাম</td><td class="v"><?php echo soko_h($service_title); ?></td></tr>
        <tr><td class="k">সেবার ধরন</td><td class="v"><?php echo soko_h($category); ?></td></tr>
        <tr><td class="k"><?php echo $is_deposit ? 'রশিদ নম্বর' : 'অর্ডার নম্বর'; ?></td><td class="v">#<?php echo soko_h($receipt_no); ?></td></tr>
        <tr><td class="k"><?php echo $is_deposit ? 'রিচার্জ স্ট্যাটাস' : 'অর্ডার স্ট্যাটাস'; ?></td><td class="v"><?php echo soko_h($is_deposit ? $dep_status_bn : soko_status_bn($order['status'])); ?></td></tr>
        <tr class="total"><td class="k"><?php echo $is_deposit ? 'মোট রিচার্জ' : 'পরিমাণ'; ?></td><td class="v"><?php echo soko_money($amount); ?></td></tr>
      </table>
    </div>

    <?php if (!$is_deposit): ?>
    <div class="seg">
      <?php require_once __DIR__ . '/includes/customer_history.php'; soko_render_order_result($order); ?>
    </div>
    <?php endif; ?>

    <!-- payment -->
    <div class="seg">
      <div class="seg-pill"><i class="fa-solid fa-wallet"></i> পেমেন্টের বিবরণ</div>
      <div class="seg-cols">
        <div class="seg-main">
          <table class="kv">
            <tr>
              <td class="k">পেমেন্ট পদ্ধতি</td>
              <td class="v">
                <?php
                  $ink = 'ink-bkash';
                  if ($method_css === 'mk-nagad') { $ink = 'ink-nagad'; }
                  if ($method_css === 'mk-rocket') { $ink = 'ink-rocket'; }
                  if ($method_css === 'mk-upay') { $ink = 'ink-upay'; }
                  if ($method_css === 'mk-wallet') { $ink = 'ink-wallet'; }
                ?>
                <?php if ($method_logo !== ''): ?>
                  <img src="<?php echo soko_h($method_logo); ?>" alt="<?php echo soko_h($method_name); ?>" style="height:22px;width:auto;max-width:96px;object-fit:contain;vertical-align:middle">
                <?php else: ?>
                  <span class="wordmark <?php echo soko_h($ink); ?>"><i class="fa-solid fa-bolt"></i> <?php echo soko_h($method_name); ?></span>
                <?php endif; ?>
              </td>
            </tr>
            <tr><td class="k">পেমেন্ট নম্বর</td><td class="v"><?php echo soko_h($payer_number !== '' ? soko_bn_digits($payer_number) : '—'); ?></td></tr>
            <tr><td class="k">লেনদেনের পরিমাণ</td><td class="v"><?php echo soko_money($amount); ?></td></tr>
            <tr><td class="k">ট্রানজেকশন আইডি (TrxID)</td><td class="v"><?php echo soko_h($trx_display); ?></td></tr>
            <tr><td class="k">পেমেন্টের তারিখ</td><td class="v"><?php echo soko_bn_date($paid_at); ?></td></tr>
          </table>
        </div>
        <div class="seg-side">
          <div class="mcard">
            <div class="mcard-head <?php echo $method_logo !== '' ? 'has-logo' : soko_h($method_css); ?>">
              <?php if ($method_logo !== ''): ?>
                <img src="<?php echo soko_h($method_logo); ?>" alt="<?php echo soko_h($method_name); ?>">
              <?php else: ?>
                <span><?php echo soko_h($method_name); ?></span>
              <?php endif; ?>
              <span class="glyph" style="<?php echo $method_logo !== '' ? 'color:' . soko_h($method_css === 'mk-nagad' ? '#F58220' : ($method_css === 'mk-rocket' ? '#8C3494' : ($method_css === 'mk-upay' ? '#1C4E9E' : '#E2136E'))) : ''; ?>"><i class="fa-solid fa-circle-check"></i></span>
            </div>
            <div class="mcard-body">
              <div class="mc-check"><i class="fa-solid <?php echo $is_paid ? 'fa-check' : 'fa-clock'; ?>"></i></div>
              <div class="mc-title"><?php echo $is_paid ? 'পেমেন্ট সফল' : 'যাচাই চলছে'; ?></div>
              <div class="mc-row"><span>পরিমাণ</span><b><?php echo soko_money($amount); ?></b></div>
              <div class="mc-row"><span>প্রাপক</span><b><?php echo soko_e(soko_brand('site_name')); ?></b></div>
              <div class="mc-row"><span>ট্রানজেকশন আইডি</span><b><?php echo soko_h($trx_display); ?></b></div>
              <div class="mc-row"><span>তারিখ ও সময়</span><b><?php echo soko_bn_date($paid_at); ?></b></div>
            </div>
          </div>
        </div>
      </div>

      <?php if (count($detail_rows) > 0): ?>
        <details class="submitted">
          <summary>অর্ডারে দেওয়া তথ্য দেখুন</summary>
          <table class="kv">
            <?php foreach ($detail_rows as $row): ?>
              <tr>
                <td class="k"><?php echo soko_h($row['label']); ?></td>
                <td class="v">
                  <?php if (soko_receipt_is_file($row['value'])): ?>
                    <a href="uploads/orders/<?php echo soko_h($row['value']); ?>" target="_blank" rel="noopener" style="color:#075F2D;text-decoration:underline"><?php echo soko_h($row['value']); ?></a>
                  <?php else: ?>
                    <?php echo soko_h($row['value']); ?>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </table>
        </details>
      <?php endif; ?>
    </div>

    <!-- instructions -->
    <div class="instr">
      <i class="fa-solid fa-circle-info ii"></i>
      <div>
        <b>গুরুত্বপূর্ণ নির্দেশনা</b>
        <ul>
          <?php if ($is_deposit): ?>
            <?php if ($state === 'verified'): ?>
              <li>আপনার রিচার্জ গ্রহণ করা হয়েছে — ব্যালেন্স ওয়ালেটে যোগ হয়েছে।</li>
              <li>রশিদটি সংরক্ষণ করুন; প্রয়োজনে সাপোর্টে দেখান।</li>
            <?php elseif ($state === 'rejected'): ?>
              <li>আপনার দেওয়া TrxID যাচাই করা যায়নি — সঠিক TrxID দিয়ে আবার রিচার্জ করুন।</li>
              <li>টাকা কেটে নেওয়া হয়ে থাকলে সাপোর্টে TrxID ও স্ক্রিনশট পাঠান।</li>
            <?php else: ?>
              <li>TrxID যাচাই করে ৫–১৫ মিনিটের মধ্যে ব্যালেন্স যোগ করা হয়।</li>
              <li>যাচাই হলে এই রশিদেই "রিচার্জ সফল" দেখতে পাবেন।</li>
            <?php endif; ?>
            <li>কোনো সমস্যায় পড়লে আমাদের সাপোর্টে যোগাযোগ করুন।</li>
          <?php elseif ($is_paid): ?>
            <li>আপনার পেমেন্ট সফলভাবে গ্রহণ করা হয়েছে।</li>
            <li>প্রয়োজনে অ্যাডমিন প্যানেল থেকে পেমেন্ট যাচাইয়ের পর অর্ডার প্রসেস করা হবে।</li>
          <?php elseif ($state === 'rejected'): ?>
            <li>আপনার দেওয়া TrxID যাচাই করা যায়নি — সঠিক TrxID দিয়ে আবার চেষ্টা করুন।</li>
            <li>টাকা কেটে নেওয়া হয়ে থাকলে সাপোর্টে ট্রানজেকশন আইডি পাঠান।</li>
          <?php elseif ($state === 'unpaid'): ?>
            <li>পেমেন্ট সম্পন্ন না হলে অর্ডার প্রসেস শুরু হবে না।</li>
            <li>বিকাশ / নগদ / রকেট / উপায় — যেকোনোভাবে সেন্ড মানি করতে পারেন।</li>
          <?php else: ?>
            <li>আপনার TrxID যাচাই করা হচ্ছে, সাধারণত ৫–১৫ মিনিট সময় লাগে।</li>
            <li>যাচাই সম্পন্ন হলে এই রশিদেই "পেমেন্ট সফল" দেখতে পাবেন।</li>
          <?php endif; ?>
          <li>কোনো সমস্যায় পড়লে আমাদের সাপোর্টে যোগাযোগ করুন।</li>
        </ul>
      </div>
    </div>

  </div>

  <div class="foot">
    <div class="foot-in">
      <div class="fs">
        <i class="fa-solid fa-headset"></i>
        <span>সাপোর্ট হেল্পলাইন<small><?php echo $support_number !== '' ? soko_bn_digits(soko_h($support_number)) : '২৪/৭ সাপোর্ট'; ?> (সকাল ১০টা – রাত ১০টা)</small></span>
      </div>
      <div class="fs center">
        <b>ধন্যবাদ</b>
        <small><?php echo soko_e(soko_brand('site_name')); ?>-এর সাথে থাকার জন্য!</small>
      </div>
      <div class="fs">
        <i class="fa-solid fa-shield-halved"></i>
        <span>নিরাপদ লেনদেন<small>আপনার তথ্য ১০০% নিরাপদ</small></span>
      </div>
    </div>
  </div>

</div>

<div class="actions no-print">
  <button type="button" class="btn primary" onclick="window.print()"><i class="fa-solid fa-print"></i> রশিদ প্রিন্ট / PDF</button>
  <?php if ($is_deposit): ?>
    <?php if ($state !== 'verified'): ?>
      <a class="btn primary" href="recharge.php"><i class="fa-solid fa-rotate-right"></i> আবার রিচার্জ</a>
    <?php endif; ?>
    <a class="btn ghost" href="recharge.php"><i class="fa-solid fa-wallet"></i> রিচার্জ পেজ</a>
  <?php else: ?>
    <?php if ($state === 'unpaid' || $state === 'rejected'): ?>
      <a class="btn primary" href="payment_gateway.php?order=<?php echo (int)$order['id']; ?>"><i class="fa-solid fa-money-bill-transfer"></i> পেমেন্ট করুন</a>
    <?php endif; ?>
    <a class="btn ghost" href="work_history.php"><i class="fa-solid fa-clock-rotate-left"></i> অর্ডার হিস্টোরি</a>
  <?php endif; ?>
  <?php if (!$is_deposit && $service_id > 0): ?>
    <a class="btn ghost" href="service_order.php?id=<?php echo (int)$service_id; ?>"><i class="fa-solid fa-rotate-right"></i> আবার অর্ডার</a>
  <?php endif; ?>
  <a class="btn ghost" href="dashboard.php"><i class="fa-solid fa-house"></i> ড্যাশবোর্ড</a>
</div>

<?php include __DIR__ . '/includes/site_footer.php'; ?>
</body>
</html>
