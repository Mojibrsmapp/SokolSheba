<?php
/**
 * Sokol Sheba — per service order page.
 *
 * One single template, but every service gets its own field set
 * (see includes/service_catalog.php). After a successful order the user
 * is sent to receipt.php, which is identical for every service.
 */
session_start();
include_once("includes/configuration.php");
include_once("includes/service_catalog.php");
include_once("includes/payment_flow.php");

$user = soko_current_user($conn);
if (!$user) {
    header("Location: index.php");
    exit;
}

$service_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$service_name_q = isset($_GET['service']) ? trim($_GET['service']) : '';
$service = soko_get_service($conn, $service_id, $service_name_q);

if (!$service) {
    header("Location: services.php");
    exit;
}

$fields = soko_resolve_fields($service);
$category = trim((string)$service['category']);
if ($category === '') {
    $category = 'সেবা';
}
$service_title = trim((string)$service['service_name']);
$service_desc = trim((string)$service['description']);
if ($service_desc === '') {
    $service_desc = 'দ্রুত ও নির্ভরযোগ্য সেবা পেতে নিচের তথ্যগুলো সঠিকভাবে পূরণ করুন।';
}
$price = (float)$service['price'];
$service_charge = 0.0;
$total_payable = $price + $service_charge;
$is_active = (strtolower(trim((string)$service['status'])) === 'active');
$wallet_balance = (float)$user['balance'];
$channels = soko_payment_channels($conn);
$service_icon = soko_service_icon($service_title, $category);
$default_method = ($total_payable > 0 && $wallet_balance >= $total_payable) ? 'wallet' : 'mobile';

