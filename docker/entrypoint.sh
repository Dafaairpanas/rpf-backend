#!/bin/sh

echo "🚀 Starting Laravel application..."

# Navigate to app directory
cd /var/www/html

# Set default PORT if not set
export PORT=${PORT:-8080}
echo "📡 Using port: $PORT"

# Update Nginx port
echo "🔧 Configuring Nginx to listen on port $PORT..."
sed -i "s/listen 8080/listen $PORT/g" /etc/nginx/nginx.conf

# Create necessary directories
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache
mkdir -p /var/log/supervisor

# Set permissions
echo "🔐 Setting permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "⚠️ APP_KEY not set, generating..."
    php artisan key:generate --force
fi

# Clear any cached config first (important for env changes)
echo "🧹 Clearing old cache..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Cache configuration for production
echo "📦 Caching configuration..."
php artisan config:cache || echo "⚠️ Config cache failed, continuing..."
php artisan route:cache || echo "⚠️ Route cache failed, continuing..."
php artisan view:cache || echo "⚠️ View cache failed, continuing..."

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force || echo "⚠️ Migration failed, continuing..."

# Run seeder if RUN_SEED is set to true
if [ "$RUN_SEED" = "true" ]; then
    echo "🌱 Running database seeder..."
    php artisan db:seed --force || echo "⚠️ Seeding failed, continuing..."
fi

# Create storage symlink if not exists
if [ ! -L public/storage ]; then
    echo "🔗 Creating storage symlink..."
    php artisan storage:link || true
fi

# Start supervisor (which manages nginx, php-fpm, and queue worker)
echo "✅ Starting services on port $PORT..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
