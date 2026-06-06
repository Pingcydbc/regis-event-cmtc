FROM php:8.2-apache

# ติดตั้งส่วนเสริมที่จำเป็นสำหรับ PHP (mysqli, pdo, pdo_mysql)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# เปิดใช้งาน mod_rewrite ของ Apache สำหรับ .htaccess
RUN a2enmod rewrite

# คัดลอกโค้ดทั้งหมดไปยังโฟลเดอร์ของ Apache
COPY . /var/www/html/

# ตั้งค่าสิทธิ์การเข้าถึงไฟล์
RUN chown -R www-data:www-data /var/www/html/

# เปิดพอร์ต 80
EXPOSE 80
