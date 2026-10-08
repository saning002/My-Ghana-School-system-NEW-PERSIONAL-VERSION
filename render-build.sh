#!/usr/bin/env bash
set -e

echo "==> Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Caching config and routes (no DB queries in these)..."
php artisan config:cache
php artisan route:cache

echo "==> Clearing any stale view cache..."
php artisan view:clear

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Seeding default system settings (safe — never overwrites existing)..."
php artisan db:seed --class=SettingsSeeder --force

echo "==> Provisioning initial admin accounts from environment variables (idempotent — safe on every deploy)..."
php artisan db:seed --class=InitialAdminSeeder --force

echo "==> Setting storage permissions..."
chmod -R 775 storage bootstrap/cache

echo "==> Creating storage symlink..."
php artisan storage:link

echo "==> Build complete."
