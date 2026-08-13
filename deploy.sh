#!/usr/bin/env bash

set -e

echo "🚀 Starting Production Deployment for Enterprise E-Commerce Platform..."

# 1. Install / Update PHP Dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

# 2. Run Database Migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# 3. Optimize & Cache Configuration
echo "⚡ Caching configurations, routes, and views..."
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache

echo "✅ Production Deployment Completed Successfully!"
