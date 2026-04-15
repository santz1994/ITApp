-- Create MySQL User Account for ITQuty Application
-- Run this in phpMyAdmin as root user

-- Create user 'itquty_user' with password 'itquty123'
-- Change the password to something more secure in production
CREATE USER IF NOT EXISTS 'itquty_user'@'localhost' IDENTIFIED BY 'itquty123';
CREATE USER IF NOT EXISTS 'itquty_user'@'127.0.0.1' IDENTIFIED BY 'itquty123';
CREATE USER IF NOT EXISTS 'itquty_user'@'192.168.1.87' IDENTIFIED BY 'itquty123';

-- Grant all privileges on itquty database
GRANT ALL PRIVILEGES ON itquty.* TO 'itquty_user'@'localhost';
GRANT ALL PRIVILEGES ON itquty.* TO 'itquty_user'@'127.0.0.1';
GRANT ALL PRIVILEGES ON itquty.* TO 'itquty_user'@'192.168.1.87';

-- Apply changes
FLUSH PRIVILEGES;

-- Show created users
SELECT User, Host FROM mysql.user WHERE User = 'itquty_user';

-- Test connection (optional)
-- SELECT 'User created successfully! You can now use: itquty_user / itquty123' AS status;
