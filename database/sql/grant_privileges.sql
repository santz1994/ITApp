-- Grant Full Privileges to itquty_user on itquty database
-- Run this in phpMyAdmin SQL tab

-- Grant all privileges on itquty database
GRANT ALL PRIVILEGES ON `itquty`.* TO 'itquty_user'@'localhost';
GRANT ALL PRIVILEGES ON `itquty`.* TO 'itquty_user'@'127.0.0.1';
GRANT ALL PRIVILEGES ON `itquty`.* TO 'itquty_user'@'192.168.1.87';

-- Apply changes immediately
FLUSH PRIVILEGES;

-- Verify privileges were granted
SHOW GRANTS FOR 'itquty_user'@'localhost';
SHOW GRANTS FOR 'itquty_user'@'127.0.0.1';
SHOW GRANTS FOR 'itquty_user'@'192.168.1.87';

SELECT 'Privileges granted successfully!' AS status;
