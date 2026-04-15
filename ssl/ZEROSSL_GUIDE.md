# Manual Let's Encrypt Setup (Alternatif)

## Jika Win-ACME Terlalu Rumit

### Step 1: Gunakan ZeroSSL atau SSLForFree
1. **Buka website**: https://zerossl.com/free-ssl/
2. **Masukkan domain**: yourdomain.com
3. **Verifikasi domain**: Upload file ke `C:\xampp\htdocs\quty2\public\.well-known\acme-challenge\`

### Step 2: Download Certificate
- Download certificate files (certificate.crt, ca-bundle.crt, private.key)
- Gabung certificate + ca-bundle menjadi fullchain.pem

### Step 3: Install Certificate
```bash
# Copy ke Apache directory
copy certificate.crt C:\xampp\apache\conf\ssl.crt\server.crt
copy private.key C:\xampp\apache\conf\ssl.key\server.key

# Jika perlu fullchain
copy /b certificate.crt + ca-bundle.crt fullchain.pem
copy fullchain.pem C:\xampp\apache\conf\ssl.crt\server.crt
```

### Step 4: Restart Apache
```bash
net stop apache2.4
net start apache2.4
```

## ✅ Keuntungan ZeroSSL:
- ✅ Gratis seperti Let's Encrypt
- ✅ Web interface (tidak perlu command line)
- ✅ Valid 90 hari, bisa renew
- ✅ Diakui semua browser

## 📋 Checklist:
- [ ] Domain pointing ke server IP
- [ ] Port 80 terbuka
- [ ] Apache running
- [ ] Certificate terinstall dengan benar
- [ ] Apache restart

## 🔄 Renewal:
- ZeroSSL kirim email reminder sebelum expired
- Repeat process untuk renew

**Ini cara termudah untuk Windows server!** 🎉