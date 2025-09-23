#!/bin/bash

# Exit on any error
set -e

echo "🚀 Starting Laravel application..."

# Attendre que RabbitMQ soit disponible
echo "⏳ Waiting for RabbitMQ to be ready..."
timeout=30
count=0
until rabbitmqctl status >/dev/null 2>&1 || [ $count -eq $timeout ]; do
    echo "RabbitMQ not ready yet, waiting... ($count/$timeout)"
    sleep 2
    count=$((count+1))
done

if [ $count -eq $timeout ]; then
    echo "⚠️  RabbitMQ startup timeout, continuing anyway..."
else
    echo "✅ RabbitMQ is ready!"
    
    # Configurer RabbitMQ pour Laravel
    echo "🔧 Setting up RabbitMQ user for Laravel..."
    rabbitmqctl add_user GB78U4zfAm4hRMGS secret 2>/dev/null || echo "User already exists"
    rabbitmqctl set_user_tags GB78U4zfAm4hRMGS administrator 2>/dev/null || true
    rabbitmqctl set_permissions -p / GB78U4zfAm4hRMGS ".*" ".*" ".*" 2>/dev/null || true
fi

echo "✅ Database connection established"

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --force --no-interaction

# Install Octane with FrankenPHP
echo "🚀 Installing Octane with FrankenPHP..."
php artisan octane:install --server=frankenphp

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

# Start supervisor to manage processes
echo "🚀 Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf