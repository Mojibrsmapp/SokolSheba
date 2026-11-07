-- =====================================================================
--  Sokol Sheba — নভেম্বর ২০২৬ আপগ্রেড (সার্ভিস অর্ডার + ম্যানুয়াল পেমেন্ট)
-- =====================================================================
--  সাধারণত এই ফাইল চালানোর দরকার নেই:
--    * order_payments টেবিলটা কোড নিজেই প্রথম ব্যবহারের সময় বানিয়ে নেয়
--    * অ্যাডমিন → "ডাটাবেস চেক" (admin/db_check.php) পেজে সব অবস্থা দেখা যায়,
--      আর "যা নেই তৈরি করে দাও" বাটনে চাপ দিলেই বাকিটা হয়ে যায়।
--
--  তবু যারা phpMyAdmin দিয়ে হাতে চালাতে চান, শুধু নিচের অংশটুকু রান করুন।
--  বারবার চালালেও সমস্যা নেই (IF NOT EXISTS)।
-- =====================================================================

-- ১) ম্যানুয়াল পেমেন্ট (সেন্ড মানি + TrxID) রেকর্ড — একই TrxID দুইবার নেওয়া যাবে না
CREATE TABLE IF NOT EXISTS `order_payments` (
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_trx` (`trx_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ২) ঐচ্ছিক: ব্যালেন্স হিস্ট্রিতে অর্ডারের ধরন (টেবিল/কলাম আগে থেকে থাকলে এই লাইনটা স্কিপ করুন)
-- ALTER TABLE `history_work` ADD COLUMN `about_order` varchar(50) DEFAULT NULL;

-- ৩) ঐচ্ছিক: সাইট সেটিংসে বিকাশ/নগদ নম্বর (অ্যাডমিন → সেটিংস থেকেও দেওয়া যায়)
-- INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('bkash_number', '01408905723')
--   ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);
-- INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('nagad_number', '01897494691')
--   ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);
