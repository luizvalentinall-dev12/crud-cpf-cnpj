FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo_mysql mbstring bcmath zip

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --optimize-autoloader

RUN chown -R www-data:www-data /var/www/app/ \
    && chmod -R 775 /var/www/app/

ARG APP_ENV=production
RUN if [ "$APP_ENV" = "production" ]; then \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache; \
    fi

EXPOSE 80
EXPOSE 9003

CMD ["php-fpm"]