$errors = array();
$old_values = array();

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {

    $payment_method = isset($_POST['payment_method']) ? strtolower(trim($_POST['payment_method'])) : 'mobile';
    if ($payment_method !== 'wallet') {
        $payment_method = 'mobile';
    }

    if (!$is_active) {
        $errors[] = 'এই সার্ভিসটি বর্তমানে সাময়িকভাবে বন্ধ আছে। পরে আবার চেষ্টা করুন।';
    }

    $submitted = array();
    $uploaded_files = array();

    foreach ($fields as $field) {
        $key = $field['key'];
        $type = $field['type'];
        $label = $field['title'];
        $is_required = false; // all fields are optional by design

        if ($type === 'file') {
            $file_key = 'file_' . $key;
            $has_file = isset($_FILES[$file_key]) && isset($_FILES[$file_key]['error']) && $_FILES[$file_key]['error'] !== UPLOAD_ERR_NO_FILE;

            if (!$has_file) {
                if ($is_required) {
                    $errors[] = $label . ' আপলোড করা আবশ্যক।';
                }
                continue;
            }

            $upload = soko_handle_upload($_FILES[$file_key], (int)$user['id'], $key);
            if ($upload['error'] !== '') {
                $errors[] = $label . ': ' . $upload['error'];
                continue;
            }
            $uploaded_files[] = $upload['path'];
            $submitted[$label] = basename($upload['path']);
            continue;
        }

        if ($type === 'textarea') {
            $value = isset($_POST['field_' . $key]) ? trim($_POST['field_' . $key]) : '';
        } elseif ($type === 'date') {
            $value = isset($_POST['field_' . $key]) ? trim($_POST['field_' . $key]) : '';
        } else {
            $value = isset($_POST['field_' . $key]) ? trim($_POST['field_' . $key]) : '';
        }

        $old_values[$key] = $value;

        if ($value === '') {
            if ($is_required) {
                $errors[] = $label . ' পূরণ করা আবশ্যক।';
            }
            $submitted[$label] = '';
            continue;
        }

        $submitted[$label] = $value;
    }

    if ($payment_method === 'wallet' && $wallet_balance < $total_payable) {
        $errors[] = 'আপনার ওয়ালেট ব্যালেন্স যথেষ্ট নয়। রিচার্জ করুন অথবা মোবাইল ব্যাংকিং নির্বাচন করুন।';
    }

    if (count($errors) === 0) {
        $input_text = soko_input_to_text($submitted) . ' | __payment__: ' . $payment_method;

        $conn->begin_transaction();
        try {
            $order_id = 0;

            if ($payment_method === 'wallet') {
                $update = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ? AND balance >= ?");
                $update->bind_param("did", $total_payable, $user['id'], $total_payable);
                $update->execute();
                if ($update->affected_rows < 1) {
                    $update->close();
                    throw new Exception('insufficient_balance');
                }
                $update->close();
            }

            $insert = $conn->prepare("INSERT INTO orders (user_id, order_type, input_data, price, status) VALUES (?, ?, ?, ?, 'pending')");
            $insert->bind_param("issd", $user['id'], $service_title, $input_text, $total_payable);
            $insert->execute();
            $order_id = $conn->insert_id;
            $insert->close();

            if ($payment_method === 'wallet') {
                $balance_after = $wallet_balance - $total_payable;
                /* ব্যালেন্স হিস্ট্রি — optional। পুরনো ডাটাবেজে টেবিল/কলাম না থাকলেও
                   অর্ডার যেন কখনো ভেঙে না পড়ে, তাই আলাদা করে ট্রাই করা হচ্ছে। */
                try {
                    $history = $conn->prepare("INSERT INTO history_work (email, order_type, price, current_balance, balance_after_cut, about_order, order_time) VALUES (?, ?, ?, ?, ?, 'place_order', NOW())");
                    if ($history) {
                        $history->bind_param("ssddd", $user['email'], $service_title, $total_payable, $wallet_balance, $balance_after);
                        $history->execute();
                        $history->close();
                    }
                } catch (Throwable $history_error) {
                    try {
                        $history = $conn->prepare("INSERT INTO history_work (email, order_type, price, current_balance, balance_after_cut, order_time) VALUES (?, ?, ?, ?, ?, NOW())");
                        if ($history) {
                            $history->bind_param("ssddd", $user['email'], $service_title, $total_payable, $wallet_balance, $balance_after);
                            $history->execute();
                            $history->close();
                        }
                    } catch (Throwable $history_error2) {
                        /* হিস্ট্রি লেখা না গেলেও অর্ডার কনফার্ম থাকবে */
                    }
                }
            }

            $conn->commit();

            if ($payment_method === 'wallet') {
                header("Location: receipt.php?order=" . $order_id . "&new=1");
            } else {
                header("Location: payment_gateway.php?order=" . $order_id);
            }
            exit;

        } catch (Throwable $ex) {
            $conn->rollback();
            if ($ex->getMessage() === 'insufficient_balance') {
                $errors[] = 'আপনার ওয়ালেট ব্যালেন্স যথেষ্ট নয়। রিচার্জ করুন অথবা মোবাইল ব্যাংকিং নির্বাচন করুন।';
            } else {
                $errors[] = 'অর্ডার সম্পন্ন করা যায়নি। একটু পরে আবার চেষ্টা করুন।';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title><?php echo soko_h($service_title); ?> — অর্ডার | <?php echo soko_e(soko_brand('site_name')); ?></title>
<link rel="shortcut icon" href="<?php echo soko_e(soko_brand('site_favicon')); ?>">
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
  --radius:16px;
}
*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
body{
  font-family:'Hind Siliguri',system-ui,-apple-system,'Segoe UI',sans-serif;
  background:var(--page);color:var(--ink);font-size:15px;line-height:1.5;
  padding-bottom:70px;
}
a{text-decoration:none;color:inherit}
button{font-family:inherit}

/* ---------------- top bar ---------------- */
.topbar{background:#fff;border-bottom:1px solid var(--line);position:sticky;top:0;z-index:60}
.topbar-inner{max-width:1080px;margin:0 auto;display:flex;align-items:center;gap:14px;padding:10px 16px}
.brand{display:flex;align-items:center;gap:10px;flex:1;min-width:0}
.brand-logo{width:48px;height:48px;border-radius:50%;overflow:hidden;flex:0 0 48px;border:2px solid #0B7A3B;background:#fff;display:flex;align-items:center;justify-content:center}
.brand-logo img{width:100%;height:100%;object-fit:cover}
.brand-name{font-size:23px;font-weight:700;color:var(--green);line-height:1.1;display:block;letter-spacing:-.3px}
.brand-name em{color:var(--red);font-style:normal}
.brand-tag{display:block;font-size:12.5px;color:var(--muted);font-weight:500;margin-top:1px}
.top-actions{display:flex;align-items:center;gap:8px}
.wallet-pill{display:flex;align-items:center;gap:8px;border:1.5px solid var(--green);border-radius:12px;padding:6px 12px 6px 6px;background:#fff}
.wallet-ic{width:30px;height:30px;border-radius:8px;background:var(--green);color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px}
.wallet-txt{display:flex;flex-direction:column;line-height:1.15}
.wallet-txt small{font-size:10.5px;color:var(--muted);font-weight:600}
.wallet-txt b{font-size:14px;color:var(--ink);font-weight:700}
.icon-btn{width:42px;height:42px;border-radius:12px;border:1.5px solid var(--line);background:#fff;color:var(--ink);display:flex;align-items:center;justify-content:center;font-size:16px;position:relative;cursor:pointer}
.icon-btn .dot{position:absolute;top:8px;right:9px;width:8px;height:8px;border-radius:50%;background:var(--red);border:2px solid #fff}

/* drawer */
.drawer{position:fixed;inset:0;z-index:120;display:none}
.drawer.is-open{display:block}
.drawer-veil{position:absolute;inset:0;background:rgba(9,32,20,.42)}
.drawer-panel{position:absolute;top:0;right:0;width:min(300px,86%);height:100%;background:#fff;padding:18px;overflow:auto;box-shadow:-8px 0 30px rgba(0,0,0,.12)}
.drawer-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.drawer-head h3{font-size:16px;font-weight:700}
.drawer-panel a{display:flex;align-items:center;gap:10px;padding:11px 12px;border-radius:10px;font-weight:600;color:var(--ink);font-size:14px}
.drawer-panel a:hover{background:var(--green-soft)}
.drawer-panel a i{width:18px;color:var(--green)}

/* ---------------- breadcrumb ---------------- */
.crumb{background:var(--green);color:#fff}
.crumb-inner{max-width:1080px;margin:0 auto;display:flex;align-items:center;gap:10px;padding:12px 16px;font-size:17px;font-weight:700}
.crumb-inner .back{color:#fff;font-size:18px;margin-right:4px}
.crumb-inner .sep{opacity:.7;font-size:13px}
.crumb-inner .now{color:#fff}

/* ---------------- shell ---------------- */
.shell{max-width:1080px;margin:0 auto;padding:16px}

/* ---------------- hero ---------------- */
.hero{background:#fff;border:1px solid var(--green-line);border-radius:var(--radius);padding:14px;display:flex;gap:18px;align-items:center;position:relative;box-shadow:0 4px 16px rgba(11,122,59,.05)}
.hero-media{flex:0 0 250px}
.nid-mock{width:250px;height:158px;border-radius:12px;background:linear-gradient(135deg,#0E8C4B 0%,#0B7A3B 45%,#0A6A34 100%);position:relative;overflow:hidden;box-shadow:0 8px 20px rgba(11,122,59,.28);padding:10px 11px;color:#fff}
.nid-mock:before{content:"";position:absolute;inset:0;background:radial-gradient(circle at 82% 12%,rgba(255,255,255,.22),transparent 55%)}
.nid-mock .gov{display:flex;align-items:center;gap:7px;position:relative;z-index:1}
.nid-mock .gov img{width:24px;height:24px;border-radius:50%;background:#fff}
.nid-mock .gov b{font-size:9px;font-weight:700;line-height:1.25;display:block}
.nid-mock .gov small{font-size:6.4px;opacity:.85;display:block;letter-spacing:.2px}
.nid-mock .row{display:flex;gap:9px;margin-top:9px;position:relative;z-index:1}
.nid-mock .photo{width:52px;height:62px;border-radius:6px;background:rgba(0,0,0,.34);border:1px solid rgba(255,255,255,.45);display:flex;align-items:center;justify-content:center;font-size:20px;color:rgba(255,255,255,.75)}
.nid-mock .lines{flex:1;display:flex;flex-direction:column;gap:5px;justify-content:center}
.nid-mock .lines span{display:block;height:5px;border-radius:3px;background:rgba(255,255,255,.55)}
.nid-mock .lines span:nth-child(1){width:78%}
.nid-mock .lines span:nth-child(2){width:56%}
.nid-mock .lines span:nth-child(3){width:66%}
.nid-mock .lines span:nth-child(4){width:44%}
.nid-mock .chip{position:absolute;right:12px;bottom:40px;width:26px;height:20px;border-radius:4px;background:linear-gradient(135deg,#F7D774,#C9A227);z-index:1}
.nid-mock .barcode{position:absolute;left:11px;right:11px;bottom:8px;height:12px;border-radius:2px;background:repeating-linear-gradient(90deg,#fff 0 2px,transparent 2px 4px,#fff 4px 5px,transparent 5px 8px);opacity:.75;z-index:1}

.hero-body{flex:1;min-width:0}
.hero-title-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.hero-title-row h1{font-size:28px;font-weight:700;letter-spacing:-.4px}
.badge-cat{background:var(--green);color:#fff;font-size:12px;font-weight:600;padding:4px 12px;border-radius:20px}
.hero-desc{color:var(--muted);font-size:14.5px;margin-top:6px;max-width:52ch}
.hero-feats{display:flex;align-items:center;gap:14px;margin-top:14px;flex-wrap:wrap}
.feat{display:flex;align-items:center;gap:8px;font-size:13.5px;font-weight:600;color:var(--ink);line-height:1.25}
.feat i{width:30px;height:30px;border-radius:50%;background:var(--green);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12.5px;flex:0 0 30px}
.feat-sep{width:1px;height:30px;background:var(--line)}
.hero-price{display:flex;flex-direction:column;align-items:flex-end;gap:8px;align-self:flex-start}
.price-tag{background:var(--green);color:#fff;font-size:22px;font-weight:700;padding:6px 16px;border-radius:12px;min-width:104px;text-align:center}
.price-note{background:var(--green-soft);color:var(--green-dark);font-size:12px;font-weight:600;padding:4px 12px;border-radius:20px}

/* ---------------- stepper ---------------- */
.steps{background:#fff;border:1px solid var(--green-line);border-radius:var(--radius);margin-top:14px;padding:14px 10px;display:flex;justify-content:space-between;position:relative}
.steps:before{content:"";position:absolute;left:11%;right:11%;top:30px;height:2px;background:#DCE7E0}
.step{position:relative;z-index:1;flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;text-align:center}
.step .dot{width:34px;height:34px;border-radius:50%;background:#C9D6CD;color:#fff;font-weight:700;font-size:14px;display:flex;align-items:center;justify-content:center}
.step .lbl{font-size:13px;font-weight:600;color:var(--muted)}
.step.is-active .dot{background:var(--green)}
.step.is-active .lbl{color:var(--ink);font-weight:700}

/* ---------------- layout ---------------- */
.grid{display:grid;grid-template-columns:1.12fr .88fr;gap:14px;margin-top:14px;align-items:start}
.card{background:#fff;border:1px solid var(--green-line);border-radius:var(--radius);box-shadow:0 4px 16px rgba(11,122,59,.045)}
.card-head{display:flex;align-items:center;gap:9px;padding:13px 16px;border-bottom:1px solid var(--line)}
.card-head .ch-ic{width:28px;height:28px;border-radius:50%;background:var(--green-soft);color:var(--green);display:flex;align-items:center;justify-content:center;font-size:12.5px}
.card-head h2{font-size:16.5px;font-weight:700}
.card-head .req-note{margin-left:auto;color:var(--red);font-size:12px;font-weight:600}
.card-body{padding:14px 16px}

.field{margin-bottom:13px}
.field label.flabel{display:block;font-size:13.5px;font-weight:600;margin-bottom:5px;color:var(--ink)}
.field label.flabel .star{color:var(--red)}
.inp{position:relative}
.inp > i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#8AA396;font-size:13.5px}
.inp input,.inp select{
  width:100%;height:44px;border:1px solid var(--line);border-radius:10px;background:#fff;
  padding:0 12px 0 40px;font-size:14px;color:var(--ink);font-family:inherit;outline:none;
}
.inp input::placeholder,.textarea::placeholder{color:#9DB0A4}
.inp input:focus,.textarea:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(11,122,59,.09)}
.textarea{width:100%;min-height:92px;border:1px solid var(--line);border-radius:10px;padding:11px 12px;font-size:14px;font-family:inherit;color:var(--ink);outline:none;resize:vertical}
.upload{display:block;border:1.6px dashed #C6DFCE;border-radius:12px;background:#FBFEFC;padding:16px 12px;text-align:center;cursor:pointer;transition:.16s}
.upload:hover{border-color:var(--green);background:#F4FBF6}
.upload i.up-ic{font-size:24px;color:#7EA890;display:block;margin-bottom:5px}
.upload b{display:block;font-size:14px;font-weight:600;color:var(--ink)}
.upload small{display:block;font-size:12px;color:var(--muted);margin-top:2px}
.upload input[type=file]{display:none}
.upload .picked{display:none;margin-top:7px;font-size:12px;font-weight:600;color:var(--green-dark);background:var(--green-soft);border-radius:8px;padding:5px 9px;word-break:break-all}
.upload.has-file .picked{display:block}
.tip{margin-top:4px;background:#F2F8F4;border:1px solid #E4F0E7;border-radius:12px;padding:12px 13px;display:flex;gap:10px}
.tip i{color:#E0A81B;font-size:15px;margin-top:2px}
.tip b{display:block;font-size:13.5px;margin-bottom:4px}
.tip ul{margin:0;padding-left:16px}
.tip li{font-size:12.8px;color:var(--muted);margin-bottom:3px}

/* summary */
.srv-line{display:flex;align-items:center;gap:10px;padding:11px 12px;border:1px solid var(--line);border-radius:12px;margin-bottom:12px}
.srv-line .sl-ic{width:32px;height:32px;border-radius:9px;background:var(--green-soft);color:var(--green);display:flex;align-items:center;justify-content:center;font-size:14px}
.srv-line b{font-size:14.5px;font-weight:700}
.sum-row{display:flex;align-items:center;justify-content:space-between;font-size:14px;padding:7px 2px;color:var(--muted)}
.sum-row span:last-child{font-weight:600;color:var(--ink)}
.sum-total{display:flex;align-items:center;justify-content:space-between;background:var(--green-soft);border-radius:10px;padding:10px 12px;margin-top:6px;font-weight:700;font-size:15px;color:var(--green-dark)}
.sum-total b{font-size:18px}

.pay-title{display:flex;align-items:center;gap:8px;font-size:15px;font-weight:700;margin:16px 0 9px}
.pay-title i{color:var(--green);font-size:13px}
.pay-opt{display:flex;align-items:center;gap:10px;border:1px solid var(--line);border-radius:12px;padding:11px 12px;margin-bottom:9px;cursor:pointer;transition:.16s;background:#fff}
.pay-opt:hover{border-color:var(--green-line)}
.pay-opt input{appearance:none;width:18px;height:18px;border-radius:50%;border:1.8px solid #C3D5C9;margin:0;flex:0 0 18px;cursor:pointer;position:relative}
.pay-opt input:checked{border-color:var(--green);background:var(--green);box-shadow:inset 0 0 0 3px #fff}
.pay-opt.selected{border-color:var(--green);background:#F7FCF8}
.pay-opt .po-main{flex:1;min-width:0}
.pay-opt .po-main b{display:block;font-size:14px;font-weight:600}
.pay-opt .po-main small{display:block;font-size:11.8px;color:var(--muted)}
.pay-opt .po-ic{color:#7E9587;font-size:15px}
.logos{display:flex;align-items:center;gap:9px;margin-top:6px;flex-wrap:wrap}
.logo-chip{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:700;padding:2px 6px;border-radius:6px;background:#F6F9F7}
.logo-chip img{height:15px;width:auto;max-width:52px;object-fit:contain;display:block}
.logo-chip.bk{color:#E2136E}
.logo-chip.ng{color:#F58220}
.logo-chip.rk{color:#8C3494}
.secure{display:flex;gap:9px;background:var(--green-soft);border:1px solid #D9EDDF;border-radius:10px;padding:9px 11px;margin:12px 0}
.secure i{color:var(--green);font-size:14px;margin-top:2px}
.secure b{display:block;font-size:12.5px;color:var(--green-dark)}
.secure small{font-size:11.5px;color:var(--muted)}
.btn-order{width:100%;height:50px;border:0;border-radius:12px;background:var(--green);color:#fff;font-size:16px;font-weight:700;display:flex;align-items:center;justify-content:center;gap:9px;cursor:pointer;transition:.18s;box-shadow:0 6px 16px rgba(11,122,59,.24)}
.btn-order:hover{background:var(--green-dark)}
.btn-order:disabled{opacity:.7;cursor:not-allowed}

.alert{border-radius:12px;padding:11px 13px;font-size:13.5px;font-weight:600;margin-bottom:12px;display:flex;gap:9px;align-items:flex-start}
.alert.bad{background:#FEF1F1;border:1px solid #F7D2D2;color:#A81616}
.alert.off{background:#FFF8E7;border:1px solid #F5E3AE;color:#8A5A00}

/* bottom nav */
.bottom-nav{position:fixed;left:0;right:0;bottom:0;background:#fff;border-top:1px solid var(--line);display:flex;justify-content:space-around;padding:6px 4px 8px;z-index:70}
.bottom-nav a{display:flex;flex-direction:column;align-items:center;gap:3px;font-size:11px;font-weight:600;color:#7A8F82;flex:1}
.bottom-nav a i{font-size:17px}
.bottom-nav a.is-active{color:var(--green)}
.bottom-nav a.is-active i{transform:translateY(-1px)}

@media (max-width:900px){
  .grid{grid-template-columns:1fr}
  .hero{flex-wrap:wrap;gap:12px}
  .hero-media{flex:1 1 100%;display:flex;justify-content:center}
  .nid-mock{width:100%;max-width:320px;height:auto;aspect-ratio:250/158}
  .hero-price{position:static;align-self:stretch;flex-direction:row;align-items:center;gap:8px}
  .hero-price .price-tag{min-width:80px}
  .hero-body{width:100%}
  .card-head{flex-wrap:wrap}
  .hero-title-row h1{font-size:23px;overflow-wrap:anywhere;line-height:1.45}
  .hero-desc{padding-right:0}
  .steps:before{top:26px;left:12%;right:12%}
  .step .dot{width:30px;height:30px;font-size:13px}
  .step .lbl{font-size:11.5px}
  .brand-name{font-size:20px}
  .topbar-inner{padding:8px 12px;gap:8px}
  .wallet-txt small{display:none}
  .wallet-pill{padding:5px 10px 5px 5px;gap:6px}
  .icon-btn{width:38px;height:38px;border-radius:10px;font-size:14px}
  .brand-tag{font-size:11px}
  .shell{padding:12px}
  .crumb-inner{font-size:15px;padding:10px 12px}
}
@media (min-width:901px){
  .bottom-nav{display:none}
  body{padding-bottom:24px}
}
.hero--birth .hero-media{display:none}
@media print{
  .topbar,.crumb,.bottom-nav,.btn-order,.drawer,.hero-price{display:none!important}
  body{background:#fff;padding:0}
}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/site_header.php'; ?>


<div class="crumb">
  <div class="crumb-inner">
    <a href="services.php" class="back"><i class="fa-solid fa-arrow-left"></i></a>
    <span><?php echo soko_h($category); ?></span>
    <i class="fa-solid fa-chevron-right sep"></i>
    <span class="now"><?php echo soko_h($service_title); ?></span>
  </div>
</div>

<main class="shell">

  <?php if (count($errors) > 0): ?>
    <div class="alert bad">
      <i class="fa-solid fa-circle-exclamation" style="margin-top:2px"></i>
      <div>
        <?php foreach ($errors as $error): ?>
          <div><?php echo soko_h($error); ?></div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if (!$is_active): ?>
    <div class="alert off">
      <i class="fa-solid fa-circle-pause" style="margin-top:2px"></i>
      <div>এই সার্ভিসটি এই মুহূর্তে বন্ধ আছে। অর্ডার নিতে সমস্যা হলে সাপোর্টে যোগাযোগ করুন।</div>
    </div>
  <?php endif; ?>

  <section class="hero<?php echo soko_is_new_birth_application($service_title) ? ' hero--birth' : ''; ?>">
    <div class="hero-media">
      <div class="nid-mock">
        <div class="gov">
          <img src="assets/birth-card-files/bd_logo.png" alt="">
          <span>
            <b><?php echo soko_is_new_birth_application($service_title) ? 'জন্ম নিবন্ধন আবেদন' : 'সেবার আবেদন'; ?></b>
            <small>Government of the People's Republic of Bangladesh</small>
          </span>
        </div>
        <div class="row">
          <div class="photo"><i class="fa-solid fa-user"></i></div>
          <div class="lines"><span></span><span></span><span></span><span></span></div>
        </div>
        <div class="chip"></div>
        <div class="barcode"></div>
      </div>
    </div>

    <div class="hero-body">
      <div class="hero-title-row">
        <h1><?php echo soko_h($service_title); ?></h1>
        <span class="badge-cat"><?php echo soko_h($category); ?></span>
      </div>
      <p class="hero-desc"><?php echo soko_h($service_desc); ?></p>
      <div class="hero-feats">
        <div class="feat"><i class="fa-solid fa-shield-halved"></i><span>নির্ভরযোগ্য<br>সেবা</span></div>
        <div class="feat-sep"></div>
        <div class="feat"><i class="fa-solid fa-bolt"></i><span>দ্রুত<br>প্রসেস</span></div>
        <div class="feat-sep"></div>
        <div class="feat"><i class="fa-solid fa-headset"></i><span>২৪/৭<br>সাপোর্ট</span></div>
      </div>
    </div>

    <div class="hero-price">
      <span class="price-tag"><?php echo soko_money($price); ?></span>
      <span class="price-note">বেসিক চার্জ</span>
    </div>
  </section>

  <ol class="steps">
    <li class="step is-active"><span class="dot">১</span><span class="lbl">তথ্য প্রদান</span></li>
    <li class="step"><span class="dot">২</span><span class="lbl">পেমেন্ট</span></li>
    <li class="step"><span class="dot">৩</span><span class="lbl">অর্ডার কনফার্ম</span></li>
    <li class="step"><span class="dot">৪</span><span class="lbl">অবস্থা দেখুন</span></li>
  </ol>

  <?php if (soko_is_new_birth_application($service_title)): ?>
  <div class="ss-order-result" role="note">
    <strong>নতুন আবেদনে আগে থেকে জন্ম নিবন্ধন নম্বর লাগবে না</strong>
    <p>আবেদন সম্পন্ন করে নিচের অর্ডার হিস্টোরিতে অবস্থা দেখুন। নম্বর ইস্যু হলে অ্যাডমিনের দেওয়া জন্ম নিবন্ধন নম্বর/সনদ রেজাল্টে পাবেন। অর্ডার নম্বর জন্ম নিবন্ধন নম্বর নয়।</p>
    <a class="ss-history-link" href="#service-history">হিস্টোরি ও রেজাল্ট দেখুন ↓</a>
  </div>
  <?php endif; ?>
  <form method="post" action="service_order.php?id=<?php echo (int)$service['id']; ?>" enctype="multipart/form-data" id="orderForm" novalidate>
    <input type="hidden" name="place_order" value="1">
    <div class="grid">

      <!-- left: information -->
      <section class="card">
        <div class="card-head">
          <span class="ch-ic"><i class="fa-solid fa-user"></i></span>
          <h2>ব্যক্তিগত তথ্য</h2>
          <span class="req-note" style="color:#0B7A3B;background:#EAF6EE;border-radius:99px;padding:3px 9px;font-weight:600">সব ঘর ঐচ্ছিক</span>
        </div>
        <div class="card-body">
          <?php foreach ($fields as $field): ?>
            <?php
              $key = $field['key'];
              $type = $field['type'];
              $input_id = 'f_' . $key;
              $current = isset($old_values[$key]) ? $old_values[$key] : '';
              $star = ''; // সব ফিল্ড ঐচ্ছিক
            ?>

            <?php if ($type === 'file'): ?>
              <div class="field">
                <label class="flabel" for="<?php echo soko_h($input_id); ?>"><?php echo soko_h($field['title']); ?><?php echo $star; ?></label>
                <label class="upload" for="<?php echo soko_h($input_id); ?>">
                  <i class="fa-solid fa-cloud-arrow-up up-ic"></i>
                  <b>ছবি আপলোড করুন</b>
                  <small>(JPG, PNG অথবা PDF — সর্বোচ্চ ৫MB)</small>
                  <span class="picked">কোনো ফাইল নির্বাচন করা হয়নি</span>
                  <input type="file" id="<?php echo soko_h($input_id); ?>" name="file_<?php echo soko_h($key); ?>" accept="<?php echo soko_h($field['accept']); ?>" data-pick="1">
                </label>
              </div>

            <?php elseif ($type === 'textarea'): ?>
              <div class="field">
                <label class="flabel" for="<?php echo soko_h($input_id); ?>"><?php echo soko_h($field['title']); ?><?php echo $star; ?></label>
                <textarea class="textarea" id="<?php echo soko_h($input_id); ?>" name="field_<?php echo soko_h($key); ?>" placeholder="<?php echo soko_h($field['placeholder']); ?>" ><?php echo soko_h($current); ?></textarea>
              </div>

            <?php else: ?>
              <div class="field">
                <label class="flabel" for="<?php echo soko_h($input_id); ?>"><?php echo soko_h($field['title']); ?><?php echo $star; ?></label>
                <div class="inp">
                  <i class="fa-solid <?php echo soko_h($field['icon']); ?>"></i>
                  <input
                    type="<?php echo $type === 'tel' ? 'tel' : ($type === 'date' ? 'date' : 'text'); ?>"
                    id="<?php echo soko_h($input_id); ?>"
                    name="field_<?php echo soko_h($key); ?>"
                    value="<?php echo soko_h($current); ?>"
                    placeholder="<?php echo soko_h($field['placeholder']); ?>"
                    <?php echo $type === 'tel' ? 'inputmode="numeric" maxlength="14"' : ''; ?>
                    >
                </div>
              </div>
            <?php endif; ?>

          <?php endforeach; ?>

          <div class="tip">
            <i class="fa-regular fa-lightbulb"></i>
            <div>
              <b>কিছু গুরুত্বপূর্ণ তথ্য</b>
              <ul>
                <li><?php echo soko_h($service_title); ?> পেতে সঠিক ও পূর্ণ তথ্য দিতে হবে।</li>
                <li>ছবি পরিষ্কার এবং সব তথ্য দৃশ্যমান থাকতে হবে।</li>
                <li>অর্ডার করার পর ১–২ ঘণ্টার মধ্যে প্রসেস শুরু হবে।</li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      <!-- right: summary + payment -->
      <aside class="card">
        <div class="card-head">
          <span class="ch-ic"><i class="fa-solid fa-file-invoice"></i></span>
          <h2>অর্ডার সারাংশ</h2>
        </div>
        <div class="card-body">
          <div class="srv-line">
            <span class="sl-ic"><i class="fa-solid <?php echo soko_h($service_icon); ?>"></i></span>
            <b><?php echo soko_h($service_title); ?></b>
          </div>

          <div class="sum-row"><span>সেবা মূল্য</span><span><?php echo soko_money($price); ?></span></div>
          <div class="sum-row"><span>সার্ভিস চার্জ</span><span><?php echo soko_money($service_charge); ?></span></div>
          <div class="sum-total"><span>মোট পরিশোধ</span><b><?php echo soko_money($total_payable); ?></b></div>

          <div class="pay-title"><i class="fa-solid fa-money-check-dollar"></i> পেমেন্ট পদ্ধতি</div>

          <label class="pay-opt" data-pay="mobile">
            <input type="radio" name="payment_method" value="mobile" <?php echo $default_method === 'mobile' ? 'checked' : ''; ?>>
            <span class="po-main">
              <b>মোবাইল ব্যাংকিং</b>
              <span class="logos">
                <?php foreach (array('bkash', 'nagad', 'rocket') as $chip_key): ?>
                  <?php $chip_logo = function_exists('soko_pay_method_logo') ? soko_pay_method_logo($chip_key) : ''; ?>
                  <?php if ($chip_logo !== ''): ?>
                    <span class="logo-chip <?php echo $chip_key === 'bkash' ? 'bk' : ($chip_key === 'nagad' ? 'ng' : 'rk'); ?>">
                      <img src="<?php echo soko_h($chip_logo); ?>" alt="<?php echo soko_h($chip_key); ?>">
                    </span>
                  <?php endif; ?>
                <?php endforeach; ?>
              </span>
            </span>
            <i class="fa-solid fa-mobile-screen-button po-ic"></i>
          </label>

          <label class="pay-opt" data-pay="wallet">
            <input type="radio" name="payment_method" value="wallet" <?php echo $default_method === 'wallet' ? 'checked' : ''; ?>>
            <span class="po-main">
              <b>ওয়ালেট ব্যালেন্স</b>
              <small>আপনার ব্যালেন্স: <?php echo soko_money($wallet_balance, 2); ?></small>
            </span>
            <i class="fa-solid fa-wallet po-ic"></i>
          </label>

          <div class="secure">
            <i class="fa-solid fa-lock"></i>
            <div>
              <b>নিরাপদ ও সুরক্ষিত লেনদেন</b>
              <small>আপনার তথ্য ১০০% নিরাপদ থাকে, তৃতীয় পক্ষের সাথে শেয়ার হয় না।</small>
            </div>
          </div>

          <button type="submit" class="btn-order" id="orderBtn">
            <i class="fa-solid fa-cart-shopping"></i> অর্ডার করুন <i class="fa-solid fa-arrow-right" style="font-size:13px"></i>
          </button>
        </div>
      </aside>

    </div>
  </form>
</main>


<script>
(function () {
  var drawer = document.getElementById('drawer');
  var menuBtn = document.getElementById('menuBtn');
  if (menuBtn && drawer) {
    menuBtn.addEventListener('click', function () { drawer.classList.add('is-open'); });
    Array.prototype.forEach.call(drawer.querySelectorAll('[data-close]'), function (el) {
      el.addEventListener('click', function () { drawer.classList.remove('is-open'); });
    });
  }

  Array.prototype.forEach.call(document.querySelectorAll('input[type=file][data-pick]'), function (input) {
    input.addEventListener('change', function () {
      var wrap = input.closest('.upload');
      var badge = wrap.querySelector('.picked');
      if (input.files && input.files.length > 0) {
        var f = input.files[0];
        var kb = Math.round(f.size / 1024);
        badge.textContent = f.name + ' (' + kb + ' KB)';
        wrap.classList.add('has-file');
      } else {
        badge.textContent = 'কোনো ফাইল নির্বাচন করা হয়নি';
        wrap.classList.remove('has-file');
      }
    });
  });

  Array.prototype.forEach.call(document.querySelectorAll('.pay-opt'), function (opt) {
    var radio = opt.querySelector('input[type=radio]');
    function sync() {
      Array.prototype.forEach.call(document.querySelectorAll('.pay-opt'), function (item) {
        var r = item.querySelector('input[type=radio]');
        if (r.checked) { item.classList.add('selected'); } else { item.classList.remove('selected'); }
      });
    }
    radio.addEventListener('change', sync);
    sync();
  });

  var form = document.getElementById('orderForm');
  if (form) {
    form.addEventListener('submit', function () {
      var btn = document.getElementById('orderBtn');
      if (btn) {
        setTimeout(function () {
          btn.disabled = true;
          btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> প্রসেস হচ্ছে...';
        }, 0);
      }
    });
  }
})();
</script>
<?php include __DIR__ . '/includes/site_footer.php'; ?>
</body>
</html>
