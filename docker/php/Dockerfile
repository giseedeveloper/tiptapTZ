FROM node:20-alpine AS node_builder

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

FROM php:8.2-fpm-alpine

LABEL maintainer="TIPTAP Tanzania <dev@tiptapafrica.co.tz>"

RUN apk add --no-cache \
    git curl libpng-dev libxml2-dev libzip-dev zip unzip \
    oniguruma-dev icu-dev freetype-dev libjpeg-turbo-dev \
    bash shadow docker-cli openssh-client

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql mbstring exif pcntl bcmath gd zip xml dom intl opcache

RUN { \
    echo "opcache.enable=1"; \
    echo "opcache.memory_consumption=256"; \
    echo "opcache.max_accelerated_files=20000"; \
    echo "opcache.validate_timestamps=0"; \
} > /usr/local/etc/php/conf.d/opcache.ini

RUN { \
    echo "upload_max_filesize=300M"; \
    echo "post_max_size=300M"; \
    echo "memory_limit=512M"; \
    echo "max_execution_time=300"; \
} > /usr/local/etc/php/conf.d/custom.ini

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --optimize-autoloader

COPY . .

COPY --from=node_builder /app/public/build ./public/build

RUN composer run-script post-autoload-dump --no-interaction

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
