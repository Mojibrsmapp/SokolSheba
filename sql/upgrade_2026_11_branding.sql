-- Optional, repeatable upgrade: shared header/footer and admin branding.
-- Back up the database first, then run in phpMyAdmin on the existing site DB.
-- Existing settings are NEVER overwritten. admin/settings.php also creates
-- this table on first use, so no ALTER to users/admin_user is needed.
SET NAMES utf8mb4;
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'সকল সেবা'),
('site_tagline', 'সব কাজ, এক প্ল্যাটফর্মে'),
('site_logo', '/assets/logo.jpg'),
('nav_logo', ''),
('site_favicon', ''),
('support_email', 'Shokolsheba@gmail.com'),
('site_notice', 'সকল কাজ চালু আছে। সহায়তার জন্য সাপোর্টে যোগাযোগ করুন।')
ON DUPLICATE KEY UPDATE `setting_key` = VALUES(`setting_key`);
