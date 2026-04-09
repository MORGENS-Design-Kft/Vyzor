#!/bin/sh
set -e

cd /var/www/html

# Ensure storage directories exist and are writable by www-data
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/logs
mkdir -p bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Execute the passed command (defaults to php-fpm from CMD)
exec "$@"
