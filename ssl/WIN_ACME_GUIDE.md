# Let's Encrypt Setup untuk Windows Server

## ❌ Certbot Tidak Tersedia di Chocolatey

Certbot untuk Windows tidak tersedia di repository Chocolatey standar. Gunakan cara alternatif:

## ✅ Cara Install Certbot di Windows:

### Method 1: Win-ACME (Rekomendasi)
```powershell
# Download Win-ACME
Invoke-WebRequest -Uri "https://github.com/win-acme/win-acme/releases/latest/download/win-acme.v2.2.9.1701.x64.pluggable.zip" -OutFile "win-acme.zip"

# Extract
Expand-Archive -Path "win-acme.zip" -DestinationPath "C:\win-acme"

# Jalankan
cd "C:\win-acme"
.\wacs.exe
```

### Method 2: Python pip (Alternative)
```bash
# Install Python (jika belum ada)
# Download dari https://www.python.org/downloads/

# Install Certbot via pip
pip install certbot

# Jalankan certbot
certbot certonly --webroot -w "C:\xampp\htdocs\quty2\public" -d yourdomain.com
```

### Method 3: Manual Certificate
1. **Generate CSR** di server
2. **Request certificate** dari Let's Encrypt staging
3. **Install certificate** manual

## 📋 Step-by-Step dengan Win-ACME:

### 1. Download & Extract Win-ACME
```powershell
# Download
Invoke-WebRequest -Uri "https://github.com/win-acme/win-acme/releases/download/v2.2.9.1701/win-acme.v2.2.9.1701.x64.pluggable.zip" -OutFile "C:\win-acme.zip"

# Extract
Expand-Archive -Path "C:\win-acme.zip" -DestinationPath "C:\win-acme"
```

### 2. Jalankan Win-ACME
```bash
cd C:\win-acme
wacs.exe
```

### 3. Pilih Option:
- **N**: Create new certificate
- **1**: Single binding of an IIS site (jika pakai IIS)
- **2**: Manual input (untuk Apache/XAMPP)

### 4. Konfigurasi:
- **Domain**: yourdomain.com
- **Web root path**: C:\xampp\htdocs\quty2\public
- **Store type**: PEM Files
- **Path**: C:\xampp\apache\conf\ssl.crt\server.crt (certificate)
- **Path**: C:\xampp\apache\conf\ssl.key\server.key (key)

### 5. Copy Certificate ke Apache
```bash
# Win-ACME akan auto-copy jika dikonfigurasi dengan benar
# Jika manual:
copy "C:\win-acme\yourdomain.com.crt" "C:\xampp\apache\conf\ssl.crt\server.crt"
copy "C:\win-acme\yourdomain.com.key" "C:\xampp\apache\conf\ssl.key\server.key"
```

## 🔄 Auto-Renewal
Win-ACME mendukung auto-renewal via Windows Task Scheduler.

## 📞 Jika Masih Bermasalah:
1. **Gunakan self-signed certificate** untuk sementara
2. **Hubungi IT admin** untuk setup certificate
3. **Gunakan commercial certificate** dari DigiCert

Apakah Anda ingin saya buat script lengkap untuk Win-ACME?