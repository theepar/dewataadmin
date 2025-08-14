#!/bin/bash

LOGFILE="$(dirname "$0")/cronjob.log"
php artisan ical:sync >> "$LOGFILE" 2>&1
