<?php
/**
 * Sokol Sheba — unified order receipt.
 *
 * Every service (NID, sign copy, TIN, birth registration …) ends here with the
 * same receipt layout. Reached after an order is placed, or later from the
 * order history: receipt.php?order=<order_id>
 */
session_start();
include_once("includes/configuration.php");
include_once("includes/service_catalog.php");

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

$is_new = isset($_GET['new']) && (string)$_GET['new'] === '1';

$parsed = soko_read_payment_note($order['input_data']);
$payment_method = $parsed['payment'];
$detail_rows = $parsed['rows'];

$service_title = trim((string)$order['order_type']);
$order_price = (float)$order['price'];
$service_charge = 0.0;
$channels = soko_payment_channels($conn);

$service_row = soko_get_service($conn, 0, $service_title);
$category = 'সেবা';
$service_id = 0;
if ($service_row) {
    $category = trim((string)$service_row['category']);
    if ($category === '') {
        $category = 'সেবা';
    }
    $service_id = (int)$service_row['id'];
}
$service_icon = soko_service_icon($service_title, $category);

$support_number = '';
if (function_exists('get_site_setting')) {
    $support_number = trim((string)get_site_setting($conn, 'support_whatsapp_num', ''));
}
$whatsapp_link = '';
if ($support_number !== '') {
    $digits = preg_replace('/[^0-9]/', '', $support_number);
    if (strlen($digits) === 11) {
        $digits = '88' . $digits;
    }
    $whatsapp_link = 'https://wa.me/' . $digits;
}

$status_key = strtolower(trim((string)$order['status']));
$is_unpaid = ($payment_method === 'mobile' || $payment_method === 'bank');

