#!/bin/bash
set -e

echo "🚀 Starting Simple Blog Application Development Environment..."

# Ensure proper permissions for Laravel storage directories
echo "🔧 Setting up storage permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create necessary directories if they don't exist
mkdir -p /var/www/html/storage/framework/{views,cache,sessions}
mkdir -p /var/www/html/storage/logs
chmod -R 775 /var/www/html/storage/framework /var/www/html/storage/logs

# Wait for database to be ready
echo "⏳ Waiting for database connection..."
until php artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1; do
    echo "Database not ready, waiting 2 seconds..."
    sleep 2
done

echo "✅ Database connection established"

# Run migrations
echo "🔄 Running migrations..."
php artisan migrate --force

# Seed database if not already seeded
echo "🌱 Checking if database needs seeding..."
if [ "$(php artisan tinker --execute="echo \App\Models\User::count();")" = "0" ]; then
    echo "🌱 Seeding database..."
    php artisan db:seed --force
    echo "✅ Database seeded successfully"
else
    echo "✅ Database already has data, skipping seeding"
fi

echo "🎉 Simple Blog Application is ready!"
echo "📡 The API is available at: http://localhost:8080/api/"

# Start PHP-FPM
exec php-fpm 