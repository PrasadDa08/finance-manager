FROM php:8.4-cli

# system deps

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev libpng-dev libonig-dev \
    && docker-php-ext-install pdo pdo_pgsql zip bcmath \
    && rm -rf /var/lib/apt/lists/*

# composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# copy app
COPY . .

# install php deps
RUN composer install --no-dev --optimize-autoloader --no-interaction

# permissions
RUN chmod -R 777 storage bootstrap/cache

EXPOSE 8000

CMD php artisan migrate --force && php artisan serve --host 0.0.0.0 --port ${PORT:-8000}