function soko_receipt_is_file($value)
{
    $lower = strtolower((string)$value);
    $extensions = array('.jpg', '.jpeg', '.png', '.webp', '.pdf');
    foreach ($extensions as $extension) {
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
<title>রশিদ #<?php echo (int)$order['id']; ?> — <?php echo soko_h($service_title); ?> | সকল সেবা</title>
<link rel="shortcut icon" href="assets/logo.jpg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{
  --green:#0B7A3B;
  --green-dark:#07612E;
  --green-soft:#EAF6EE;
  --green-line:#D6EBDC;
  --red:#E51B23;
  --ink:#123225;
  --muted:#5E7466;
  --line:#E3EDE6;
  --page:#F5FAF6;
}
*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
body{font-family:'Hind Siliguri',system-ui,-apple-system,'Segoe UI',sans-serif;background:var(--page);color:var(--ink);font-size:15px;line-height:1.55;padding-bottom:70px}
a{text-decoration:none;color:inherit}
button{font-family:inherit}

/* top bar */
.topbar{background:#fff;border-bottom:1px solid var(--line);position:sticky;top:0;z-index:60}
.topbar-inner{max-width:880px;margin:0 auto;display:flex;align-items:center;gap:14px;padding:10px 16px}
.brand{display:flex;align-items:center;gap:10px;flex:1;min-width:0}
.brand-logo{width:48px;height:48px;border-radius:50%;overflow:hidden;flex:0 0 48px;border:2px solid var(--green);background:#fff}
.brand-logo img{width:100%;height:100%;object-fit:cover}
.brand-name{font-size:23px;font-weight:700;color:var(--green);line-height:1.1;display:block;letter-spacing:-.3px}
.brand-name em{color:var(--red);font-style:normal}
.brand-tag{display:block;font-size:12.5px;color:var(--muted);font-weight:500;margin-top:1px}
.wallet-pill{display:flex;align-items:center;gap:8px;border:1.5px solid var(--green);border-radius:12px;padding:6px 12px 6px 6px;background:#fff}
.wallet-ic{width:30px;height:30px;border-radius:8px;background:var(--green);color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px}
.wallet-txt{display:flex;flex-direction:column;line-height:1.15}
.wallet-txt small{font-size:10.5px;color:var(--muted);font-weight:600}
.wallet-txt b{font-size:14px}
.icon-btn{width:42px;height:42px;border-radius:12px;border:1.5px solid var(--line);background:#fff;color:var(--ink);display:flex;align-items:center;justify-content:center;font-size:16px}

/* success banner */
.banner{background:linear-gradient(135deg,#0E8C4B 0%,#0B7A3B 55%,#075F2D 100%);color:#fff;text-align:center;padding:26px 16px 30px}
.banner .tick{width:62px;height:62px;border-radius:50%;background:rgba(255,255,255,.18);border:2px solid rgba(255,255,255,.5);display:flex;align-items:center;justify-content:center;font-size:26px;margin:0 auto 10px}
.banner h1{font-size:23px;font-weight:700;letter-spacing:-.2px}
.banner p{font-size:13.5px;opacity:.92;margin-top:3px}
.banner .order-chip{display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.3);border-radius:20px;padding:5px 14px;font-size:13px;font-weight:600;margin-top:12px}

.shell{max-width:880px;margin:0 auto;padding:16px}

/* receipt */
.receipt{background:#fff;border:1px solid var(--green-line);border-radius:16px;overflow:hidden;box-shadow:0 8px 24px rgba(11,122,59,.07)}
.r-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:16px;border-bottom:1px dashed #D9E6DD;background:#FBFEFC}
.r-brand{display:flex;align-items:center;gap:10px}
.r-brand img{width:44px;height:44px;border-radius:50%;border:2px solid var(--green);object-fit:cover}
.r-brand b{display:block;font-size:18px;color:var(--green);line-height:1.15;font-weight:700}
.r-brand b em{color:var(--red);font-style:normal}
.r-brand small{display:block;font-size:11.5px;color:var(--muted)}
.r-title{text-align:right}
.r-title b{display:block;font-size:16px;letter-spacing:.5px;font-weight:700}
.r-title span{display:block;font-size:11.5px;color:var(--muted);text-transform:uppercase;letter-spacing:1.4px}

/* meta */
.r-meta{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--line);border-bottom:1px solid var(--line)}
.meta-cell{background:#fff;padding:11px 14px}
.meta-cell span{display:block;font-size:11.5px;color:var(--muted);font-weight:600;margin-bottom:2px}
.meta-cell b{display:block;font-size:13.8px;font-weight:600;word-break:break-word}
.pill{display:inline-block;font-size:11.5px;font-weight:700;padding:2px 10px;border-radius:20px}
.pill.ok{background:#E7F7ED;color:#0A6B34;border:1px solid #C4E7D2}
.pill.wait{background:#FFF6E5;color:#8A5A00;border:1px solid #F2DFB0}
.pill.bad{background:#FDEDED;color:#A81616;border:1px solid #F4D0D0}

/* body */
.r-body{padding:16px}
.r-service{display:flex;align-items:center;gap:11px;padding:11px 13px;border:1px solid var(--line);border-radius:12px;margin-bottom:14px}
.r-service .ic{width:36px;height:36px;border-radius:10px;background:var(--green-soft);color:var(--green);display:flex;align-items:center;justify-content:center;font-size:15px}
.r-service b{display:block;font-size:15px;font-weight:700}
.r-service small{display:block;font-size:12px;color:var(--muted)}
.r-service .amt{margin-left:auto;font-size:18px;font-weight:700;color:var(--green-dark)}

h3.sec{font-size:13px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.6px;margin:14px 0 7px}

table.details{width:100%;border-collapse:collapse}
table.details tr{border-bottom:1px dashed #E7F0EA}
table.details tr:last-child{border-bottom:0}
table.details td{padding:9px 2px;font-size:13.8px;vertical-align:top}
table.details td.k{color:var(--muted);width:42%;font-weight:500}
table.details td.v{font-weight:600;text-align:right;word-break:break-word}
table.details td.v a{color:var(--green-dark);text-decoration:underline}

.totals{margin-top:12px;border-top:1px solid var(--line);padding-top:10px}
.t-row{display:flex;justify-content:space-between;font-size:14px;padding:5px 0;color:var(--muted)}
.t-row span:last-child{font-weight:600;color:var(--ink)}
.t-grand{display:flex;justify-content:space-between;align-items:center;background:var(--green-soft);border-radius:12px;padding:11px 14px;margin-top:8px;font-weight:700;font-size:15px;color:var(--green-dark)}
.t-grand b{font-size:20px}

/* payment note */
.pay-note{margin-top:14px;background:#FFFCF3;border:1px solid #F2E4BC;border-radius:12px;padding:12px 14px}
.pay-note h4{font-size:13.5px;font-weight:700;color:#8A5A00;display:flex;align-items:center;gap:7px;margin-bottom:6px}
.pay-note p{font-size:12.8px;color:#7A6320;margin-bottom:7px}
.pay-num{display:flex;align-items:center;justify-content:space-between;background:#fff;border:1px dashed #E4D6A8;border-radius:9px;padding:8px 11px;font-size:13.5px;margin-bottom:6px}
.pay-num b{font-size:14.5px;letter-spacing:.4px}
.pay-num small{font-size:11.5px;color:var(--muted);font-weight:600}

/* footer note */
.r-foot{border-top:1px dashed #D9E6DD;background:#FBFEFC;padding:13px 16px;display:flex;gap:10px;align-items:flex-start}
.r-foot i{color:var(--green);font-size:14px;margin-top:2px}
.r-foot p{font-size:12.3px;color:var(--muted)}
.r-foot p b{color:var(--ink)}

/* actions */
.actions{display:flex;gap:10px;margin-top:14px;flex-wrap:wrap}
.btn{flex:1;min-width:150px;height:48px;border-radius:12px;border:0;display:flex;align-items:center;justify-content:center;gap:8px;font-size:14.5px;font-weight:700;cursor:pointer;transition:.16s}
.btn.primary{background:var(--green);color:#fff;box-shadow:0 6px 16px rgba(11,122,59,.22)}
.btn.primary:hover{background:var(--green-dark)}
.btn.ghost{background:#fff;border:1.5px solid var(--green-line);color:var(--green-dark)}
.btn.ghost:hover{border-color:var(--green)}

.bottom-nav{position:fixed;left:0;right:0;bottom:0;background:#fff;border-top:1px solid var(--line);display:flex;justify-content:space-around;padding:6px 4px 8px;z-index:70}
.bottom-nav a{display:flex;flex-direction:column;align-items:center;gap:3px;font-size:11px;font-weight:600;color:#7A8F82;flex:1}
.bottom-nav a i{font-size:17px}
.bottom-nav a.is-active{color:var(--green)}

@media (max-width:700px){
  .r-meta{grid-template-columns:repeat(2,1fr)}
  .brand-name{font-size:20px}
  .brand-tag{font-size:11px}
  .topbar-inner{padding:8px 12px;gap:8px}
  .wallet-txt small{display:none}
  .icon-btn{width:38px;height:38px;border-radius:10px;font-size:14px}
  .banner h1{font-size:20px}
  .shell{padding:12px}
  .r-title b{font-size:14px}
}
@media (min-width:701px){
  .bottom-nav{display:none}
  body{padding-bottom:24px}
}
@media print{
  .topbar,.bottom-nav,.banner,.actions{display:none!important}
  body{background:#fff;padding:0}
  .receipt{border:0;box-shadow:none}
  .shell{padding:0;max-width:100%}
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
    <div class="top-actions" style="display:flex;align-items:center;gap:8px">
      <a href="recharge.php" class="wallet-pill">
        <span class="wallet-ic"><i class="fa-solid fa-wallet"></i></span>
        <span class="wallet-txt"><small>ওয়ালেট</small><b><?php echo soko_money($user['balance'], 2); ?></b></span>
      </a>
      <a href="support.php" class="icon-btn" aria-label="সাপোর্ট"><i class="fa-solid fa-headset"></i></a>
    </div>
  </div>
</header>

<section class="banner">
  <div class="tick"><i class="fa-solid fa-check"></i></div>
  <h1><?php echo $is_new ? 'আপনার অর্ডার সফলভাবে জমা হয়েছে!' : 'আপনার অর্ডারের রশিদ'; ?></h1>
  <p><?php echo $is_new ? 'রশিদটি সংরক্ষণ করুন — এই নম্বর দিয়েই অর্ডারের অবস্থা জানতে পারবেন।' : 'নিচে আপনার অর্ডারের সম্পূর্ণ বিবরণ দেওয়া হলো।'; ?></p>
  <span class="order-chip"><i class="fa-solid fa-receipt"></i> অর্ডার নং: #<?php echo soko_bn_digits((int)$order['id']); ?></span>
</section>

<main class="shell">

  <div class="receipt">
    <div class="r-head">
      <div class="r-brand">
        <img src="assets/logo.jpg" alt="সকল সেবা">
        <span>
          <b>সকল <em>সেবা</em></b>
          <small>সব কাজ, এক প্ল্যাটফর্ম</small>
        </span>
      </div>
      <div class="r-title">
        <b>রশিদ</b>
        <span>Receipt</span>
      </div>
    </div>

    <div class="r-meta">
      <div class="meta-cell">
        <span>গ্রাহকের নাম</span>
        <b><?php echo soko_h($user['name']); ?></b>
      </div>
      <div class="meta-cell">
        <span>মোবাইল নম্বর</span>
        <b><?php echo soko_h($user['mobile'] !== '' ? $user['mobile'] : '—'); ?></b>
      </div>
      <div class="meta-cell">
        <span>অর্ডার নম্বর</span>
        <b>#<?php echo soko_bn_digits((int)$order['id']); ?></b>
      </div>
      <div class="meta-cell">
        <span>অর্ডারের তারিখ</span>
        <b><?php echo soko_bn_date($order['created_at']); ?></b>
      </div>
      <div class="meta-cell">
        <span>পেমেন্ট পদ্ধতি</span>
        <b><?php echo soko_h(soko_payment_label($payment_method)); ?></b>
      </div>
      <div class="meta-cell">
        <span>অবস্থা</span>
        <b>
          <?php if ($status_key === 'cancel' || $status_key === 'cancelled'): ?>
            <span class="pill bad"><?php echo soko_h(soko_status_bn($order['status'])); ?></span>
          <?php elseif ($status_key === 'success' || $status_key === 'completed'): ?>
            <span class="pill ok"><?php echo soko_h(soko_status_bn($order['status'])); ?></span>
          <?php else: ?>
            <span class="pill wait"><?php echo soko_h(soko_status_bn($order['status'])); ?></span>
          <?php endif; ?>
        </b>
      </div>
    </div>

    <div class="r-body">

      <div class="r-service">
        <span class="ic"><i class="fa-solid <?php echo soko_h($service_icon); ?>"></i></span>
        <span>
          <b><?php echo soko_h($service_title); ?></b>
          <small><?php echo soko_h($category); ?></small>
        </span>
        <span class="amt"><?php echo soko_money($order_price); ?></span>
      </div>

      <h3 class="sec">সেবার তথ্য</h3>
      <table class="details">
        <?php if (count($detail_rows) > 0): ?>
          <?php foreach ($detail_rows as $row): ?>
            <tr>
              <td class="k"><?php echo soko_h($row['label']); ?></td>
              <td class="v">
                <?php if (soko_receipt_is_file($row['value'])): ?>
                  <a href="uploads/orders/<?php echo soko_h($row['value']); ?>" target="_blank" rel="noopener"><?php echo soko_h($row['value']); ?></a>
                <?php else: ?>
                  <?php echo soko_h($row['value']); ?>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td class="k">তথ্য</td>
            <td class="v">—</td>
          </tr>
        <?php endif; ?>
      </table>

      <div class="totals">
        <div class="t-row"><span>সেবা মূল্য</span><span><?php echo soko_money($order_price); ?></span></div>
        <div class="t-row"><span>সার্ভিস চার্জ</span><span><?php echo soko_money($service_charge); ?></span></div>
        <div class="t-grand"><span>মোট পরিশোধ</span><b><?php echo soko_money($order_price + $service_charge); ?></b></div>
      </div>

      <?php if ($is_unpaid && $status_key !== 'cancel' && $status_key !== 'success' && $status_key !== 'completed'): ?>
        <div class="pay-note">
          <h4><i class="fa-solid fa-circle-exclamation"></i> পেমেন্ট বাকি আছে</h4>
          <p>নিচের যেকোনো একটি নম্বরে <?php echo soko_money($order_price + $service_charge); ?> সেন্ড মানি করে ট্রানজেকশন আইডি সাপোর্টে পাঠান। পেমেন্ট ভেরিফাই হলে অর্ডার প্রসেস শুরু হবে।</p>
          <?php foreach ($channels as $channel): ?>
            <?php if ($channel['number'] !== ''): ?>
              <div class="pay-num">
                <span><?php echo soko_h($channel['name']); ?> <small>(<?php echo soko_h($channel['type']); ?>)</small></span>
                <b><?php echo soko_bn_digits(soko_h($channel['number'])); ?></b>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
          <?php if ($whatsapp_link !== ''): ?>
            <a class="btn ghost" style="margin-top:6px;height:42px" href="<?php echo soko_h($whatsapp_link); ?>" target="_blank" rel="noopener">
              <i class="fa-brands fa-whatsapp"></i> ট্রানজেকশন আইডি পাঠান
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

    </div>

    <div class="r-foot">
      <i class="fa-solid fa-circle-info"></i>
      <p>
        <b>ধন্যবাদ!</b> অর্ডারটি আমাদের সিস্টেমে জমা হয়েছে। সাধারণত ১–২ ঘণ্টার মধ্যে প্রসেস সম্পন্ন হয়।
        অর্ডারের অবস্থা জানতে <b>অর্ডার হিস্টোরি</b> দেখুন
        <?php if ($support_number !== ''): ?>
          অথবা সাপোর্টে যোগাযোগ করুন: <b><?php echo soko_bn_digits(soko_h($support_number)); ?></b>
        <?php endif; ?>।
      </p>
    </div>
  </div>

  <div class="actions">
    <button type="button" class="btn primary" onclick="window.print()"><i class="fa-solid fa-print"></i> রশিদ প্রিন্ট করুন</button>
    <a class="btn ghost" href="work_history.php"><i class="fa-solid fa-clock-rotate-left"></i> অর্ডার হিস্টোরি</a>
    <?php if ($service_id > 0): ?>
      <a class="btn ghost" href="service_order.php?id=<?php echo (int)$service_id; ?>"><i class="fa-solid fa-rotate-right"></i> আবার অর্ডার করুন</a>
    <?php endif; ?>
    <a class="btn ghost" href="dashboard.php"><i class="fa-solid fa-house"></i> ড্যাশবোর্ড</a>
  </div>

</main>

<nav class="bottom-nav">
  <a href="dashboard.php"><i class="fa-solid fa-house"></i><span>হোম</span></a>
  <a href="services.php"><i class="fa-solid fa-layer-group"></i><span>সেবা</span></a>
  <a href="recharge.php"><i class="fa-solid fa-wallet"></i><span>ওয়ালেট</span></a>
  <a href="profile.php"><i class="fa-regular fa-user"></i><span>প্রোফাইল</span></a>
</nav>

</body>
</html>
