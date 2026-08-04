FROM base-laravel:8.4

WORKDIR /var/www

COPY . .

RUN composer install --no-interaction --optimize-autoloader

EXPOSE 8003

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8003"]
