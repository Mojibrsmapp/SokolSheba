<?php
/**
 * Sokol Sheba — shared helpers for the service order flow.
 *
 * Used by: service_order.php (per service order form) and receipt.php (unified receipt).
 * Keeps one source of truth for: logged in user, per-service field sets,
 * money/date formatting in Bangla and the manual payment channels.
 */

/* -------------------------------------------------------------------------
 * Small helpers
 * ---------------------------------------------------------------------- */

if (!function_exists('soko_h')) {
    function soko_h($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('soko_bn_digits')) {
    /** 2025 -> ২০২৫ */
    function soko_bn_digits($text)
    {
        $en = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $bn = array('০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯');
        return str_replace($en, $bn, (string)$text);
    }
}

if (!function_exists('soko_money')) {
    /** 250 -> "৳ ২৫০" ; soko_money(0, 2) -> "৳ ০.০০" */
    function soko_money($amount, $decimals = 0)
    {
        $formatted = number_format((float)$amount, $decimals, '.', ',');
        return '৳ ' . soko_bn_digits($formatted);
    }
}

if (!function_exists('soko_bn_date')) {
    /** "2026-11-06 10:24:11" -> "০৬ নভেম্বর ২০২৬, ১০:২৪" */
    function soko_bn_date($datetime)
    {
        $ts = strtotime((string)$datetime);
        if (!$ts) {
            return '—';
        }
        $months = array(
            1 => 'জানুয়ারি', 2 => 'ফেব্রুয়ারি', 3 => 'মার্চ', 4 => 'এপ্রিল',
            5 => 'মে', 6 => 'জুন', 7 => 'জুলাই', 8 => 'আগস্ট',
            9 => 'সেপ্টেম্বর', 10 => 'অক্টোবর', 11 => 'নভেম্বর', 12 => 'ডিসেম্বর',
        );
        $day = soko_bn_digits(date('d', $ts));
        $month = $months[(int)date('n', $ts)];
        $year = soko_bn_digits(date('Y', $ts));
        $time = soko_bn_digits(date('h:i', $ts));
        $meridiem = ((int)date('G', $ts) < 12) ? 'AM' : 'PM';
        return $day . ' ' . $month . ' ' . $year . ', ' . $time . ' ' . $meridiem;
    }
}

if (!function_exists('soko_status_bn')) {
    function soko_status_bn($status)
    {
        $map = array(
            'pending' => 'পেন্ডিং',
            'processing' => 'প্রসেসিং',
            'success' => 'সম্পন্ন',
            'completed' => 'সম্পন্ন',
            'cancel' => 'বাতিল',
            'cancelled' => 'বাতিল',
            'rejected' => 'বাতিল',
        );
        $key = strtolower(trim((string)$status));
        return isset($map[$key]) ? $map[$key] : ucfirst($key);
    }
}

if (!function_exists('soko_status_class')) {
    function soko_status_class($status)
    {
        $key = strtolower(trim((string)$status));
        if ($key === 'pending' || $key === 'processing') {
            return 'is-pending';
        }
        if ($key === 'success' || $key === 'completed') {
            return 'is-done';
        }
        if ($key === 'cancel' || $key === 'cancelled' || $key === 'rejected') {
            return 'is-cancel';
        }
        return 'is-pending';
    }
}

/* -------------------------------------------------------------------------
 * Logged in user
 * ---------------------------------------------------------------------- */

if (!function_exists('soko_current_user')) {
    /**
     * Returns: ['id','name','email','mobile','balance','token'] or null.
     * Supports both session styles used across the project:
     *   $_SESSION['user_token']  (token column)  and  $_SESSION['user_id'] (base64 email).
     */
    function soko_current_user($conn)
    {
        $columns = "id, name, email, mobile, balance, token";

        if (isset($_SESSION['user_token']) && $_SESSION['user_token'] !== '') {
            $token = $_SESSION['user_token'];
            $stmt = $conn->prepare("SELECT $columns FROM users WHERE token = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("s", $token);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $stmt->close();
                    return $row;
                }
                $stmt->close();
            }
        }

        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] !== '') {
            $email = base64_decode($_SESSION['user_id']);
            if ($email !== false && $email !== '') {
                $stmt = $conn->prepare("SELECT $columns FROM users WHERE email = ? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result && $result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $stmt->close();
                        return $row;
                    }
                    $stmt->close();
                }
            }
        }

        return null;
    }
}

