<?php
/**
 * Sokol Sheba — manual payment flow (send money + TrxID).
 *
 * Flow:
 *   service_order.php  -> order created (wallet paid / unpaid)
 *   payment_gateway.php-> user picks bKash / Nagad / Rocket / Upay, sends money,
 *                         submits sender number + TrxID  -> order_payments (pending)
 *   payment_processing.php -> waits for the admin verification
 *   admin/payment_verify.php -> admin marks the TrxID verified / rejected
 *   receipt.php        -> one A4 payment receipt for every service
 */

require_once __DIR__ . '/service_catalog.php';

if (!function_exists('soko_pay_ensure_table')) {
    /** Creates the payments table on first use (no manual SQL needed). */
    function soko_pay_ensure_table($conn)
    {
        static $done = false;
        if ($done) {
            return;
        }
        $sql = "CREATE TABLE IF NOT EXISTS `order_payments` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11) NOT NULL,
            `user_id` int(11) NOT NULL,
            `method` varchar(20) NOT NULL DEFAULT 'bkash',
            `sender_number` varchar(20) NOT NULL DEFAULT '',
            `trx_id` varchar(64) NOT NULL DEFAULT '',
            `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
            `status` varchar(20) NOT NULL DEFAULT 'pending',
            `admin_note` varchar(255) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `verified_at` datetime DEFAULT NULL,
            `slip_file` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_trx` (`trx_id`),
            KEY `idx_order` (`order_id`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        @$conn->query($sql);

        /* পুরনো ডাটাবেজে slip_file কলাম না থাকলে যোগ করে নেয় */
        $has_slip = @$conn->query("SHOW COLUMNS FROM `order_payments` LIKE 'slip_file'");
        if (!$has_slip || $has_slip->num_rows === 0) {
            @$conn->query("ALTER TABLE `order_payments` ADD COLUMN `slip_file` varchar(255) DEFAULT NULL");
        }

        $done = true;
    }
}

if (!function_exists('soko_pay_methods')) {
    /** The four mobile banking channels we accept. */
    function soko_pay_methods()
    {
        return array(
            'bkash' => array('name' => 'বিকাশ', 'english' => 'bKash', 'css' => 'mk-bkash', 'color' => '#E2136E', 'logo' => 'assets/images/payments/bkash.png'),
            'nagad' => array('name' => 'নগদ', 'english' => 'Nagad', 'css' => 'mk-nagad', 'color' => '#F58220', 'logo' => 'assets/images/payments/nagad.png'),
            'rocket' => array('name' => 'রকেট', 'english' => 'Rocket', 'css' => 'mk-rocket', 'color' => '#8C3494', 'logo' => 'assets/images/payments/rocket.png'),
            'upay' => array('name' => 'উপায়', 'english' => 'Upay', 'css' => 'mk-upay', 'color' => '#F5A623', 'logo' => 'assets/images/payments/upay.png'),
        );
    }
}

if (!function_exists('soko_pay_method_meta')) {
    function soko_pay_method_meta($method)
    {
        $methods = soko_pay_methods();
        $key = strtolower(trim((string)$method));
        if (isset($methods[$key])) {
            return $methods[$key];
        }
        return array('name' => 'মোবাইল ব্যাংকিং', 'english' => 'Mobile Banking', 'css' => 'mk-bkash', 'color' => '#0B7A3B', 'logo' => 'assets/images/payments/bkash.png');
    }
}

if (!function_exists('soko_pay_method_logo')) {
    /** Payment logo path (assets/images/payments/*.png) — না থাকলে খালি স্ট্রিং। */
    function soko_pay_method_logo($method)
    {
        $methods = soko_pay_methods();
        $key = strtolower(trim((string)$method));
        if (!isset($methods[$key]['logo'])) {
            return '';
        }
        $relative = $methods[$key]['logo'];
        if (!file_exists(__DIR__ . '/../' . $relative)) {
            return '';
        }
        return $relative;
    }
}

if (!function_exists('soko_pay_merchant_numbers')) {
    /** ["bkash" => "019...", "nagad" => "..."] from site_settings, fallback payment_settings. */
    function soko_pay_merchant_numbers($conn)
    {
        $numbers = array();
        foreach (soko_pay_methods() as $key => $meta) {
            $numbers[$key] = '';
        }

        if (function_exists('get_site_setting')) {
            foreach ($numbers as $key => $n) {
                $value = trim((string)get_site_setting($conn, $key . '_number', ''));
                if ($value !== '') {
                    $numbers[$key] = $value;
                }
            }
        }

        $result = @$conn->query("SELECT method_key, number FROM payment_settings ORDER BY id ASC");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $key = strtolower(trim((string)$row['method_key']));
                if (isset($numbers[$key]) && $numbers[$key] === '') {
                    $numbers[$key] = trim((string)$row['number']);
                }
            }
        }

        $fallback = '';
        foreach ($numbers as $key => $n) {
            if ($n !== '') {
                $fallback = $n;
                break;
            }
        }
        foreach ($numbers as $key => $n) {
            if ($n === '') {
                $numbers[$key] = $fallback;
            }
        }

        return $numbers;
    }
}

if (!function_exists('soko_pay_latest')) {
    /** Latest payment attempt of one order, or null. */
    function soko_pay_latest($conn, $order_id)
    {
        soko_pay_ensure_table($conn);
        $order_id = (int)$order_id;
        $stmt = $conn->prepare("SELECT * FROM order_payments WHERE order_id = ? ORDER BY id DESC LIMIT 1");
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
        $stmt->close();
        return $row;
    }
}

