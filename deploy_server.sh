#!/bin/bash
set -e

echo "=========================================================="
echo "🚀 [1/4] Pulling latest updates from GitHub..."
echo "=========================================================="
git pull origin main

echo "=========================================================="
echo "📦 [2/4] Syncing application code to rudrapg_app container..."
echo "=========================================================="
# Safely copy application folders, skipping mounted .env and storage to avoid 'device or resource busy' error
tar -C adminlaravel --exclude='.env' --exclude='storage' --exclude='vendor' --exclude='.git' -cf - . | docker cp - rudrapg_app:/var/www/html/

echo "=========================================================="
echo "🗄️ [3/4] Running database migrations..."
echo "=========================================================="
docker exec rudrapg_app php artisan migrate --force

echo "=========================================================="
echo "🧹 [4/4] Clearing and optimizing caches..."
echo "=========================================================="
docker exec rudrapg_app php artisan optimize:clear

echo "=========================================================="
echo "✅ Production deployment complete! All systems operational."
echo "=========================================================="
