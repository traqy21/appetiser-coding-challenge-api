#!/bin/sh
php artisan make:migration $1
chown -R 1000:1000 database/
echo "Migration created."