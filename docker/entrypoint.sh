#!/bin/bash
set -e

cd /var/www/html

echo "▶ Preparando assets..."
php artisan filament:assets
php artisan vendor:publish --tag=livewire:config
php artisan vendor:publish --tag=livewire:assets --force

echo "▶ Optimizando configuración..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan filament:optimize

echo "▶ Ejecutando migraciones..."
php artisan migrate:fresh --force

echo "▶ Enlazando storage..."
php artisan storage:link --force 2>/dev/null || true

echo "✔ Bootstrap completo. Arrancando servicios..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf