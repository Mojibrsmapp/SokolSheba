<?php
/**
 * Sokol Sheba — admin: verify manual mobile banking payments (TrxID).
 * Approve  -> payment verified, user gets the receipt
 * Reject   -> payment rejected with a reason, user can submit again
 */
include_once("header.php");
include_once("../includes/payment_flow.php");

soko_pay_ensure_table($conn);

$msg = "";
$err = "";

if (isset($_GET['action']) && isset($_GET['id'])) {
    $payment_id = (int)$_GET['id'];
    $action = trim($_GET['action']);
    $note = isset($_GET['note']) ? trim($_GET['note']) : "";

    $stmt = $conn->prepare("SELECT * FROM order_payments WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$payment) {
        $err = "পেমেন্টটি পাওয়া যায়নি!";
    } elseif ($payment['status'] !== 'pending') {
        $err = "এই পেমেন্টটি ইতিমধ্যে প্রসেস করা হয়েছে!";
    } elseif ($action === 'approve') {
        if (soko_pay_update_status($conn, $payment_id, 'verified', $note !== '' ? $note : 'Admin verified')) {
            $ord = $conn->prepare("UPDATE orders SET status = 'pending' WHERE id = ? AND status = 'pending'");
            $ord->bind_param("i", $payment['order_id']);
            $ord->execute();
            $ord->close();
            $msg = "পেমেন্ট যাচাই সম্পন্ন হয়েছে! অর্ডার #" . (int)$payment['order_id'] . " এখন প্রসেসে যাবে।";
        } else {
            $err = "পেমেন্ট আপডেট করা যায়নি!";
        }
    } elseif ($action === 'reject') {
        if ($note === '') {
            $note = "TrxID যাচাই করা যায়নি";
        }
        if (soko_pay_update_status($conn, $payment_id, 'rejected', $note)) {
            $msg = "পেমেন্ট বাতিল করা হয়েছে, ইউজার আবার সাবমিট করতে পারবে।";
        } else {
            $err = "পেমেন্ট আপডেট করা যায়নি!";
        }
    }
}

$filter = isset($_GET['status']) ? trim($_GET['status']) : 'pending';
if ($filter !== 'pending' && $filter !== 'verified' && $filter !== 'rejected' && $filter !== 'all') {
    $filter = 'pending';
}

$counts = array('pending' => 0, 'verified' => 0, 'rejected' => 0);
$cres = $conn->query("SELECT status, COUNT(id) AS c FROM order_payments GROUP BY status");
if ($cres) {
    while ($c = $cres->fetch_assoc()) {
        $key = strtolower((string)$c['status']);
        if (isset($counts[$key])) {
            $counts[$key] = (int)$c['c'];
        }
    }
}

$sql = "SELECT p.*, o.order_type, o.order_type AS service_name, u.name AS user_name, u.mobile AS user_mobile, u.email AS user_email
        FROM order_payments p
        LEFT JOIN orders o ON o.id = p.order_id
        LEFT JOIN users u ON u.id = p.user_id";