if (!function_exists('soko_pay_create')) {
    /**
     * Stores one "send money" claim. Returns:
     *   ['ok' => true, 'payment_id' => n]  or  ['ok' => false, 'error' => '...']
     */
    function soko_pay_create($conn, $order_id, $user_id, $method, $sender_number, $trx_id, $amount, $slip_file = '')
    {
        soko_pay_ensure_table($conn);

        $method = strtolower(trim($method));
        $methods = soko_pay_methods();
        if (!isset($methods[$method])) {
            return array('ok' => false, 'error' => 'পেমেন্ট পদ্ধতি নির্বাচন করুন।');
        }

        /* নম্বর ঐচ্ছিক — দিলে তবেই ফরম্যাট দেখা হয় */
        $sender_digits = preg_replace('/[^0-9]/', '', $sender_number);
        if ($sender_digits !== '' && strlen($sender_digits) !== 11) {
            return array('ok' => false, 'error' => 'যে নম্বর থেকে টাকা পাঠিয়েছেন সেটি ১১ ডিজিটের হতে হবে (না দিলে খালি রাখুন)।');
        }

        $trx_id = strtoupper(trim(preg_replace('/[^A-Za-z0-9]/', '', $trx_id)));
        if (strlen($trx_id) < 6 || strlen($trx_id) > 40) {
            return array('ok' => false, 'error' => 'ট্রানজেকশন আইডি (TrxID) সঠিকভাবে লিখুন (কমপক্ষে ৬ অক্ষর)।');
        }

        if (soko_pay_trx_exists($conn, $trx_id)) {
            return array('ok' => false, 'error' => 'এই TrxID আগেই ব্যবহার করা হয়েছে। সঠিক TrxID দিন।');
        }

        $order_id = (int)$order_id;
        $user_id = (int)$user_id;
        $amount = (float)$amount;

        $slip_file = trim((string)$slip_file);
        $stmt = $conn->prepare("INSERT INTO order_payments (order_id, user_id, method, sender_number, trx_id, amount, slip_file, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        if (!$stmt) {
            return array('ok' => false, 'error' => 'পেমেন্ট তথ্য সংরক্ষণ করা যায়নি। আবার চেষ্টা করুন।');
        }
        $stmt->bind_param("iisssds", $order_id, $user_id, $method, $sender_digits, $trx_id, $amount, $slip_file);
        $ok = $stmt->execute();
        $errno = $conn->errno;
        $insert_id = $conn->insert_id;
        $stmt->close();

        if (!$ok) {
            if ((int)$errno === 1062) {
                return array('ok' => false, 'error' => 'এই TrxID আগেই ব্যবহার করা হয়েছে। সঠিক TrxID দিন।');
            }
            return array('ok' => false, 'error' => 'পেমেন্ট তথ্য সংরক্ষণ করা যায়নি। আবার চেষ্টা করুন।');
        }

        return array('ok' => true, 'payment_id' => $insert_id);
    }
}

if (!function_exists('soko_pay_trx_exists')) {
    function soko_pay_trx_exists($conn, $trx_id)
    {
        $stmt = $conn->prepare("SELECT id FROM order_payments WHERE trx_id = ? LIMIT 1");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("s", $trx_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = ($res && $res->num_rows > 0);
        $stmt->close();
        return $exists;
    }
}

if (!function_exists('soko_pay_status_bn')) {
    function soko_pay_status_bn($status)
    {
        $map = array(
            'pending' => 'যাচাই চলছে',
            'verified' => 'পেমেন্ট সফল',
            'rejected' => 'পেমেন্ট বাতিল',
        );
        $key = strtolower(trim((string)$status));
        return isset($map[$key]) ? $map[$key] : 'যাচাই চলছে';
    }
}

if (!function_exists('soko_pay_update_status')) {
    function soko_pay_update_status($conn, $payment_id, $status, $admin_note)
    {
        $payment_id = (int)$payment_id;
        $status = strtolower(trim($status));
        if ($status !== 'verified' && $status !== 'rejected' && $status !== 'pending') {
            return false;
        }
        $stmt = $conn->prepare("UPDATE order_payments SET status = ?, admin_note = ?, verified_at = NOW() WHERE id = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("ssi", $status, $admin_note, $payment_id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}

if (!function_exists('soko_pending_payment_count')) {
    function soko_pending_payment_count($conn)
    {
        soko_pay_ensure_table($conn);
        $result = @$conn->query("SELECT COUNT(id) AS c FROM order_payments WHERE status = 'pending'");
        if ($result && $row = $result->fetch_assoc()) {
            return (int)$row['c'];
        }
        return 0;
    }
}

if (!function_exists('soko_receipt_no')) {
    /** #SS20260918001 — deterministic per order. */
    function soko_receipt_no($order)
    {
        $ts = strtotime(isset($order['created_at']) ? $order['created_at'] : 'now');
        if (!$ts) {
            $ts = time();
        }
        $seq = (int)$order['id'] % 1000;
        return 'SS' . date('Ymd', $ts) . sprintf('%03d', $seq);
    }
}

if (!function_exists('soko_order_payment_state')) {
    /**
     * 'wallet' | 'verified' | 'pending' | 'rejected' | 'unpaid'
     */
    function soko_order_payment_state($conn, $order)
    {
        $parsed = soko_read_payment_note(isset($order['input_data']) ? $order['input_data'] : '');
        if ($parsed['payment'] === 'wallet') {
            return 'wallet';
        }
        $payment = soko_pay_latest($conn, (int)$order['id']);
        if (!$payment) {
            return 'unpaid';
        }
        $status = strtolower(trim((string)$payment['status']));
        if ($status === 'verified') {
            return 'verified';
        }
        if ($status === 'rejected') {
            return 'rejected';
        }
        return 'pending';
    }
}
