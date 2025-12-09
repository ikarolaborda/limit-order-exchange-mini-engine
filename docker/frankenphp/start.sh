#!/bin/sh
set -e

# Install composer dependencies if vendor folder doesn't exist
if [ ! -d "/app/vendor" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction
fi

# Run database migrations
php artisan migrate --force

# If arguments were passed, use them; otherwise default to Octane
if [ $# -gt 0 ]; then
    exec "$@"
else
    exec php artisan octane:frankenphp --host=0.0.0.0 --port=8000
fi
