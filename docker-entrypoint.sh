#!/bin/bash
set -e

# ── 0. Detect platform and set the correct port ────────────────────────────────
# Render injects RENDER_EXTERNAL_URL and uses port 10000
# Coolify / any other host uses port 80
if [ -n "$RENDER_EXTERNAL_URL" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
    APACHE_PORT=10000
    echo "==> Platform: Render — port $APACHE_PORT, APP_URL=$APP_URL"
else
    # Coolify or any other Docker host
    APACHE_PORT=${PORT:-80}
    echo "==> Platform: Coolify/VPS — port $APACHE_PORT"
fi

# Update Apache to listen on the correct port at runtime
sed -i "s/Listen 80/Listen $APACHE_PORT/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:$APACHE_PORT>/g" /etc/apache2/sites-available/000-default.conf
sed -i "s/Listen 10000/Listen $APACHE_PORT/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:10000>/<VirtualHost \*:$APACHE_PORT>/g" /etc/apache2/sites-available/000-default.conf

# ── 2. Ensure upload sub-directories exist inside the persistent disk ───────────
# The disk is mounted at /var/www/html/storage/app/public (see render.yaml).
# Only create directories — do NOT chown the whole disk on every boot (slow + risky).
echo "==> Ensuring upload directories exist on persistent disk..."
mkdir -p /var/www/html/storage/app/public/students/photos
mkdir -p /var/www/html/storage/app/public/students/backgrounds
mkdir -p /var/www/html/storage/app/public/uploads

# Only fix ownership if the directory is not already owned by www-data
# This avoids the slow recursive chown on every deploy
DISK_OWNER=$(stat -c '%U' /var/www/html/storage/app/public 2>/dev/null || echo "unknown")
if [ "$DISK_OWNER" != "www-data" ]; then
    echo "==> Fixing disk ownership (first boot or ownership changed)..."
    chown -R www-data:www-data /var/www/html/storage/app/public
fi
chmod -R 775 /var/www/html/storage/app/public

# ── 3. Ensure the rest of storage/ exists (ephemeral, recreated each boot) ─────
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# ── 4. Recreate public/storage and public/uploads symlinks on EVERY boot ───────
# public/ is part of the container image (ephemeral). The symlinks must be
# recreated each boot so URLs resolve to the persistent disk.
echo "==> Recreating public/storage symlink..."
rm -rf /var/www/html/public/storage 2>/dev/null || true
ln -sfn /var/www/html/storage/app/public /var/www/html/public/storage
chown -h www-data:www-data /var/www/html/public/storage
echo "    public/storage -> storage/app/public [persistent disk]"

echo "==> Recreating public/uploads symlink..."
rm -rf /var/www/html/public/uploads 2>/dev/null || true
ln -sfn /var/www/html/storage/app/public/uploads /var/www/html/public/uploads
chown -h www-data:www-data /var/www/html/public/uploads
echo "    public/uploads -> storage/app/public/uploads [persistent disk]"

# Verify symlink resolves correctly
if [ -L /var/www/html/public/storage ] && [ -d /var/www/html/public/storage ]; then
    echo "==> Symlink verified OK"
    PHOTO_COUNT=$(find /var/www/html/storage/app/public/students -type f 2>/dev/null | wc -l)
    echo "==> Student files on persistent disk: $PHOTO_COUNT"
else
    echo "WARNING: public/storage symlink may not be resolving correctly!"
fi

# ── 5. Wait for the database ────────────────────────────────────────────────────
echo "==> Waiting for database..."

# If DB_HOST is not set, skip the wait entirely
if [ -z "$DB_HOST" ]; then
    echo "==> DB_HOST not set — skipping database wait."
else
    MAX_TRIES=30
    COUNT=0
    until php -r "
        \$h = getenv('DB_HOST'); \$p = getenv('DB_PORT') ?: 5432;
        \$c = @fsockopen(\$h, \$p, \$e, \$s, 5);
        if (\$c) { fclose(\$c); exit(0); } exit(1);
    " 2>/dev/null; do
        COUNT=$((COUNT+1))
        if [ $COUNT -ge $MAX_TRIES ]; then
            echo "==> DB not ready after ${MAX_TRIES} attempts."
            echo "==> Check your DB_HOST env var on Render — make sure the database is linked correctly."
            echo "==> Current DB_HOST: $DB_HOST"
            exit 1
        fi
        echo "    attempt $COUNT/$MAX_TRIES..."
        sleep 3
    done
    echo "==> Database ready."
fi

# ── 6. Laravel bootstrap ────────────────────────────────────────────────────────
php artisan view:clear   || true
php artisan config:clear || true   # clear any stale cache before migrate/seed
php artisan migrate --force

echo "==> Seeding default system settings (idempotent)..."
php artisan db:seed --class=SettingsSeeder --force --no-interaction || true

echo "==> Provisioning initial admin accounts from environment variables (idempotent)..."
# IMPORTANT: seeders run BEFORE config:cache so env() reads live Render env vars
php artisan db:seed --class=InitialAdminSeeder --force --no-interaction || true

# Cache config AFTER seeding so env() works correctly during seeding
php artisan config:cache || true
php artisan route:clear  || true
php artisan route:cache  || true
php artisan view:cache   || true

echo "==> Bootstrap complete. Starting Apache."
exec apache2-foreground
