<?php
require_once __DIR__ . '/service_catalog.php';

/** Only the authenticated owner's orders; never trust a user id from the URL. */
function soko_customer_orders($conn, $token, $filter = [], $page = 1) {
    if (!is_string($token) || $token === '') return ['rows' => [], 'more' => false, 'error' => false];
    $page = max(1, min(100000, (int)$page));
    $sql = 'SELECT o.* FROM orders o INNER JOIN users u ON u.id = o.user_id WHERE u.token = ?';
    $types = 's';
    $params = [$token];
    if (!empty($filter['service'])) {
        $sql .= ' AND o.order_type = ?';
        $types .= 's'; $params[] = $filter['service'];
    } elseif (!empty($filter['category'])) {
        $sql .= ' AND o.order_type IN (SELECT service_name FROM custom_services WHERE category = ?)';
        $types .= 's'; $params[] = $filter['category'];
    }
    $sql .= ' ORDER BY o.id DESC LIMIT 21 OFFSET ' . (($page - 1) * 20);
    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new RuntimeException('History unavailable');
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) { $stmt->close(); throw new RuntimeException('History unavailable'); }
        $result = $stmt->get_result();
        $rows = [];
        if ($result) while ($row = $result->fetch_assoc()) $rows[] = $row;
        $stmt->close();
        return ['rows' => array_slice($rows, 0, 20), 'more' => count($rows) > 20, 'error' => false];
    } catch (Throwable $error) {
        error_log('Customer order history could not be loaded.');
        return ['rows' => [], 'more' => false, 'error' => true];
    }
}

function soko_order_result_has_file($order) {
    $file = trim((string)($order['result_file'] ?? ''));
    if ($file === '') return false;
    // Old file-order forms put the customer's INPUT attachment in result_file.
    // Never present that submitted document as an administrator's result.
    if (preg_match('~^(?:/?|\.\./)(?:admin/)?uploads/orders/~', $file)) return false;
    return true;
}

/** Resolve both historical admin upload locations, restricted to result folders. */
function soko_order_result_path($file, $root = null) {
    $root = $root ?? dirname(__DIR__);
    $file = ltrim(trim((string)$file), '/');
    if (!preg_match('~^(?:admin/)?uploads/results/[a-zA-Z0-9_.-]+\.(?:pdf|jpg|jpeg|png|webp|zip|txt)$~i', $file)) return null;
    $candidates = [$root . '/' . $file];
    if (strpos($file, 'uploads/results/') === 0) $candidates[] = $root . '/admin/' . $file;
    $allowed = [$root . '/uploads/results', $root . '/admin/uploads/results'];
    $matches = [];
    foreach ($candidates as $candidate) {
        $real = realpath($candidate);
        if (!$real || !is_file($real)) continue;
        foreach ($allowed as $directory) {
            $base = realpath($directory);
            if ($base && strpos($real, $base . DIRECTORY_SEPARATOR) === 0) $matches[$real] = true;
        }
    }
    // Ambiguous legacy paths must not accidentally download another order's file.
    return count($matches) === 1 ? array_key_first($matches) : null;
}

