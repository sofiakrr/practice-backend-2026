FROM php:8.2-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev \
    && docker-php-ext-install pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && chmod +x /var/www/docker/entrypoint.sh \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000

ENTRYPOINT ["/var/www/docker/entrypoint.sh"]
