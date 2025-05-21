#!/bin/bash

LOG_FILE="storage/logs/scheduler.log"

echo "🚀 Laravel scheduler is now running..." | tee -a $LOG_FILE

while true; do
    echo "[`date '+%Y-%m-%d %H:%M:%S'`] Running schedule:run..." | tee -a $LOG_FILE
    php artisan schedule:run >> $LOG_FILE 2>&1
    sleep 60
done
