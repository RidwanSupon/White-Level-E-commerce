#!/bin/sh
set -e

echo "🚀 Starting Production Boot Sequence for Render Deployment..."

# 1. Verify storage and bootstrap cache directories exist with correct permissions
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/testing \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 2. Verify APP_KEY exists
if [ -z "$APP_KEY" ]; then
    echo "⚠️ WARNING: APP_KEY is not set in environment! Generating fallback key..."
    php artisan key:generate --force
fi

# 3. Storage Link
echo "🔗 Ensuring Storage Link..."
php artisan storage:link --force || true

# 4. Clear Stale Caches
echo "🧹 Clearing stale config and route caches..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# 5. Database Migrations (Safe force run for production)
if [ "$APP_ENV" = "production" ] || [ -n "$DATABASE_URL" ] || [ -n "$DB_HOST" ]; then
    echo "🗄️ Executing database migrations..."
    php artisan migrate --force
fi

# 6. Production Optimization Caching
echo "⚡ Optimizing application configuration and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Production Boot Preparation Complete. Starting Web Server..."
exec "$@"
