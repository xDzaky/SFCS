# 📦 Panduan Instalasi SFCS (School Facility Complaint System)

## Persyaratan Sistem

### Server Requirements
- **PHP**: >= 8.2.0
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Web Server**: Apache / Nginx
- **Composer**: Latest version
- **Node.js**: >= 18.x
- **NPM**: Latest version

### PHP Extensions Required
- PDO
- pdo_mysql
- mbstring
- fileinfo
- openssl
- tokenizer
- xml
- ctype
- json
- bcmath
- curl
- zip
- gd atau imagick (untuk manipulasi gambar)
- intl

---

## 🚀 Instalasi untuk Shared Hosting (cPanel)

### Step 1: Persiapan Database
1. Login ke **cPanel** hosting Anda
2. Buka **MySQL Databases**
3. Buat database baru:
   - Database name: `namasekolah_sfcs`
   - Create database
4. Buat user database:
   - Username: `namasekolah_sfcsuser`
   - Password: (password yang kuat)
   - Create user
5. Tambahkan user ke database dengan **ALL PRIVILEGES**

### Step 2: Upload Files
1. Download source code dari repository
2. Extract file zip
3. Upload semua file ke folder `public_html` atau subdomain folder
4. Pastikan struktur seperti ini:
   ```
   public_html/
   ├── app/
   ├── bootstrap/
   ├── config/
   ├── database/
   ├── public/
   ├── resources/
   ├── routes/
   ├── storage/
   ├── vendor/
   └── ...
   ```

### Step 3: Konfigurasi PHP Version
1. Di cPanel, buka **MultiPHP Manager** atau **Select PHP Version**
2. Pilih domain/subdomain Anda
3. Set PHP version ke **8.2** atau lebih tinggi
4. Centang semua extensions yang required (lihat list di atas)
5. Save changes

### Step 4: Install Dependencies
1. Buka **Terminal** di cPanel
2. Masuk ke folder project:
   ```bash
   cd ~/public_html
   # atau jika menggunakan subdomain:
   cd ~/sfcs
   ```

3. Install Composer dependencies:
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

4. Install Node.js dependencies:
   ```bash
   npm install
   npm run build
   ```

### Step 5: Konfigurasi Environment
1. Copy file `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```

2. Edit file `.env` sesuai dengan konfigurasi hosting:
   ```env
   APP_NAME="SFCS - Nama Sekolah"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://sfcs.namasekolah.sch.id
   
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=namasekolah_sfcs
   DB_USERNAME=namasekolah_sfcsuser
   DB_PASSWORD=password_database_anda
   
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=email@sekolah.sch.id
   MAIL_PASSWORD=password_email
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=noreply@sekolah.sch.id
   MAIL_FROM_NAME="SFCS Sekolah"
   ```

3. Generate application key:
   ```bash
   php artisan key:generate
   ```

### Step 6: Setup Database
1. Jalankan migrasi database:
   ```bash
   php artisan migrate --force
   ```

2. Seed data awal (kategori, gedung, user admin):
   ```bash
   php artisan db:seed --force
   ```

### Step 7: Set Permissions
```bash
chmod -R 775 storage bootstrap/cache
chown -R username:username storage bootstrap/cache
```

Ganti `username` dengan user cPanel Anda.

### Step 8: Configure Web Server

#### Untuk cPanel (Apache)
1. Buat file `.htaccess` di root folder:
   ```apache
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteRule ^(.*)$ public/$1 [L]
   </IfModule>
   ```

2. Atau set **Document Root** ke folder `public`:
   - Di cPanel → Domains → pilih domain → Document Root
   - Ubah ke: `/home/username/public_html/public`

### Step 9: Optimize untuk Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 10: Test Akses
1. Buka browser dan akses domain: `https://sfcs.namasekolah.sch.id`
2. Login dengan akun default:
   - **Super Admin**: superadmin@sfcs.sch.id / password
   - **Admin**: admin@sfcs.sch.id / password
   - **Kepala Sekolah**: kepsek@sfcs.sch.id / password

3. ⚠️ **PENTING**: Segera ganti password default!

---

## 🖥️ Instalasi untuk VPS/Dedicated Server

### Step 1: Install Required Software
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2 dan extensions
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring \
php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-intl \
php8.2-bcmath php8.2-tokenizer -y

# Install MySQL
sudo apt install mysql-server -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y
```

### Step 2: Setup Database
```bash
sudo mysql