/* -------------------------------------------------------------------------
 * Service + per service fields
 * ---------------------------------------------------------------------- */

if (!function_exists('soko_get_service')) {
    /**
     * Load one service from custom_services either by id or by name.
     */
    function soko_get_service($conn, $service_id, $service_name)
    {
        $service_id = (int)$service_id;
        if ($service_id > 0) {
            $stmt = $conn->prepare("SELECT * FROM custom_services WHERE id = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("i", $service_id);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
                $stmt->close();
                if ($row) {
                    return $row;
                }
            }
        }

        $service_name = trim((string)$service_name);
        if ($service_name !== '') {
            $stmt = $conn->prepare("SELECT * FROM custom_services WHERE service_name = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("s", $service_name);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
                $stmt->close();
                if ($row) {
                    return $row;
                }
            }
        }

        return null;
    }
}

if (!function_exists('soko_field')) {
    function soko_field($key, $title, $type, $required, $placeholder, $icon, $accept)
    {
        return array(
            'key' => $key,
            'title' => $title,
            'type' => $type,
            'required' => $required ? 1 : 0,
            'placeholder' => $placeholder,
            'icon' => $icon,
            'accept' => $accept,
        );
    }
}

if (!function_exists('soko_service_presets')) {
    /**
     * Field sets per service. The first keyword found inside the service name wins,
     * so every service can ask for completely different information.
     */
    function soko_service_presets()
    {
        $card_front = soko_field('nid_front', 'NID কার্ডের সামনের ছবি', 'file', 1, 'সামনের অংশ আপলোড করুন', 'fa-image', 'image/*');
        $card_back = soko_field('nid_back', 'NID কার্ডের পেছনের ছবি', 'file', 1, 'পেছনের অংশ আপলোড করুন', 'fa-image', 'image/*');
        $photo = soko_field('photo', 'আবেদনকারীর ছবি', 'file', 0, 'ছবি আপলোড করুন (ঐচ্ছিক)', 'fa-camera', 'image/*');
        $name = soko_field('name', 'নাম', 'text', 1, 'আপনার পূর্ণ নাম লিখুন', 'fa-user', '');
        $mobile = soko_field('mobile', 'মোবাইল নম্বর', 'tel', 1, '১১ ডিজিটের মোবাইল নম্বর লিখুন', 'fa-phone', '');
        $nid = soko_field('nid', 'NID নম্বর', 'text', 1, '১০ / ১৩ / ১৭ ডিজিটের NID নম্বর', 'fa-id-card', '');
        $nid_any = soko_field('nid', 'NID / ফরম / ভোটার নম্বর', 'text', 1, 'NID অথবা ফরম নম্বর লিখুন', 'fa-id-card', '');
        $dob = soko_field('dob', 'জন্ম তারিখ', 'date', 1, '', 'fa-calendar-days', '');
        $dob_opt = soko_field('dob', 'জন্ম তারিখ', 'date', 0, '', 'fa-calendar-days', '');
        $brn = soko_field('brn', 'জন্ম নিবন্ধন নম্বর', 'text', 1, '১৭ ডিজিটের জন্ম নিবন্ধন নম্বর', 'fa-file-lines', '');
        $details = soko_field('details', 'বিস্তারিত তথ্য', 'textarea', 1, 'প্রয়োজনীয় সব তথ্য বিস্তারিতভাবে লিখুন', 'fa-pen', '');
        $pdf = soko_field('pdf_file', 'পিডিএফ ফাইল', 'file', 1, 'PDF আপলোড করুন (সর্বোচ্চ ৫MB)', 'fa-file-pdf', '.pdf');
        $tin = soko_field('tin', 'TIN নম্বর', 'text', 1, 'TIN নম্বর লিখুন', 'fa-file-invoice', '');

        return array(
            // keyword => fields
            'সাইন কপি' => array($name, $mobile, $nid, $dob, $card_front, $card_back),
            'সাইন' => array($nid_any, $name, $mobile, $dob, $card_front),
            'আইডি কার্ড' => array($name, $nid, $dob, $card_front, $card_back),
            'সার্ভার কপি' => array($nid, $dob, $card_front),
            'স্মার্ট' => array($nid, $dob, $photo),
            'পাসওয়ার্ড' => array($nid, $dob, $mobile),
            'মোবাইল নাম্বার' => array($mobile, $nid),
            'জন্ম নিবন্ধন' => array($brn, $dob, $name, $mobile),
            'ভোটার' => array($details, $mobile),
            'সংশোধন' => array($nid, $details, $mobile),
            'টিন' => array($tin, $name, $mobile, $nid),
            'পাসপোর্ট' => array(soko_field('passport', 'পাসপোর্ট নম্বর', 'text', 1, 'পাসপোর্ট নম্বর লিখুন', 'fa-passport', ''), $dob, $name, $pdf),
            'কল' => array($mobile, soko_field('duration', 'কত দিনের তথ্য', 'text', 1, 'যেমন: ৩০ দিন', 'fa-clock', '')),
            'লোকেশন' => array($mobile, $name),
        );
    }
}

