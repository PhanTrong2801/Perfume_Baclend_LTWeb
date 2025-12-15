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

# --- QUAN TRỌNG: SỬA LỖI 404 NOT FOUND ---
# Cho phép Laravel sử dụng file .htaccess để điều hướng route
RUN sed -ri -e 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

# 4. Copy cấu hình PHP chuẩn cho Production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# 5. Cài Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Thiết lập thư mục làm việc
WORKDIR /var/www/html

# 7. Copy toàn bộ code
COPY . .

# 8. Cài đặt gói thư viện
# Thêm --ignore-platform-reqs để tránh lỗi version nhỏ nhặt trên cloud
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs

# 9. Cấp quyền
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# 10. Startup Command (Tự động Migrate & Cache)
# Khi server khởi động:
# 1. Chạy migrate (tạo bảng database tự động)
# 2. Cache config/route để website chạy nhanh hơn
# 3. Bật Apache
CMD bash -c "php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache && apache2-foreground && php artisan storage:link"