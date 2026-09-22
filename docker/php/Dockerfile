FROM php:8.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
        ca-certificates \
        libpq-dev \
        libzip-dev \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
    && docker-php-ext-install pdo_pgsql pgsql zip gd bcmath mbstring exif \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock* ./
RUN composer install --no-scripts --no-interaction --prefer-dist --no-progress

COPY package.json package-lock.json* ./
RUN npm install

COPY . .

RUN composer install --no-scripts --no-interaction --prefer-dist --no-progress \
    && npm run build

RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8005

CMD ["bash", "-c", "mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache && chmod -R 775 storage bootstrap/cache && php artisan storage:link --force || true && (grep -qE '^APP_KEY=base64:' .env 2>/dev/null || php artisan key:generate --ansi --force) && php artisan config:clear && php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=8005"]
