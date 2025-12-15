# Sử dụng PHP 8.4 (Khớp với Laravel 12)
FROM php:8.4-apache

# 1. Cài đặt thư viện hệ thống & Extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    libicu-dev \
    && docker-php-ext-install pdo_mysql zip intl opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Bật mod_rewrite
RUN a2enmod rewrite

# 3. Cấu hình Document Root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# --- QUAN TRỌNG 1: SỬA LỖI 404 NOT FOUND ---
RUN sed -ri -e 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

# 4. Copy cấu hình PHP chuẩn
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# --- QUAN TRỌNG 2: TĂNG GIỚI HẠN UPLOAD (THÊM ĐOẠN NÀY) ---
# Tạo file cấu hình riêng để ghi đè giới hạn 2MB mặc định
RUN echo "upload_max_filesize = 64M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini

# 5. Cài Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Thiết lập thư mục làm việc
WORKDIR /var/www/html

# 7. Copy toàn bộ code
COPY . .

# 8. Cài đặt gói thư viện
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs

# 9. Cấp quyền (Sửa thành 777 cho storage để chắc chắn không lỗi quyền ghi trên Render)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 777 storage bootstrap/cache

# 10. Startup Command
# Thêm 'php artisan storage:link' vào đây là CHUẨN để fix lỗi ảnh không hiện
CMD bash -c "php artisan migrate --force && php artisan config:clear && php artisan route:cache && php artisan view:cache && php artisan storage:link && apache2-foreground"