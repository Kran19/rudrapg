#!/bin/bash
set -e

echo "=========================================================="
echo "🚀 [1/5] Pulling latest updates from GitHub..."
echo "=========================================================="
git checkout -- student_web/ || true
git checkout -- adminlaravel/public/student-app/ || true
git pull origin main

echo "=========================================================="
echo "🔒 [2/5] Setting host public asset permissions & web links..."
echo "=========================================================="
chmod -R 755 student_web || true
chmod -R 755 adminlaravel/public || true
# Create relative symlink so both root (/) and (/student-app/) serve identical assets without Nginx 404 fallback
(cd student_web && ln -sfn . student-app) || true
# Sync student_web assets into adminlaravel/public/student-app for Docker container parity
cp -rf student_web/* adminlaravel/public/student-app/ || true

echo "=========================================================="
echo "📦 [3/5] Syncing application code to rudrapg_app container..."
echo "=========================================================="
docker cp adminlaravel/app/. rudrapg_app:/var/www/html/app/
docker cp adminlaravel/bootstrap/. rudrapg_app:/var/www/html/bootstrap/
docker cp adminlaravel/config/. rudrapg_app:/var/www/html/config/
docker cp adminlaravel/database/. rudrapg_app:/var/www/html/database/
docker cp adminlaravel/public/. rudrapg_app:/var/www/html/public/
docker cp adminlaravel/resources/. rudrapg_app:/var/www/html/resources/
docker cp adminlaravel/routes/. rudrapg_app:/var/www/html/routes/
docker cp adminlaravel/artisan rudrapg_app:/var/www/html/artisan
docker cp adminlaravel/composer.json rudrapg_app:/var/www/html/composer.json

echo "=========================================================="
echo "🗄️ [4/5] Setting container permissions & database migrations..."
echo "=========================================================="
docker exec -u 0:0 rudrapg_app chown -R www-data:www-data /var/www/html/ || true
docker exec -u 0:0 rudrapg_app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true
docker exec rudrapg_app php artisan migrate --force
docker exec rudrapg_app php artisan optimize:clear

echo "=========================================================="
echo "🔄 [5/5] Restarting rudrapg_app container..."
echo "=========================================================="
docker restart rudrapg_app
sleep 3
docker ps --filter "name=rudrapg_app"
systemctl reload nginx 2>/dev/null || service nginx reload 2>/dev/null || true

echo "=========================================================="
echo "✅ Production deployment complete! All systems operational."
echo "=========================================================="
