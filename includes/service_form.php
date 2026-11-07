<?php
/** Field contracts shared by the catalog, admin editor and customer validation. */
function soko_application_kind($name) {
    $name = strtolower((string)$name);
    foreach (['সংশোধন', 'correction', 'নবায়ন', 'নবায়ন', 'renew', 'যাচাই', 'verification', 'verify', 'status', 'স্ট্যাটাস'] as $word) {
        if (strpos($name, $word) !== false) return '';
    }
    if (soko_is_new_birth_application($name)) return 'birth';
    $application = preg_match('/\b(new|application|apply)\b/u', $name) || strpos($name, 'নতুন') !== false || strpos($name, 'আবেদন') !== false;
    if (!$application) return '';
    if (strpos($name, 'পাসপোর্ট') !== false || strpos($name, 'passport') !== false) return 'passport';
    if (preg_match('/\bnid\b|national\s+(?:id|identity)|এন\s*আই\s*ডি|জাতীয় পরিচয়পত্র|জাতীয় পরিচয়পত্র|ভোটার নিবন্ধন|new voter/u', $name)) return 'nid';
    return '';
}
function soko_is_mfs_info($name) {
    return preg_match('/বিকাশ|নগদ|রকেট|উপায়|উপায়|bkash|nagad|rocket|upay/iu', $name)
        && preg_match('/ইনফো|info|তথ্য/iu', $name);
}
function soko_field_semantic_key($title) {
    $title = preg_replace('/[\s:ঃ*]+$/u', '', strtolower(trim((string)$title)));
    $rules = [
        'birth_pdf' => '/(?:জন্ম.*নিবন্ধন|birth).*(?:pdf|পিডিএফ)/u',
        'father_name' => '/পিতা|বাবার|father/u', 'mother_name' => '/মাতা|মায়ের|মায়ের|mother/u',
        'brn' => '/জন্ম.*নিবন্ধন.*(?:নম্বর|নাম্বার|নং)|\bbrn\b|birth.*(?:number|no\.)/u',
        'passport' => '/(?:পাসপোর্ট|passport).*(?:নম্বর|নাম্বার|number|no\.)/u',
        'nid' => '/(?:nid|এন\s*আই\s*ডি|পরিচয়পত্র|পরিচয়পত্র).*(?:নম্বর|নাম্বার|নং|number|no\.)/u',
        'trx' => '/trx|transaction|ট্রানজেকশন/u', 'dob' => '/জন্ম.*তারিখ|date.*birth/u',
        'mobile' => '/মোবাইল|যোগাযোগ.*নম্বর|phone|mobile/u',
        'village' => '/গ্রাম|village/u', 'thana' => '/থানা|উপজেলা|thana|upazila/u',
        'district' => '/জেলা|district/u', 'division' => '/বিভাগ|division/u',
        'address' => '/ঠিকানা|address/u', 'name' => '/^(নিজ নাম|নিজের নাম|আবেদনকারীর নাম|পূর্ণ নাম|নাম|name|full name)$/u',
    ];
    foreach ($rules as $key => $pattern) if (preg_match($pattern, $title)) return $key;
    return '';
}
function soko_application_contracts($fields, $name) {
    $kind = soko_application_kind($name);
    $out = [];
    foreach ($fields as $field) {
        $semantic = soko_field_semantic_key($field['title']);
        $key = $field['key'];
        if (($kind === 'birth' && ($key === 'brn' || $semantic === 'brn'))
            || ($kind === 'nid' && ($key === 'nid' || $semantic === 'nid'))
            || ($kind === 'passport' && ($key === 'passport' || $semantic === 'passport'))
            || (soko_is_mfs_info($name) && ($key === 'trx' || $semantic === 'trx'))) continue;
        $out[] = $field;
    }
    if (!$out && $kind !== '') $out = soko_match_preset($name);
    $required = [];
    if ($kind === 'birth') {
        foreach (['name'=>'নিজ নাম', 'father_name'=>'পিতার নাম', 'mother_name'=>'মাতার নাম', 'village'=>'গ্রাম / বাসা / রোড', 'thana'=>'থানা / উপজেলা', 'district'=>'জেলা', 'division'=>'বিভাগ'] as $key=>$title) {
            $required[] = soko_field($key, $title, 'text', 1, $title . ' লিখুন', 'fa-pen', '');
        }
    } elseif ($kind === 'nid') {
        $required[] = soko_field('birth_pdf', 'জন্ম নিবন্ধনের PDF', 'file', 1, 'জন্ম নিবন্ধনের মূল PDF আপলোড করুন', 'fa-file-pdf', '.pdf,application/pdf');
    } elseif ($kind === 'passport') {
        $required[] = soko_field('nid', 'NID নম্বর', 'text', 1, '১০ / ১৩ / ১৭ ডিজিটের NID নম্বর', 'fa-id-card', '');
    }
    foreach ($required as $base) {
        $found = false;
        foreach ($out as &$field) {
            if ($field['key'] !== $base['key'] && soko_field_semantic_key($field['title']) !== $base['key']) continue;
            $found = true;
            // Mandatory identity evidence cannot be bypassed by an old configuration.
            if ($base['key'] === 'birth_pdf') { $field['type'] = 'file'; $field['accept'] = '.pdf,application/pdf'; $field['required'] = 1; }
            if ($kind === 'passport' && $base['key'] === 'nid') $field['required'] = 1;
            break;
        }
        unset($field);
        if (!$found) $out[] = $base;
    }
    return $out;
}
function soko_admin_form_fields($post) {
    $fields = [];
    foreach ((array)($post['field_title'] ?? []) as $index => $title) {
        if (!is_string($title) || trim($title) === '') continue;
        $type = $post['field_type'][$index] ?? 'text';
        if (!in_array($type, ['text','tel','date','number','file','textarea'], true)) $type = 'text';
        $key = $post['field_key'][$index] ?? '';
        if (!is_string($key) || !preg_match('/^[a-z][a-z0-9_]*$/', $key)) $key = '';
        $fields[] = ['key'=>$key, 'title'=>trim($title), 'type'=>$type,
            'required'=>!empty($post['field_required'][$index]) ? 1 : 0,
            'placeholder'=>is_string($post['field_placeholder'][$index] ?? null) ? trim($post['field_placeholder'][$index]) : '',
            'accept'=>is_string($post['field_accept'][$index] ?? null) ? trim($post['field_accept'][$index]) : ''];
    }
    return $fields;
}
function soko_order_upload_error($file, $accept = '') {
    if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return 'ফাইল নির্বাচন করুন।';
    if (($file['error'] ?? -1) !== UPLOAD_ERR_OK) return 'ফাইল আপলোডে সমস্যা হয়েছে। আবার নির্বাচন করুন।';
    if (($file['size'] ?? 0) <= 0 || $file['size'] > 5 * 1024 * 1024) return 'ফাইলের সাইজ ১ বাইট থেকে ৫MB হতে হবে।';
    if (!is_string($file['name'] ?? null) || !is_string($file['tmp_name'] ?? null)) return 'সঠিক ফাইল নির্বাচন করুন।';
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp','pdf'];
    $accept = strtolower(trim($accept));
    if ($accept !== '') {
        $selected = [];
        foreach (explode(',', $accept) as $part) {
            $part = trim($part);
            if ($part === 'image/*') $selected = array_merge($selected, ['jpg','jpeg','png','webp']);
            elseif ($part === 'application/pdf') $selected[] = 'pdf';
            elseif ($part === 'image/jpeg') $selected = array_merge($selected, ['jpg','jpeg']);
            elseif ($part === 'image/png') $selected[] = 'png';
            elseif ($part === 'image/webp') $selected[] = 'webp';
            elseif (preg_match('/^\.(jpg|jpeg|png|webp|pdf)$/', $part, $match)) $selected[] = $match[1];
        }
        if ($selected) $allowed = array_intersect($allowed, $selected);
    }
    if (!in_array($ext, $allowed, true)) return 'সঠিক ফাইলের ধরন নির্বাচন করুন (' . implode(', ', $allowed) . ')।';
    if ($ext === 'pdf') {
        $tmp = $file['tmp_name'] ?? '';
        $head = is_file($tmp) ? file_get_contents($tmp, false, null, 0, 5) : false;
        if ($head !== '%PDF-') return 'সঠিক PDF দিন; ছবির নাম বদলে PDF দিলে গ্রহণ করা হবে না।';
    }
    return '';
}
function soko_validate_order_fields($fields, $post, $files) {
    $errors = []; $values = []; $old = [];
    foreach ($fields as $field) {
        $key = $field['key']; $label = $field['title'];
        if ($field['type'] === 'file') {
            $file = $files['file_' . $key] ?? null;
            if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                if (!empty($field['required'])) $errors[] = $label . ' আপলোড করা আবশ্যক।';
            } else {
                $error = soko_order_upload_error($file, $field['accept'] ?? '');
                if ($error !== '') $errors[] = $label . ': ' . $error;
            }
        } else {
            $value = $post['field_' . $key] ?? '';
            if (!is_string($value)) { $errors[] = $label . ' সঠিকভাবে লিখুন।'; $value = ''; }
            $old[$key] = $value = trim($value);
            if ($value === '' && !empty($field['required'])) $errors[] = $label . ' পূরণ করা আবশ্যক।';
            $values[$label] = $value;
        }
    }
    return ['errors'=>$errors, 'values'=>$values, 'old_values'=>$old];
}
