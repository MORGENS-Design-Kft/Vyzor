#!/bin/sh
set -e

cd /var/www/html

# Make all project files readable by www-data (PHP-FPM worker user)
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

# Ensure storage directories exist and are writable
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/logs
mkdir -p bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Install/update PHP dependencies
composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Install and build frontend assets
npm ci
npm run build

# Cache config for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Run migrations
php artisan migrate --force

# Create storage symlink
php artisan storage:link 2>/dev/null || true

# Execute the passed command (defaults to php-fpm from CMD)
exec "$@"
