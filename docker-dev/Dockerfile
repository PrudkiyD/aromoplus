FROM php:8.2-apache

# Встановлюємо необхідні розширення
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libicu-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl

# Увімкнення mod_rewrite для роботи .htaccess
RUN a2enmod rewrite

# Дозволяємо Apache читати .htaccess у папці /var/www/html
RUN sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html