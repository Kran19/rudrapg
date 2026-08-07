#!/bin/bash

# Exit immediately if a command exits with a non-zero status
set -e

echo "======================================"
echo "🚀 Starting Deployment for Rudra PG..."
echo "======================================"

# 1. Pull the latest code from GitHub
echo "📥 Pulling latest code from GitHub (main branch)..."
git pull origin main

# 2. Install/Update PHP dependencies
echo "📦 Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev

# 3. Clear and rebuild caches
echo "🧹 Clearing old caches..."
php artisan optimize:clear

echo "⚙️ Caching Configuration..."
php artisan config:cache

echo "🛣️ Caching Routes..."
php artisan route:cache

echo "🎨 Caching Views..."
php artisan view:cache

# 4. Environment & Keys
if [ ! -f .env ]; then
    echo "📝 .env file not found. Copying from .env.example..."
    cp .env.example .env
    echo "🔑 Generating Application Key..."
    php artisan key:generate --force
fi

# 5. Run Migrations (Safe for production with --force)
echo "🗄️ Running database migrations..."
php artisan migrate --force

# 6. Storage Link & Permissions
echo "🔗 Creating storage link manually (bypassing disabled PHP exec())..."
rm -rf public/storage
ln -s ../storage/app/public public/storage || true

echo "🔐 Setting folder permissions..."
chmod -R 775 storage bootstrap/cache

echo "======================================"
echo "✅ Deployment completed successfully!"
echo "======================================"
