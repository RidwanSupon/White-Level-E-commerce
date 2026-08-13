#!/usr/bin/env bash
set -e

echo "🚀 Starting Production Boot Sequence for Render Deployment..."

# 1. Verify APP_KEY exists
if [ -z "$APP_KEY" ]; then
    echo "⚠️ WARNING: APP_KEY is not set in environment! Generating fallback key..."
    php artisan key:generate --force
fi

# 2. Storage Link
echo "🔗 Ensuring Storage Link..."
php artisan storage:link --force || true

# 3. Clear Stale Caches
echo "🧹 Clearing stale config and route caches..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# 4. Database Migrations (Safe force run)
echo "🗄️ Executing database migrations..."
php artisan migrate --force

# 5. Production Optimization Caching
echo "⚡ Optimizing application configuration and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Production Preparation Complete. Starting Web Server..."
exec "$@"
