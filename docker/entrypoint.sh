#!/bin/sh
set -eu

if [ "$1" = "php-fpm" ]; then
    if [ ! -f storage/oauth-private.key ] && [ ! -f storage/oauth-public.key ]; then
        php artisan passport:keys --no-interaction
    fi
    php artisan migrate --force --no-interaction
    php artisan auth:preparar --no-interaction
fi

exec docker-php-entrypoint "$@"
