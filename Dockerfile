# Tahap 1: Compile aset frontend (Vue 3 + Vite + Tailwind CSS)
FROM node:20-alpine AS frontend
WORKDIR /app
COPY . .
RUN npm ci && npm run build

# Tahap 2: Runtime PHP 8.3 & Laravel
FROM php:8.3-cli-alpine

# Pasang ekstensi PHP yang dibutuhkan Laravel, cURL, dan SQLite
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_sqlite sqlite3 curl mbstring xml bcmath zip

# Pasang Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Salin kode sumber dan hasil build frontend dari Tahap 1
COPY . .
COPY --from=frontend /app/public/build ./public/build

# Install dependensi PHP produksi
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Atur izin tulis folder storage & cache
RUN chmod -R 775 storage bootstrap/cache

# Inisialisasi file SQLite otomatis, jalankan migrasi, dan nyalakan server
CMD sh -c 'mkdir -p "$(dirname "${DB_DATABASE:-database/database.sqlite}")" && touch "${DB_DATABASE:-database/database.sqlite}" && php artisan optimize:clear && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}'