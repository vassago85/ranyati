#!/bin/sh
set -e

echo "Starting Ranyati Group..."

# Remove the baked-in .env so Laravel reads from Docker environment variables
rm -f /var/www/html/.env

mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/bootstrap/cache

echo "Waiting for database..."
max_tries=30
count=0
until nc -z db 3306 2>/dev/null; do
    count=$((count + 1))
    if [ $count -ge $max_tries ]; then
        echo "Database not reachable after $max_tries attempts"
        exit 1
    fi
    echo "  Attempt $count/$max_tries..."
    sleep 2
done
sleep 3
echo "Database is ready"

echo "Running migrations..."
php artisan migrate --seed --force || echo "Migration had issues, continuing..."

echo "Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear 2>/dev/null || true

php artisan storage:link 2>/dev/null || true

chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

echo "Ranyati Group is ready!"

exec /usr/bin/supervisord -c /etc/supervisord.conf
