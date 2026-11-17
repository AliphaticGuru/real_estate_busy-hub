# ======================================
# Stage 1: base — build (compile) PHP extensions
# ======================================
FROM php:8.5-fpm-alpine AS base

# Switch to a mirror for faster downloads; if the mirror is unreachable or
# serves broken packages, fall back to the official Alpine CDN.
RUN set -eux; \
    build_deps="$PHPIZE_DEPS \
        libzip-dev libpng-dev libjpeg-turbo-dev libwebp-dev freetype-dev \
        icu-dev postgresql-dev"; \
    sed -i 's|dl-cdn.alpinelinux.org|mirrors.aliyun.com|g' /etc/apk/repositories; \
    if ! apk add --no-cache --virtual .build-deps $build_deps; then \
        sed -i 's|mirrors.aliyun.com|dl-cdn.alpinelinux.org|g' /etc/apk/repositories; \
        apk add --no-cache --virtual .build-deps $build_deps; \
    fi; \
    docker-php-ext-configure zip; \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
    docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pdo_mysql \
        bcmath \
        zip \
        intl \
        gd \
        pcntl; \
    apk del .build-deps; \
    rm -rf /var/cache/apk/* /tmp/*

# ======================================
# Stage 2: final — runtime image
# ======================================
FROM php:8.5-fpm-alpine AS final

# Runtime libraries only (no -dev packages): keeps the final image small.
RUN set -eux; \
    runtime_deps="libpq libzip libpng libjpeg-turbo libwebp freetype icu-libs bash"; \
    sed -i 's|dl-cdn.alpinelinux.org|mirrors.aliyun.com|g' /etc/apk/repositories; \
    if ! apk add --no-cache $runtime_deps; then \
        sed -i 's|mirrors.aliyun.com|dl-cdn.alpinelinux.org|g' /etc/apk/repositories; \
        apk add --no-cache $runtime_deps; \
    fi; \
    rm -rf /var/cache/apk/* /tmp/*

# Copy the PHP extensions compiled in the base stage
COPY --from=base /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=base /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

# Set working directory
WORKDIR /var/www/html

# NOTE: Application source (and vendor/) are intentionally NOT copied here.
# In development they come from the bind mount in docker-compose.yml; at
# deploy time the deployment platform copies the source in.

# Run as the unprivileged FPM user
USER www-data

EXPOSE 9000

# Verify PHP-FPM is running
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD ["sh", "-c", "pgrep php-fpm > /dev/null"]

CMD ["php-fpm"]
