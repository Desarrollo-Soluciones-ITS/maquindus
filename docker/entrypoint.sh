#!/bin/bash
set -e

cd /var/www/html

echo "▶ Limpiando configuración cacheada..."
php artisan config:clear

echo "▶ Optimizando configuración..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan filament:optimize

echo "▶ Publicando assets de Livewire..."
php artisan livewire:publish --assets

echo "▶ Ejecutando migraciones pendientes..."
php artisan migrate --force

echo "▶ Enlazando storage..."
php artisan storage:link --force 2>/dev/null || true

echo "▶ Preparando base de datos SQLite para búsqueda..."
if [ ! -f "storage/app/maquindus/search-index.sqlite" ]; then
    mkdir -p storage/app/maquindus
    touch storage/app/maquindus/search-index.sqlite
    chown www-data:www-data storage/app/maquindus/search-index.sqlite
    php artisan search:index
fi

echo "✔ Bootstrap completo. Arrancando servicios..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf