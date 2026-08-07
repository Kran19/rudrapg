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

# 4. Run Migrations (Safe for production with --force)
echo "🗄️ Running database migrations..."
php artisan migrate --force

echo "======================================"
echo "✅ Deployment completed successfully!"
echo "======================================"
