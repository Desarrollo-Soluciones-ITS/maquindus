#!/bin/bash
set -e

cd /var/www/html

echo "▶ Optimizando configuración..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan filament:optimize

echo "▶ Ejecutando migraciones pendientes..."
php artisan migrate --force

echo "▶ Enlazando storage..."
php artisan storage:link --force 2>/dev/null || true

echo "✔ Bootstrap completo. Arrancando servicios..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf