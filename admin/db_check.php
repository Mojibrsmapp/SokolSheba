<?php
/**
 * Sokol Sheba — ডাটাবেস চেক / অটো-আপগ্রেড
 *
 * নতুন সার্ভিস-অর্ডার ও ম্যানুয়াল পেমেন্ট (সেন্ড মানি + TrxID) ফ্লো চালাতে
 * ডাটাবেজে কী আছে / কী নেই তা দেখায়; ?fix=1 দিলে যা নেই তা নিজেই তৈরি করে দেয়
 * (নিরাপদ — বারবার চালানো যায়)।
 *
 *   admin/db_check.php        → শুধু রিপোর্ট
 *   admin/db_check.php?fix=1  → যা নেই, তৈরি করে দেবে
 */
include_once("header.php");

if (!isset($conn) || !$conn) {
    exit('Database connection নেই।');
}

$fix = isset($_GET['fix']) && $_GET['fix'] === '1';
$db_name = '';
$r = @$conn->query("SELECT DATABASE() AS db");
if ($r && ($row = $r->fetch_assoc())) {
    $db_name = (string)$row['db'];
}

function soko_db_table_exists($conn, $table)
{
    $t = $conn->real_escape_string($table);
    $res = @$conn->query("SHOW TABLES LIKE '" . $t . "'");
    return ($res && $res->num_rows > 0);
}

