#!/bin/bash

# Script Deploy & Sinkronisasi SIPEGA di Server Hostinger / VPS
echo "============================================="
echo "  SINKRONISASI REPOSITORI & DATABASE SIPEGA  "
echo "============================================="

# 1. Tarik update terbaru dari GitHub
git pull origin main

# 2. Jalankan migrasi database
php artisan migrate --force

# 3. Bersihkan cache aplikasi & view
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "============================================="
echo "  UPDATE BERHASIL DIAPLIKASIKAN DI SERVER!  "
echo "============================================="
