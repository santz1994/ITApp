-- Fix privileges for all itquty_user accounts
-- Run this in phpMyAdmin on 192.168.1.87

-- Grant ALL PRIVILEGES to all itquty_user accounts that only have USAGE
GRANT ALL PRIVILEGES ON `itquty`.* TO 'itquty_user'@'%';
GRANT ALL PRIVILEGES ON `itquty`.* TO 'itquty_user'@'192.168.1.%';
GRANT ALL PRIVILEGES ON `itquty`.* TO 'itquty_user'@'desktop-qumpipu';

-- Apply changes immediately
FLUSH PRIVILEGES;

-- Verify all privileges are now correct
SHOW GRANTS FOR 'itquty_user'@'%';
SHOW GRANTS FOR 'itquty_user'@'192.168.1.%';
SHOW GRANTS FOR 'itquty_user'@'desktop-qumpipu';
SHOW GRANTS FOR 'itquty_user'@'localhost';
SHOW GRANTS FOR 'itquty_user'@'127.0.0.1';
SHOW GRANTS FOR 'itquty_user'@'192.168.1.87';

SELECT 'All privileges fixed! Test your connection now.' AS status;