function soko_db_column_exists($conn, $db, $table, $column)
{
    $db = $conn->real_escape_string($db);
    $t = $conn->real_escape_string($table);
    $c = $conn->real_escape_string($column);
    $res = @$conn->query("SELECT COUNT(*) AS c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . $db . "' AND TABLE_NAME = '" . $t . "' AND COLUMN_NAME = '" . $c . "'");
    if ($res && ($row = $res->fetch_assoc())) {
        return ((int)$row['c'] > 0);
    }
    return false;
}

$required_tables = array(
    'users' => 'একাউন্ট ও ওয়ালেট ব্যালেন্স',
    'custom_services' => 'সার্ভিস তালিকা (প্রতি সার্ভিসের ফিল্ড এখানেই)',
    'orders' => 'অর্ডার রেকর্ড',
    'site_settings' => 'সাইট সেটিংস (বিকাশ/নগদ নম্বর ইত্যাদি)',
    'payment_settings' => 'ম্যানুয়াল পেমেন্ট নম্বর (ঐচ্ছিক)',
    'history_work' => 'ব্যালেন্স হিস্ট্রি (ঐচ্ছিক)',
    'order_payments' => 'ম্যানুয়াল পেমেন্টের TrxID রেকর্ড',
);

$required_columns = array(
    'orders' => array(
        'user_id' => "int(11) NOT NULL DEFAULT 0",
        'order_type' => "varchar(191) NOT NULL DEFAULT ''",
        'input_data' => "text DEFAULT NULL",
        'price' => "decimal(10,2) NOT NULL DEFAULT 0.00",
        'status' => "varchar(30) NOT NULL DEFAULT 'pending'",
        'result_text' => "text DEFAULT NULL",
        'result_file' => "varchar(255) DEFAULT NULL",
        'created_at' => "timestamp NOT NULL DEFAULT current_timestamp()",
    ),
    'custom_services' => array(
        'service_name' => "varchar(191) NOT NULL DEFAULT ''",
        'category' => "varchar(120) NOT NULL DEFAULT ''",
        'price' => "decimal(10,2) NOT NULL DEFAULT 0.00",
        'service_type' => "varchar(30) NOT NULL DEFAULT 'custom'",
        'status' => "varchar(30) NOT NULL DEFAULT 'active'",
        'description' => "text DEFAULT NULL",
        'access_link' => "varchar(255) DEFAULT NULL",
        'custom_fields' => "text DEFAULT NULL",
    ),
    'users' => array(
        'name' => "varchar(191) NOT NULL DEFAULT ''",
        'email' => "varchar(191) NOT NULL DEFAULT ''",
        'mobile' => "varchar(30) NOT NULL DEFAULT ''",
        'balance' => "decimal(10,2) NOT NULL DEFAULT 0.00",
        'token' => "varchar(191) NOT NULL DEFAULT ''",
    ),
    'order_payments' => array(
        'order_id' => "int(11) NOT NULL DEFAULT 0",
        'user_id' => "int(11) NOT NULL DEFAULT 0",
        'method' => "varchar(20) NOT NULL DEFAULT 'bkash'",
        'sender_number' => "varchar(20) NOT NULL DEFAULT ''",
        'trx_id' => "varchar(64) NOT NULL DEFAULT ''",
        'amount' => "decimal(10,2) NOT NULL DEFAULT 0.00",
        'status' => "varchar(20) NOT NULL DEFAULT 'pending'",
        'slip_file' => "varchar(255) DEFAULT NULL",
        'created_at' => "timestamp NOT NULL DEFAULT current_timestamp()",
    ),
    'history_work' => array(
        'email' => "varchar(191) NOT NULL DEFAULT ''",
        'order_type' => "varchar(191) NOT NULL DEFAULT ''",
        'price' => "decimal(10,2) NOT NULL DEFAULT 0.00",
        'current_balance' => "decimal(10,2) NOT NULL DEFAULT 0.00",
        'balance_after_cut' => "decimal(10,2) NOT NULL DEFAULT 0.00",
        'order_time' => "datetime DEFAULT NULL",
    ),
);

$create_sql = array(
    'order_payments' => "CREATE TABLE IF NOT EXISTS `order_payments` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'history_work' => "CREATE TABLE IF NOT EXISTS `history_work` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `email` varchar(191) NOT NULL DEFAULT '',
        `order_type` varchar(191) NOT NULL DEFAULT '',
        `price` decimal(10,2) NOT NULL DEFAULT 0.00,
        `current_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
        `balance_after_cut` decimal(10,2) NOT NULL DEFAULT 0.00,
        `about_order` varchar(50) DEFAULT NULL,
        `order_time` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'payment_settings' => "CREATE TABLE IF NOT EXISTS `payment_settings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `method_key` varchar(30) NOT NULL DEFAULT '',
        `method_name` varchar(60) NOT NULL DEFAULT '',
        `number` varchar(30) NOT NULL DEFAULT '',
        `type` varchar(30) NOT NULL DEFAULT 'Personal',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
);

$log = array();

if ($fix) {
    foreach ($create_sql as $table => $sql) {
        if (!soko_db_table_exists($conn, $table)) {
            if (@$conn->query($sql)) {
                $log[] = array('ok', 'তৈরি হয়েছে: টেবিল ' . $table);
            } else {
                $log[] = array('bad', 'তৈরি করা যায়নি: ' . $table . ' — ' . $conn->error);
            }
        }
    }
    foreach ($required_columns as $table => $columns) {
        if (!soko_db_table_exists($conn, $table)) {
            continue;
        }
        foreach ($columns as $column => $definition) {
            if (!soko_db_column_exists($conn, $db_name, $table, $column)) {
                $sql = "ALTER TABLE `" . $table . "` ADD COLUMN `" . $column . "` " . $definition;
                if (@$conn->query($sql)) {
                    $log[] = array('ok', 'যোগ হয়েছে: ' . $table . '.' . $column);
                } else {
                    $log[] = array('bad', 'যোগ করা যায়নি: ' . $table . '.' . $column . ' — ' . $conn->error);
                }
            }
        }
    }
    if (count($log) === 0) {
        $log[] = array('ok', 'কিছু বদলাতে হয়নি — ডাটাবেজে সবকিছু আগে থেকেই ঠিক আছে।');
    }
}

$missing_tables = array();
$missing_columns = array();
foreach ($required_tables as $table => $label) {
    if (!soko_db_table_exists($conn, $table)) {
        $missing_tables[$table] = $label;
    }
}
foreach ($required_columns as $table => $columns) {
    if (!soko_db_table_exists($conn, $table)) {
        continue;
    }
    foreach ($columns as $column => $definition) {
        if (!soko_db_column_exists($conn, $db_name, $table, $column)) {
            $missing_columns[] = $table . '.' . $column;
        }
    }
}

$extra = array();
if (soko_db_table_exists($conn, 'history_work') && !soko_db_column_exists($conn, $db_name, 'history_work', 'about_order')) {
    $extra[] = 'history_work.about_order নেই — সমস্যা নেই, কোড নিজেই কলাম ছাড়া হিস্ট্রি লেখার চেষ্টা করে।';
}
if (!soko_db_table_exists($conn, 'order_payments')) {
    $extra[] = 'order_payments নেই — পেমেন্ট পেজ প্রথমবার খোলার সময় কোড নিজেই বানিয়ে নেয়।';
}
$all_ok = (count($missing_tables) === 0 && count($missing_columns) === 0);
?>

<div class="max-w-4xl mx-auto">

  <div class="bg-white rounded-2xl border border-slate-200 p-4 md:p-5 mb-4">
    <h1 class="text-lg md:text-xl font-extrabold text-slate-800 flex items-center gap-2">
      <i class="fa-solid fa-database text-emerald-600"></i> ডাটাবেস চেক
    </h1>
    <p class="text-xs md:text-sm text-slate-500 mt-1">
      সার্ভিস অর্ডার ও ম্যানুয়াল পেমেন্ট (সেন্ড মানি + TrxID) ফ্লো চালাতে ডাটাবেজে যা দরকার — সব এখানে দেখা যাচ্ছে।
      <span class="text-slate-400">ডাটাবেজ: <?php echo htmlspecialchars($db_name !== '' ? $db_name : '(অজানা)'); ?></span>
    </p>
  </div>

  <?php if ($fix): ?>
    <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-4">
      <h2 class="text-sm font-extrabold text-slate-800 mb-2">মেরামতের রিপোর্ট</h2>
      <?php foreach ($log as $entry): ?>
        <div class="flex items-start gap-2 py-1.5 border-b border-dashed border-slate-100 last:border-0 text-[13px]">
          <i class="fa-solid <?php echo $entry[0] === 'ok' ? 'fa-circle-check text-emerald-600' : 'fa-circle-xmark text-red-600'; ?> mt-0.5"></i>
          <span class="<?php echo $entry[0] === 'ok' ? 'text-slate-700' : 'text-red-600 font-semibold'; ?>"><?php echo htmlspecialchars($entry[1]); ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-4">
    <?php if ($all_ok): ?>
      <div class="flex items-center gap-2 text-emerald-700 font-bold text-sm">
        <i class="fa-solid fa-circle-check"></i> সব ঠিক আছে — কোনো SQL চালাতে হবে না।
      </div>
    <?php else: ?>
      <div class="flex items-start gap-2 text-red-600 font-bold text-sm">
        <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
        <span><?php echo (count($missing_tables) + count($missing_columns)); ?> টি জিনিস নেই। নিচের বাটনে চাপ দিলে কোড নিজেই তৈরি করে দেবে, অথবা নিচের টেবিল দেখে হাতে SQL চালাতে পারেন।</span>
      </div>
    <?php endif; ?>
  </div>

  <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-4 overflow-x-auto">
    <h2 class="text-sm font-extrabold text-slate-800 mb-2">টেবিল</h2>
    <table class="w-full text-[13px]">
      <thead>
        <tr class="text-left text-slate-500">
          <th class="py-1.5 pr-3">টেবিল</th>
          <th class="py-1.5 pr-3">কাজ</th>
          <th class="py-1.5">অবস্থা</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($required_tables as $table => $label): ?>
          <tr class="border-t border-slate-100">
            <td class="py-1.5 pr-3 font-mono text-[12px] text-slate-700"><?php echo htmlspecialchars($table); ?></td>
            <td class="py-1.5 pr-3 text-slate-500"><?php echo htmlspecialchars($label); ?></td>
            <td class="py-1.5 font-bold <?php echo soko_db_table_exists($conn, $table) ? 'text-emerald-600' : 'text-red-600'; ?>">
              <?php echo soko_db_table_exists($conn, $table) ? 'আছে' : 'নেই'; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-4">
    <h2 class="text-sm font-extrabold text-slate-800 mb-2">কলাম</h2>
    <?php if (count($missing_columns) === 0): ?>
      <p class="text-emerald-700 font-semibold text-[13px]"><i class="fa-solid fa-circle-check"></i> সব প্রয়োজনীয় কলাম আছে।</p>
    <?php else: ?>
      <p class="text-red-600 font-semibold text-[13px] mb-1">এই কলামগুলো নেই:</p>
      <ul class="list-disc pl-5 text-[13px] text-slate-700 font-mono">
        <?php foreach ($missing_columns as $col): ?>
          <li><?php echo htmlspecialchars($col); ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <?php if (count($extra) > 0): ?>
    <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-4">
      <h2 class="text-sm font-extrabold text-slate-800 mb-2">ঐচ্ছিক</h2>
      <?php foreach ($extra as $line): ?>
        <div class="flex items-start gap-2 py-1 text-[13px] text-slate-600">
          <i class="fa-regular fa-circle-info text-amber-500 mt-0.5"></i>
          <span><?php echo htmlspecialchars($line); ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-4 flex flex-wrap items-center gap-2">
    <a href="db_check.php?fix=1" class="bg-emerald-700 hover:bg-emerald-800 text-white text-[13px] font-bold px-4 py-2 rounded-xl">
      <i class="fa-solid fa-screwdriver-wrench"></i> যা নেই, তৈরি করে দাও
    </a>
    <a href="db_check.php" class="bg-white border border-emerald-700 text-emerald-700 text-[13px] font-bold px-4 py-2 rounded-xl">
      <i class="fa-solid fa-rotate"></i> আবার চেক করো
    </a>
    <a href="payment_verify.php" class="bg-white border border-slate-300 text-slate-700 text-[13px] font-bold px-4 py-2 rounded-xl">
      <i class="fa-solid fa-money-check-dollar"></i> পেমেন্ট যাচাই
    </a>
    <a href="index.php" class="bg-white border border-slate-300 text-slate-700 text-[13px] font-bold px-4 py-2 rounded-xl">
      <i class="fa-solid fa-house"></i> ড্যাশবোর্ড
    </a>
  </div>

</div>

<?php include_once("footer.php"); ?>
