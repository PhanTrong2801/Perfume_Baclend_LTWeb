# Sử dụng PHP 8.3
FROM php:8.3-apache

# 1. Cài đặt thư viện hệ thống & Extensions
# Thêm 'opcache' để tăng tốc website PHP lên rất nhiều
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

# 4. Copy cấu hình PHP chuẩn cho Production (Quan trọng)
# File này chứa cấu hình tối ưu sẵn của PHP
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# 5. Cài Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Thiết lập thư mục làm việc
WORKDIR /var/www/html

# 7. Copy toàn bộ code
COPY . .

# 8. Cài đặt gói thư viện
# Bỏ --ignore-platform-reqs để đảm bảo môi trường thực tế đủ extension
# Thêm --no-scripts để tránh lỗi chạy lệnh artisan khi chưa có .env
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 9. Cấp quyền (Quan trọng cho Render)
# Render đôi khi chạy user ngẫu nhiên, nhưng chown www-data vẫn là chuẩn nhất cho Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# 10. Chạy lệnh migrate và khởi động Apache khi container start
# Tạo file script entrypoint ảo hoặc chạy trực tiếp
CMD bash -c "php artisan config:cache && php artisan route:cache && php artisan view:cache && apache2-foreground"