if ($filter !== 'all') {
    $sql .= " WHERE p.status = '" . $conn->real_escape_string($filter) . "'";
}
$sql .= " ORDER BY p.id DESC LIMIT 200";
$payments = array();
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $payments[] = $row;
    }
}
?>
<style>
.pv-wrap{padding:18px}
.pv-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px}
.pv-tab{background:#fff;border:1px solid #E2E8F0;border-radius:10px;padding:8px 14px;font-size:12.5px;font-weight:700;color:#475569;text-decoration:none}
.pv-tab.active{background:#107C41;border-color:#107C41;color:#fff}
.pv-card{background:#fff;border:1px solid #E5EAF0;border-radius:14px;overflow:hidden}
.pv-table{width:100%;border-collapse:collapse}
.pv-table th{background:#F7FAF8;font-size:11.5px;text-transform:uppercase;letter-spacing:.4px;color:#64748B;padding:11px 12px;text-align:left;border-bottom:1px solid #E8EEF2}
.pv-table td{padding:11px 12px;font-size:12.8px;border-bottom:1px solid #F1F5F9;color:#1E293B;vertical-align:middle}
.pv-table tr:hover td{background:#FBFDFC}
.pv-mk{display:inline-block;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700;color:#fff}
.pv-mk.bkash{background:#E2136E}
.pv-mk.nagad{background:#F58220}
.pv-mk.rocket{background:#8C3494}
.pv-mk.upay{background:#1C4E9B}
.pv-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700}
.pv-badge.wait{background:#FFF7E6;color:#8A5A00;border:1px solid #F2DFB0}
.pv-badge.ok{background:#EAF7EF;color:#0A6B34;border:1px solid #C4E7D2}
.pv-badge.bad{background:#FDEDED;color:#A81616;border:1px solid #F4D0D0}
.pv-btn{display:inline-flex;align-items:center;gap:5px;border:0;border-radius:8px;padding:6px 11px;font-size:11.5px;font-weight:700;text-decoration:none;cursor:pointer}
.pv-btn.ok{background:#107C41;color:#fff}
.pv-btn.bad{background:#FDEDED;color:#A81616;border:1px solid #F4D0D0}
.pv-note{width:100%;border:1px solid #E2E8F0;border-radius:8px;padding:6px 8px;font-size:11.5px;font-family:inherit;margin-top:5px}
.pv-empty{text-align:center;padding:40px 16px;color:#64748B;font-size:13px}
.pv-alert{border-radius:10px;padding:10px 13px;font-size:12.8px;font-weight:600;margin-bottom:12px}
.pv-alert.ok{background:#EAF7EF;border:1px solid #C4E7D2;color:#0A6B34}
.pv-alert.bad{background:#FDEDED;border:1px solid #F4D0D0;color:#A81616}
.pv-mono{font-family:ui-monospace,Menlo,Consolas,monospace;font-size:12px;font-weight:700}
@media(max-width:900px){.pv-wrap{padding:12px}.pv-table{display:block;overflow-x:auto;white-space:nowrap}}
</style>

<div class="page-wrapper-custom">
  <div class="content-wrapper">
    <div class="pv-wrap">

      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
          <h4 class="mb-1" style="font-weight:800;color:#0F172A"><i class="fa-solid fa-money-check-dollar text-success me-2"></i> পেমেন্ট যাচাই</h4>
          <p class="mb-0" style="font-size:12.5px;color:#64748B">ম্যানুয়াল সেন্ড মানি + TrxID যাচাই করুন। অনুমোদন দিলে ইউজার রশিদ পাবে।</p>
        </div>
      </div>

      <?php if ($msg !== ""): ?>
        <div class="pv-alert ok"><i class="fa-solid fa-circle-check me-1"></i> <?php echo htmlspecialchars($msg); ?></div>
      <?php endif; ?>
      <?php if ($err !== ""): ?>
        <div class="pv-alert bad"><i class="fa-solid fa-circle-exclamation me-1"></i> <?php echo htmlspecialchars($err); ?></div>
      <?php endif; ?>

      <div class="pv-tabs">
        <a class="pv-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>" href="payment_verify.php?status=pending">যাচাই বাকি (<?php echo $counts['pending']; ?>)</a>
        <a class="pv-tab <?php echo $filter === 'verified' ? 'active' : ''; ?>" href="payment_verify.php?status=verified">অনুমোদিত (<?php echo $counts['verified']; ?>)</a>
        <a class="pv-tab <?php echo $filter === 'rejected' ? 'active' : ''; ?>" href="payment_verify.php?status=rejected">বাতিল (<?php echo $counts['rejected']; ?>)</a>
        <a class="pv-tab <?php echo $filter === 'all' ? 'active' : ''; ?>" href="payment_verify.php?status=all">সব দেখুন</a>
      </div>

      <div class="pv-card">
        <?php if (count($payments) === 0): ?>
          <div class="pv-empty"><i class="fa-solid fa-inbox" style="font-size:30px;opacity:.35"></i><div class="mt-2">কোনো পেমেন্ট পাওয়া যায়নি।</div></div>
        <?php else: ?>
          <table class="pv-table">
            <thead>
              <tr>
                <th>#</th>
                <th>ইউজার</th>
                <th>সার্ভিস / অর্ডার</th>
                <th>মেথড</th>
                <th>সেন্ডার নম্বর</th>
                <th>TrxID</th>
                <th>টাকা</th>
                <th>সময়</th>
                <th>স্ট্যাটাস</th>
                <th>অ্যাকশন</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($payments as $p): ?>
                <?php
                  $mk = strtolower((string)$p['method']);
                  $meta = soko_pay_method_meta($mk);
                  $status = strtolower((string)$p['status']);
                  $badge = $status === 'verified' ? 'ok' : ($status === 'rejected' ? 'bad' : 'wait');
                  $status_bn = $status === 'verified' ? 'অনুমোদিত' : ($status === 'rejected' ? 'বাতিল' : 'যাচাই বাকি');
                ?>
                <tr>
                  <td class="pv-mono">#<?php echo (int)$p['id']; ?></td>
                  <td>
                    <div style="font-weight:700"><?php echo htmlspecialchars((string)$p['user_name']); ?></div>
                    <div style="font-size:11px;color:#64748B"><?php echo htmlspecialchars((string)$p['user_email']); ?></div>
                  </td>
                  <td>
                    <div style="font-weight:700"><?php echo htmlspecialchars((string)$p['service_name']); ?></div>
                    <div style="font-size:11px;color:#64748B">অর্ডার #<?php echo (int)$p['order_id']; ?></div>
                  </td>
                  <td><span class="pv-mk <?php echo htmlspecialchars($mk); ?>"><?php echo htmlspecialchars($meta['name']); ?></span></td>
                  <td class="pv-mono"><?php echo htmlspecialchars((string)$p['sender_number']); ?></td>
                  <td class="pv-mono"><?php echo htmlspecialchars((string)$p['trx_id']); ?></td>
                  <td style="font-weight:700">৳ <?php echo number_format((float)$p['amount'], 0); ?></td>
                  <td style="font-size:11.5px;color:#64748B"><?php echo htmlspecialchars((string)$p['created_at']); ?></td>
                  <td><span class="pv-badge <?php echo $badge; ?>"><?php echo $status_bn; ?></span></td>
                  <td>
                    <?php if ($status === 'pending'): ?>
                      <form method="get" action="payment_verify.php" style="margin:0">
                        <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter); ?>">
                        <input type="text" name="note" class="pv-note" placeholder="নোট (ঐচ্ছিক)">
                        <div class="d-flex gap-1 mt-1">
                          <button type="submit" name="action" value="approve" class="pv-btn ok"><i class="fa-solid fa-check"></i> অনুমোদন</button>
                          <button type="submit" name="action" value="reject" class="pv-btn bad"><i class="fa-solid fa-xmark"></i> বাতিল</button>
                        </div>
                      </form>
                    <?php else: ?>
                      <span style="font-size:11px;color:#64748B"><?php echo htmlspecialchars((string)$p['admin_note']); ?></span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<?php include_once("footer.php"); ?>
