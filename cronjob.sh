#!/bin/bash

LOGFILE="$(dirname "$0")/cronjob.log"
php artisan schedule:work >> "$LOGFILE" 2>&1
