FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    libonig-dev \
    libxml2-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd pdo pdo_mysql mbstring exif pcntl bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /var/www/html
COPY --chown=www-data:www-data . /var/www/html

WORKDIR /var/www/html

RUN composer install --no-interaction --optimize-autoloader --no-dev

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

RUN a2enmod rewrite

EXPOSE 80