function soko_render_order_result($order) {
    $text = trim((string)($order['result_text'] ?? ''));
    $file = soko_order_result_has_file($order);
    $birth = strpos((string)($order['order_type'] ?? ''), 'জন্ম') !== false || stripos((string)($order['order_type'] ?? ''), 'birth') !== false;
    echo '<div class="ss-order-result"><strong>' . ($birth ? 'রেজাল্ট / জন্ম নিবন্ধন নম্বর' : 'অর্ডারের রেজাল্ট') . '</strong>';
    if ($text !== '') {
        echo '<p class="ss-result-text">' . nl2br(soko_h($text)) . '</p>';
        if (filter_var($text, FILTER_VALIDATE_URL) && preg_match('~^https?://~i', $text)) {
            echo '<a class="ss-history-link" href="' . soko_h($text) . '" target="_blank" rel="noopener noreferrer">রেজাল্ট লিংক খুলুন ↗</a>';
        }
    }
    if ($file) echo '<a class="ss-history-link" href="/order_result.php?order=' . (int)$order['id'] . '">রেজাল্ট ফাইল ডাউনলোড ↓</a>';
    if ($text === '' && !$file) {
        $status = strtolower((string)($order['status'] ?? 'pending'));
        if (in_array($status, ['cancel', 'cancelled', 'rejected'], true)) echo '<p>অর্ডার বাতিল হয়েছে। বিস্তারিত জানতে সাপোর্টে যোগাযোগ করুন।</p>';
        elseif (in_array($status, ['success', 'completed', 'complete'], true)) echo '<p>অর্ডার সম্পন্ন দেখানো হয়েছে, তবে রেজাল্ট এখনও যুক্ত হয়নি। সাপোর্টে যোগাযোগ করুন।</p>';
        else echo '<p>' . (soko_is_new_birth_application($order['order_type'] ?? '') ? 'নতুন আবেদন করতে আগে থেকে জন্ম নিবন্ধন নম্বর লাগে না। নম্বর ইস্যু হলে অ্যাডমিনের দেওয়া নম্বর/সনদ এখানেই পাবেন। অর্ডার নম্বর জন্ম নিবন্ধন নম্বর নয়।' : 'অর্ডার প্রক্রিয়াধীন। অ্যাডমিন রেজাল্ট দিলে এখানে দেখতে পাবেন।') . '</p>';
    }
    echo '</div>';
}

function soko_render_order_history($data, $page = null) {
    echo '<section class="ss-order-history" id="service-history" aria-labelledby="ss-history-title"><div class="ss-history-heading"><div><h2 id="ss-history-title">অর্ডার হিস্টোরি ও রেজাল্ট</h2><p>আপনার আবেদন, বর্তমান অবস্থা ও ফলাফল</p></div><a class="ss-history-link" href="/work_history.php">সকল অর্ডার →</a></div>';
    if (!empty($data['error'])) echo '<p role="status">হিস্টোরি লোড করা যায়নি। পেজ রিফ্রেশ করে আবার চেষ্টা করুন।</p>';
    elseif (empty($data['rows'])) echo '<p class="ss-history-empty">এখনও কোনো অর্ডার নেই। অর্ডার করলে তার অবস্থা ও রেজাল্ট এখানে দেখাবে।</p>';
    else {
        echo '<div class="ss-history-grid">';
        foreach ($data['rows'] as $order) {
            $status = strtolower((string)$order['status']);
            $state = in_array($status, ['success', 'completed', 'complete'], true) ? 'success' : (in_array($status, ['cancel', 'cancelled', 'rejected'], true) ? 'cancel' : 'pending');
            echo '<article class="ss-history-order"><div class="ss-order-top"><span>অর্ডার #' . (int)$order['id'] . '</span><span class="ss-order-state ss-state-' . $state . '">' . soko_h(soko_status_bn($status)) . '</span></div><h3>' . soko_h($order['order_type']) . '</h3><div class="ss-order-meta"><span>৳ ' . number_format((float)$order['price'], 2) . '</span><time>' . soko_h($order['created_at'] ?? '') . '</time></div>';
            soko_render_order_result($order);
            echo '<a class="ss-history-link" href="/receipt.php?order=' . (int)$order['id'] . '">অর্ডারের বিবরণ / রশিদ →</a></article>';
        }
        echo '</div>';
    }
    if ($page !== null && ($page > 1 || !empty($data['more']))) {
        echo '<nav class="ss-history-pagination" aria-label="হিস্টোরির পৃষ্ঠা">';
        foreach ([-1 => '← আগের পৃষ্ঠা', 1 => 'পরের পৃষ্ঠা →'] as $step => $label) {
            if (($step < 0 && $page <= 1) || ($step > 0 && empty($data['more']))) continue;
            $query = $_GET;
            $query['history_page'] = $page + $step;
            echo '<a class="ss-history-link" href="?' . soko_h(http_build_query($query)) . '#service-history">' . $label . '</a>';
        }
        echo '</nav>';
    }
    echo '</section>';
}
