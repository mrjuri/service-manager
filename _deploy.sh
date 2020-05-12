#!/bin/sh

#composer dump-autoload

php artisan cache:clear

php artisan config:clear
#php artisan config:cache

php artisan view:clear
#php artisan view:cache
