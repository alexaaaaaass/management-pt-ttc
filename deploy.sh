#!/usr/bin/env bash
set -e

echo "=========================================================="
echo "  DEPLOYMENT & OPTIMIZATION SCRIPT - ERP PT TTC"
echo "=========================================================="

# 1. Masuk ke mode maintenance sementara (maksimal beberapa detik)
php artisan down --refresh=5 || true

# 2. Tarik perubahan kode terbaru dari repository Git
echo "[1/7] Menarik update terbaru dari Git..."
git pull origin main

# 3. Install dependency PHP dengan autoloader teroptimasi (Production)
echo "[2/7] Mengoptimalkan Composer Autoloader (Production)..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 4. Jalankan migrasi database jika ada tabel/kolom baru
echo "[3/7] Menjalankan migrasi database..."
php artisan migrate --force

# 5. Bersihkan cache lama terlebih dahulu
echo "[4/7] Membersihkan cache lama..."
php artisan optimize:clear
php artisan filament:clear-cached-components

# 6. Pre-compile & Cache seluruh komponen Filament, Icons, Config, Route & View
echo "[5/7] Mengaktifkan Layer Pre-Compilation (Filament, Blade Icons, Route & Config)..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan filament:cache-components
php artisan icons:cache

# 7. Reset cache permission Spatie / Filament Shield & Restart Queue Worker
echo "[6/7] Menyegarkan cache Permission Shield & Queue Worker..."
php artisan permission:cache-reset || true
php artisan queue:restart || true

# 8. Buka kembali aplikasi dari mode maintenance
echo "[7/7] Mengaktifkan kembali aplikasi..."
php artisan up

echo "=========================================================="
echo "  DEPLOYMENT SELESAI! Aplikasi ERP PT TTC berjalan optimal."
echo "=========================================================="