if (!function_exists('soko_match_preset')) {
    function soko_match_preset($service_name)
    {
        $service_name = (string)$service_name;
        foreach (soko_service_presets() as $keyword => $fields) {
            if (strpos($service_name, $keyword) !== false) {
                return $fields;
            }
        }
        return null;
    }
}

if (!function_exists('soko_resolve_fields')) {
    /**
     * Returns the field list for one service:
     *   1. built in preset (per service type), otherwise
     *   2. the JSON stored in custom_services.custom_fields, otherwise
     *   3. one free text field.
     */
    function soko_resolve_fields($service)
    {
        $preset = soko_match_preset(isset($service['service_name']) ? $service['service_name'] : '');
        if (is_array($preset) && count($preset) > 0) {
            return $preset;
        }

        $db_fields = array();
        if (!empty($service['custom_fields'])) {
            $decoded = json_decode($service['custom_fields'], true);
            if (is_array($decoded)) {
                $db_fields = $decoded;
            }
        }

        $fields = array();
        $index = 0;
        foreach ($db_fields as $raw) {
            $title = '';
            if (isset($raw['title'])) {
                $title = trim((string)$raw['title']);
            }
            if ($title === '') {
                $index++;
                continue;
            }
            $type = isset($raw['type']) ? strtolower(trim((string)$raw['type'])) : 'text';
            if ($type === 'number' || $type === 'phone' || $type === 'mobile') {
                $type = 'tel';
            }
            if ($type !== 'text' && $type !== 'tel' && $type !== 'date' && $type !== 'textarea' && $type !== 'file') {
                $type = 'text';
            }
            $required = 0;
            if (!empty($raw['required'])) {
                $required = 1;
            }
            $placeholder = isset($raw['placeholder']) ? trim((string)$raw['placeholder']) : '';
            if ($placeholder === '' && $type !== 'date' && $type !== 'file') {
                $placeholder = $title . ' লিখুন';
            }
            $icon = 'fa-pen';
            if ($type === 'tel') {
                $icon = 'fa-phone';
            } elseif ($type === 'date') {
                $icon = 'fa-calendar-days';
            } elseif ($type === 'file') {
                $icon = 'fa-cloud-arrow-up';
            } elseif ($type === 'textarea') {
                $icon = 'fa-align-left';
            }
            $fields[] = soko_field('field_' . $index, $title, $type, $required, $placeholder, $icon, $type === 'file' ? '' : '');
            $index++;
        }

        if (count($fields) === 0) {
            $fields[] = soko_field('field_0', 'প্রয়োজনীয় তথ্য', 'textarea', 1, 'অর্ডারের জন্য প্রয়োজনীয় তথ্য লিখুন', 'fa-pen', '');
        }

        return $fields;
    }
}