CREATE DATABASE sfcs_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sfcs_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON sfcs_db.* TO 'sfcs_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 3: Clone & Setup Project
```bash
cd /var/www
sudo git clone https://github.com/yourusername/SFCS.git
cd SFCS

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Setup environment
cp .env.example .env
nano .env  # Edit sesuai konfigurasi

# Generate key & migrate
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force

# Set permissions
sudo chown -R www-data:www-data /var/www/SFCS
sudo chmod -R 775 storage bootstrap/cache
```

### Step 4: Configure Nginx
```bash
sudo nano /etc/nginx/sites-available/sfcs
```

Paste konfigurasi:
```nginx
server {
    listen 80;
    server_name sfcs.namasekolah.sch.id;
    root /var/www/SFCS/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/sfcs /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Step 5: Setup SSL (Let's Encrypt)
```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d sfcs.namasekolah.sch.id
```

### Step 6: Setup Cron Job untuk Scheduled Tasks
```bash
crontab -e
```

Tambahkan:
```cron
* * * * * cd /var/www/SFCS && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔧 Konfigurasi Email Notifikasi

### Menggunakan Gmail SMTP
1. Aktifkan **2-Factor Authentication** di akun Gmail
2. Generate **App Password**: https://myaccount.google.com/apppasswords
3. Edit `.env`:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=email@gmail.com
   MAIL_PASSWORD=app_password_16_digit
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=noreply@sekolah.sch.id
   MAIL_FROM_NAME="SFCS Sekolah"
   ```

### Menggunakan Mailgun / SendGrid / SES
Lihat dokumentasi Laravel untuk konfigurasi: https://laravel.com/docs/mail

---

## 👥 Akun Default

Setelah seeding, sistem akan membuat akun berikut:

| Role | Email | Password | Akses |
|------|-------|----------|-------|
| Super Admin | superadmin@sfcs.sch.id | password | Full access system |
| Admin | admin@sfcs.sch.id | password | Kelola pengaduan |
| Kepala Sekolah | kepsek@sfcs.sch.id | password | Laporan & monitoring |
| Teknisi | teknisi1@sfcs.sch.id | password | Handle pengaduan |
| Guru | sri.wahyuni@sfcs.sch.id | password | Buat pengaduan |
| Siswa | andi@sfcs.sch.id | password | Buat pengaduan |

⚠️ **SANGAT PENTING**: Segera ganti semua password default setelah instalasi!

---

## 🔐 Keamanan Production

### 1. Hapus Route Debug
Edit `routes/web.php`, hapus atau comment route testing.

### 2. Disable Debug Mode
Di `.env`:
```env
APP_DEBUG=false
APP_ENV=production
```

### 3. Set Proper Permissions
```bash
# Files: 644
find /path/to/sfcs -type f -exec chmod 644 {} \;

# Directories: 755
find /path/to/sfcs -type d -exec chmod 755 {} \;

# Storage & Cache: 775
chmod -R 775 storage bootstrap/cache
```

### 4. Update Dependencies Regularly
```bash
composer update
npm update
```

### 5. Backup Database Rutin
```bash
# Manual backup
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# Setup automated backup dengan cron
```

---

## 🐛 Troubleshooting

### 500 Internal Server Error
```bash
# Check logs
tail -50 storage/logs/laravel.log

# Clear all cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Fix permissions
chmod -R 775 storage bootstrap/cache
```

### 403 Forbidden
- Pastikan Document Root mengarah ke folder `public`
- Check file permissions
- Pastikan `.htaccess` ada di folder `public`

### Database Connection Error
- Pastikan database sudah dibuat
- Check credentials di `.env`
- Test koneksi: `php artisan tinker` → `DB::connection()->getPdo();`

### Email Tidak Terkirim
- Check SMTP credentials di `.env`
- Test email: `php artisan tinker` → `Mail::raw('Test', function($msg) { $msg->to('test@email.com'); });`
- Check log: `storage/logs/laravel.log`

---

## 📞 Support

Untuk bantuan teknis atau pertanyaan:
- Email: support@xdzaky.my.id
- Documentation: [README.md](README.md)
- Technical Spec: [tech-spec-document.md](tech-spec-document.md)

---

## 📄 License

This software is licensed for commercial use by schools.
See [LICENSE.md](LICENSE.md) for details.

---

**© 2026 SFCS - School Facility Complaint System**
**Developed by xDzaky**
