#!/bin/bash

LOGFILE="$(dirname "$0")/cronjob.log"
php artisan ical:sync >> "$LOGFILE" 2>&1

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Cronjob dijalankan" >> "$LOGFILE"

echo "Pulling latest code from origin/main..."
git pull origin main >> "$LOGFILE" 2>&1

if [ $? -eq 0 ]; then
    echo "Git pull sukses!" >> "$LOGFILE"
else
    echo "Git pull gagal!" >> "$LOGFILE"
fi