/* -------------------------------------------------------------------------
 * Payment channels (manual mobile banking / bank)
 * ---------------------------------------------------------------------- */

if (!function_exists('soko_payment_channels')) {
    function soko_payment_channels($conn)
    {
        $channels = array(
            'bkash' => array('name' => 'বিকাশ', 'key' => 'bkash', 'number' => '', 'type' => 'Personal', 'color' => '#E2136E'),
            'nagad' => array('name' => 'নগদ', 'key' => 'nagad', 'number' => '', 'type' => 'Personal', 'color' => '#F58220'),
            'rocket' => array('name' => 'রকেট', 'key' => 'rocket', 'number' => '', 'type' => 'Personal', 'color' => '#8C3494'),
        );

        if (function_exists('get_site_setting')) {
            foreach ($channels as $key => $meta) {
                $value = get_site_setting($conn, $key . '_number', '');
                if ($value !== '') {
                    $channels[$key]['number'] = $value;
                }
            }
        }

        $result = $conn->query("SELECT method_key, method_name, number, type FROM payment_settings ORDER BY id ASC");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $key = strtolower(trim((string)$row['method_key']));
                if (isset($channels[$key]) && $channels[$key]['number'] === '' && trim((string)$row['number']) !== '') {
                    $channels[$key]['number'] = trim((string)$row['number']);
                    $channels[$key]['type'] = trim((string)$row['type']);
                }
            }
        }

        return $channels;
    }
}

if (!function_exists('soko_payment_label')) {
    function soko_payment_label($method)
    {
        $map = array(
            'mobile' => 'মোবাইল ব্যাংকিং',
            'mobile_banking' => 'মোবাইল ব্যাংকিং',
            'bank' => 'ব্যাংক ট্রান্সফার',
            'wallet' => 'ওয়ালেট ব্যালেন্স',
        );
        $key = strtolower(trim((string)$method));
        return isset($map[$key]) ? $map[$key] : 'মোবাইল ব্যাংকিং';
    }
}

/* -------------------------------------------------------------------------
 * Order input storage (orders.input_data is a plain text column)
 * ---------------------------------------------------------------------- */

if (!function_exists('soko_input_to_text')) {
    /** ['নাম' => 'করিম', 'mobile' => '017..'] -> "নাম: করিম | মোবাইল: 017..." */
    function soko_input_to_text($pairs)
    {
        $chunks = array();
        foreach ($pairs as $label => $value) {
            $label = trim((string)$label);
            $value = trim((string)$value);
            if ($label === '') {
                continue;
            }
            if ($value === '') {
                $value = '—';
            }
            $chunks[] = $label . ': ' . $value;
        }
        return implode(' | ', $chunks);
    }
}

if (!function_exists('soko_text_to_input')) {
    /** "নাম: করিম | মোবাইল: 017..." -> [['label'=>'নাম','value'=>'করিম'], ...] */
    function soko_text_to_input($text)
    {
        $rows = array();
        $text = trim((string)$text);
        if ($text === '') {
            return $rows;
        }
        $chunks = explode(' | ', $text);
        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') {
                continue;
            }
            $pos = strpos($chunk, ': ');
            if ($pos === false) {
                $rows[] = array('label' => '', 'value' => $chunk);
            } else {
                $rows[] = array(
                    'label' => substr($chunk, 0, $pos),
                    'value' => substr($chunk, $pos + 2),
                );
            }
        }
        return $rows;
    }
}

