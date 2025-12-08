FROM dunglas/frankenphp:latest AS base

WORKDIR /app

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    pcntl \
    pdo_pgsql \
    bcmath \
    redis \
    opcache \
    zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Development stage - includes composer and dev dependencies
FROM base AS development

WORKDIR /app

# Copy startup script
COPY docker/frankenphp/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# In development, we mount the entire project directory
# so we don't need to copy files here

ENTRYPOINT ["/usr/local/bin/start.sh"]

# Builder stage for production
FROM base AS builder

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader

COPY . .

RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Production stage - minimal image
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

