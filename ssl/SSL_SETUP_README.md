# SSL Setup for ITQuty Laravel Application

## Certificate Details
- **Common Name**: 192.168.1.87
- **Subject Alternative Names**: 192.168.1.87, localhost, itquty.local
- **Valid Until**: 10 years from creation
- **Organization**: ITQuty

## Setup Instructions

### 1. Copy SSL Certificates
Run the `copy_ssl_certs.bat` file to copy certificates to your local Apache server.

### 2. Restart Apache
After copying certificates, restart Apache:
- Open XAMPP Control Panel
- Stop Apache
- Start Apache

### 3. Access Your Application
- HTTP: http://192.168.1.87/
- HTTPS: https://192.168.1.87/

### 4. Browser Security Warning
Since this is a self-signed certificate, your browser will show a security warning. Click "Advanced" and "Proceed to 192.168.1.87 (unsafe)" to continue.

## Certificate Files Location
- Certificate: `C:\xampp\apache\conf\ssl.crt\server.crt`
- Private Key: `C:\xampp\apache\conf\ssl.key\server.key`

## Troubleshooting
- If HTTPS still doesn't work, check Apache error logs
- Make sure port 443 is not blocked by firewall
- Verify the SSL virtual host configuration in `httpd-ssl.conf`