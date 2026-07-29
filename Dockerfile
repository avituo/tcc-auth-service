FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl \
    && docker-php-ext-install pdo pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-interaction --optimize-autoloader

EXPOSE 8003

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8003"]
