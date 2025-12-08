FROM dunglas/frankenphp:latest AS builder

WORKDIR /app

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    nodejs \
    npm \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    pcntl \
    pdo_pgsql \
    bcmath \
    redis \
    opcache \
    zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader

COPY package.json package-lock.json ./
RUN npm ci --omit=dev

COPY . .
RUN npm run build

RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

FROM dunglas/frankenphp:latest AS production

WORKDIR /app

RUN install-php-extensions \
    pcntl \
    pdo_pgsql \
    bcmath \
    redis \
    opcache

COPY --from=builder /app /app

RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

ENTRYPOINT ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=8000"]

