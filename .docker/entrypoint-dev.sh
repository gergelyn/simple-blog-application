#!/bin/bash
set -e

echo "🚀 Starting Simple Blog Application Development Environment..."

# Wait for database to be ready
echo "⏳ Waiting for database connection..."
until php artisan migrate:status > /dev/null 2>&1; do
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
echo "📡 API endpoints available at: http://localhost:8080/api/posts"

# Start PHP-FPM
exec php-fpm 