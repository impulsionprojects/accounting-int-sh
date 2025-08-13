#!/bin/bash

cd /app

# Install dependencies if vendor directory doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing dependencies..."
    composer update
fi

# Generate app key if not set
if [ ! -f ".env" ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Storage link
php artisan storage:link

echo "Clearing cache..."
php artisan optimize:clear

echo "Starting server..."
php artisan serve --host=0.0.0.0 --port=8181
