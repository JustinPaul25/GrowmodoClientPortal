#!/bin/sh
echo Initializing API;

cd /var/www;

/bin/sh ./_setup.sh

echo API is Ready;

# Always leave this as the last one
php-fpm;
