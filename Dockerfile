# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# base: compiled PHP extensions shared by the app runtime.
# ---------------------------------------------------------------------------
FROM php:8.5-fpm-alpine AS base

RUN set -eux; \
    build_deps="$PHPIZE_DEPS \
        libzip-dev libpng-dev libjpeg-turbo-dev libwebp-dev freetype-dev \
        icu-dev libpq-dev"; \
    sed -i 's|dl-cdn.alpinelinux.org|mirrors.aliyun.com|g' /etc/apk/repositories; \
    if ! apk add --no-cache --virtual .build-deps $build_deps; then \
        sed -i 's|mirrors.aliyun.com|dl-cdn.alpinelinux.org|g' /etc/apk/repositories; \
        apk add --no-cache --virtual .build-deps $build_deps; \
    fi

# One extension per layer: a failed compile only costs that extension, the
# already-built ones stay in the cache.
RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install bcmath
RUN docker-php-ext-configure zip && docker-php-ext-install zip
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd
# intl is the only C++ extension. Its Makefile can regenerate configure while
# `make -j install` is running, which fails with "cp: can't stat 'modules/*'",
# so build it serially.
RUN docker-php-ext-install -j1 intl
RUN docker-php-ext-install pcntl

# Runtime libraries only. The -dev packages above pulled these in as
# dependencies of the virtual .build-deps, so they must be world packages or
# `apk del .build-deps` removes them too and the .so files stop loading.
RUN apk add --no-cache libzip libpng libjpeg-turbo libwebp freetype icu-libs libpq

# opcache is already compiled into the official PHP image ("Zend OPcache"
# appears in `php -m`); only its ini tuning is added below.
RUN apk del .build-deps && rm -rf /var/cache/apk/* /tmp/*

# Fail the build, not production, if a runtime library went missing.
RUN php -r 'foreach (["pdo_pgsql", "bcmath", "zip", "intl", "gd", "pcntl"] as $ext) { if (! extension_loaded($ext)) { fwrite(STDERR, "missing PHP extension: $ext\n"); exit(1); } } echo "extensions ok\n";'

# ---------------------------------------------------------------------------
# vendor: production-only Composer dependencies (no dev packages).
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-progress

# ---------------------------------------------------------------------------
# assets: Vite build with Node 22. Only public/build is used by the runtimes.
# ---------------------------------------------------------------------------
FROM node:22-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# ---------------------------------------------------------------------------
# app: immutable PHP-FPM runtime running as a non-root user. No .env baked in.
# ---------------------------------------------------------------------------
FROM base AS app

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=vendor /app/bootstrap/cache ./bootstrap/cache
COPY --from=assets /app/public/build ./public/build

COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini

RUN mkdir -p storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD ["sh", "-c", "pgrep -f 'php-fpm: master' > /dev/null"]

CMD ["php-fpm", "--nodaemonize"]

# ---------------------------------------------------------------------------
# caddy: web tier with the built public/ files baked in (no shared volume).
# ---------------------------------------------------------------------------
FROM caddy:2-alpine AS caddy

COPY --from=assets /app/public /srv/public
COPY Caddyfile Caddyfile.local /etc/caddy/

EXPOSE 80 443

HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD ["sh", "-c", "wget -q -O /dev/null http://127.0.0.1:2019/config/"]