if (!function_exists('soko_read_payment_note')) {
    /**
     * Payment info is kept at the end of input_data as a hidden row:
     *   "__payment__: wallet"
     * Receipt reads it back and hides it from the visible list.
     */
    function soko_read_payment_note($input_text)
    {
        $rows = soko_text_to_input($input_text);
        $clean = array();
        $payment = 'wallet';
        foreach ($rows as $row) {
            if ($row['label'] === '__payment__') {
                $payment = $row['value'];
                continue;
            }
            $clean[] = $row;
        }
        return array('payment' => $payment, 'rows' => $clean);
    }
}

if (!function_exists('soko_order_by_id')) {
    function soko_order_by_id($conn, $order_id, $user_id)
    {
        $order_id = (int)$order_id;
        $user_id = (int)$user_id;
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1");
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
        $stmt->close();
        return $row;
    }
}

if (!function_exists('soko_service_icon')) {
    function soko_service_icon($service_name, $category)
    {
        $haystack = (string)$service_name . ' ' . (string)$category;
        if (strpos($haystack, 'সাইন') !== false) {
            return 'fa-signature';
        }
        if (strpos($haystack, 'কার্ড') !== false) {
            return 'fa-id-card';
        }
        if (strpos($haystack, 'সার্ভার') !== false) {
            return 'fa-server';
        }
        if (strpos($haystack, 'স্মার্ট') !== false) {
            return 'fa-mobile-screen';
        }
        if (strpos($haystack, 'টিন') !== false || strpos($haystack, 'TIN') !== false) {
            return 'fa-file-invoice';
        }
        if (strpos($haystack, 'জন্ম') !== false) {
            return 'fa-baby';
        }
        if (strpos($haystack, 'পাসপোর্ট') !== false) {
            return 'fa-passport';
        }
        if (strpos($haystack, 'ভোটার') !== false) {
            return 'fa-users';
        }
        return 'fa-layer-group';
    }
}

if (!function_exists('soko_sanitize_filename')) {
    function soko_sanitize_filename($name)
    {
        $name = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', (string)$name);
        return trim($name, '_');
    }
}

if (!function_exists('soko_handle_upload')) {
    /**
     * Stores one uploaded file inside uploads/orders and returns the relative path.
     * Returns array('path' => '...' , 'error' => '').
     */
    function soko_handle_upload($file, $user_id, $field_key)
    {
        $result = array('path' => '', 'error' => '');

        if (!isset($file) || !is_array($file) || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            $result['error'] = 'ফাইল পাওয়া যায়নি';
            return $result;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $result['error'] = 'ফাইল আপলোডে সমস্যা হয়েছে';
            return $result;
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            $result['error'] = 'ফাইলের সাইজ ৫MB এর বেশি হতে পারবে না';
            return $result;
        }

        $allowed = array('jpg', 'jpeg', 'png', 'webp', 'pdf');
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowed, true)) {
            $result['error'] = 'শুধুমাত্র JPG, PNG, WEBP অথবা PDF আপলোড করুন';
            return $result;
        }

        $upload_dir = __DIR__ . '/../uploads/orders/';
        if (!is_dir($upload_dir)) {
            @mkdir($upload_dir, 0755, true);
        }
        if (!is_dir($upload_dir)) {
            $result['error'] = 'আপলোড ফোল্ডার তৈরি করা যায়নি';
            return $result;
        }

        $base = soko_sanitize_filename(pathinfo($file['name'], PATHINFO_FILENAME));
        if ($base === '') {
            $base = 'file';
        }
        if (strlen($base) > 40) {
            $base = substr($base, 0, 40);
        }
        $new_name = 'ord_' . (int)$user_id . '_' . $field_key . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;

        if (!move_uploaded_file($file['tmp_name'], $upload_dir . $new_name)) {
            $result['error'] = 'ফাইল সংরক্ষণ করা যায়নি';
            return $result;
        }

        $result['path'] = 'uploads/orders/' . $new_name;
        return $result;
    }
}
