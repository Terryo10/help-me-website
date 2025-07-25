#!/bin/bash

# Laravel Deployment Script
# This script ensures proper deployment and autoloader refresh

echo "Starting Laravel deployment..."

# Clear all caches
echo "Clearing application caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Refresh autoloader
echo "Refreshing composer autoloader..."
composer dump-autoload --optimize --no-dev

# Cache configurations for production
echo "Caching configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations if needed
echo "Running database migrations..."
php artisan migrate --force

# Link storage if needed
echo "Creating storage link..."
php artisan storage:link

# Set proper permissions
echo "Setting proper permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo "Deployment completed successfully!"
echo "EmailNotificationService should now be properly registered and available."
