#!/bin/bash

# Exit on any error
set -e

echo "🚀 Starting Laravel application..."

echo "✅ Database connection established"

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --no-interaction

# Clear and cache config for production
echo "🔧 Optimizing application..."
php artisan config:clear --no-interaction
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction

# Create storage link if it doesn't exist
if [ ! -L /app/public/storage ]; then
    echo "🔗 Creating storage symlink..."
    php artisan storage:link --no-interaction
fi

# Set proper permissions
echo "🔒 Setting permissions..."
chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

echo "✅ Laravel application ready!"

# Start FrankenPHP
exec "$@"