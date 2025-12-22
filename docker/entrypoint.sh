#!/bin/sh
set -e

echo "🚀 Starting Laravel application..."

# Navigate to app directory
cd /var/www/html

# Cache configuration for production
echo "📦 Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Create storage symlink if not exists
if [ ! -L public/storage ]; then
    echo "🔗 Creating storage symlink..."
    php artisan storage:link
fi

# Set correct permissions
echo "🔐 Setting permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Create supervisor log directory
mkdir -p /var/log/supervisor

# Update Nginx port from environment variable if set
if [ -n "$PORT" ]; then
    echo "🔧 Configuring Nginx to listen on port $PORT..."
    sed -i "s/listen 8080/listen $PORT/" /etc/nginx/nginx.conf
fi

# Start supervisor (which manages nginx, php-fpm, and queue worker)
echo "✅ Starting services..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
