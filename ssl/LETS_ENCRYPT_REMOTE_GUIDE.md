# Let's Encrypt Setup untuk Server Remote

## ⚠️ Penting: Server Berjalan di PC Lain

Karena Apache server berjalan di PC lain, setup Let's Encrypt harus dilakukan di server tersebut.

## 📋 Langkah Setup di Server Remote:

### 1. Akses Server Remote
```bash
# Via RDP atau SSH ke server
# Jalankan commands berikut di server
```

### 2. Install Certbot di Server
```bash
# Install Chocolatey (jika belum ada)
powershell -NoProfile -ExecutionPolicy Bypass -Command "iex ((New-Object System.Net.WebClient).DownloadString('https://chocolatey.org/install.ps1'))"

# Install Certbot
choco install certbot -y
```

### 3. Generate Certificate
```bash
# Pastikan domain mengarah ke server IP
# Port 80 harus terbuka

certbot certonly --webroot -w "C:\xampp\htdocs\quty2\public" -d yourdomain.com --agree-tos --email admin@yourdomain.com
```

### 4. Copy Certificates
```bash
# Copy ke Apache directory
copy "C:\ProgramData\letsencrypt\live\yourdomain.com\fullchain.pem" "C:\xampp\apache\conf\ssl.crt\server.crt"
copy "C:\ProgramData\letsencrypt\live\yourdomain.com\privkey.pem" "C:\xampp\apache\conf\ssl.key\server.key"
```

### 5. Restart Apache
```bash
net stop apache2.4
net start apache2.4
```

## 🔧 Alternative Solutions:

### Option 1: Self-Signed Certificate (Development)
- Gunakan certificate yang sudah dibuat
- Browser akan tetap show "Not Secure" tapi HTTPS berfungsi

### Option 2: Commercial Certificate
- Beli dari DigiCert/GlobalSign
- Upload manual ke server

### Option 3: Internal CA
- Setup Certificate Authority internal
- Distribute via Group Policy

## 📞 Rekomendasi:
1. **Hubungi IT Admin** untuk setup Let's Encrypt di server
2. **Atau gunakan self-signed** untuk development
3. **Atau setup domain** dan DNS pointing ke server IP

Apakah Anda bisa akses server tersebut langsung?