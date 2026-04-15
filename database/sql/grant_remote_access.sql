-- Grant Remote Access for itquty_user
-- Run this on the MySQL server at 192.168.1.87 in phpMyAdmin

-- Option 1: Grant access from your specific PC
CREATE USER IF NOT EXISTS 'itquty_user'@'DESKTOP-QUMPIPU' IDENTIFIED BY 'itquty123';
GRANT ALL PRIVILEGES ON `itquty`.* TO 'itquty_user'@'DESKTOP-QUMPIPU';

-- Option 2: Grant access from any IP in the network (192.168.1.x)
CREATE USER IF NOT EXISTS 'itquty_user'@'192.168.1.%' IDENTIFIED BY 'itquty123';
GRANT ALL PRIVILEGES ON `itquty`.* TO 'itquty_user'@'192.168.1.%';

-- Option 3: Grant access from anywhere (LESS SECURE - use only in development)
CREATE USER IF NOT EXISTS 'itquty_user'@'%' IDENTIFIED BY 'itquty123';
GRANT ALL PRIVILEGES ON `itquty`.* TO 'itquty_user'@'%';

-- Apply changes
FLUSH PRIVILEGES;

-- Verify the users
SELECT User, Host FROM mysql.user WHERE User = 'itquty_user';

SELECT 'Remote access granted! Now test connection from your PC.' AS status;
