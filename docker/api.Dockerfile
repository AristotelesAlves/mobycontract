FROM composer:2.8 AS dependencias_runtime

WORKDIR /var/www/html

COPY app/composer.json app/composer.lock* ./

RUN --mount=type=cache,target=/root/.cache/composer \
    composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --no-interaction \
        --prefer-dist \
        --no-progress

COPY app/ .

RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

FROM composer:2.8 AS dependencias_desenvolvimento

WORKDIR /var/www/html

COPY app/composer.json app/composer.lock* ./

RUN --mount=type=cache,target=/root/.cache/composer \
    composer install \
        --no-scripts \
        --no-autoloader \
        --no-interaction \
        --prefer-dist \
        --no-progress

COPY app/ .

RUN composer dump-autoload --optimize

FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache $PHPIZE_DEPS icu-dev libpq-dev postgresql-dev oniguruma-dev \
        libpng-dev libjpeg-turbo-dev libwebp-dev freetype-dev libzip-dev \
        postgresql-client \
        icu-libs libpq oniguruma libpng libjpeg-turbo libwebp freetype libzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        intl \
        opcache \
        pcntl \
        pdo_pgsql \
        zip \
        gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS icu-dev libpq-dev postgresql-dev oniguruma-dev \
        libpng-dev libjpeg-turbo-dev libwebp-dev freetype-dev libzip-dev

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-projeto.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/98-opcache.ini

RUN addgroup -g 1000 moby \
    && adduser -u 1000 -G moby -h /var/www/html -s /bin/sh -D moby

WORKDIR /var/www/html

COPY --chown=moby:moby app/ .
COPY docker/entrypoint.sh /usr/local/bin/moby-entrypoint.sh

RUN mkdir -p storage/app/public storage/framework/cache/data \
        storage/framework/sessions storage/framework/testing storage/framework/views \
        storage/logs bootstrap/cache \
    && chown -R moby:moby storage bootstrap/cache

USER moby

FROM base AS runtime

COPY --from=dependencias_runtime --chown=moby:moby /var/www/html/vendor ./vendor

ENTRYPOINT ["sh", "/usr/local/bin/moby-entrypoint.sh"]

EXPOSE 9000

CMD ["php-fpm", "--nodaemonize"]

FROM base AS desenvolvimento

COPY --from=dependencias_desenvolvimento --chown=moby:moby /var/www/html/vendor ./vendor

CMD ["php", "artisan", "list"]
