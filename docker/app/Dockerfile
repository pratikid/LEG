FROM php:8.4-fpm-alpine AS base

WORKDIR /var/www/html

# Install dependencies and PHP extensions
RUN apk update && apk upgrade && apk add --no-cache \
    autoconf \
    g++ \
    make \
    libtool \
    linux-headers \
    curl \
    git \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    oniguruma-dev \
    openssl-dev \
    pkgconfig \
    postgresql-dev \
    unzip \
    zip \
 && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip soap \
 && pecl install mongodb \
 && docker-php-ext-enable mongodb \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && apk del autoconf g++ make libtool linux-headers pkgconfig

# Enable OPcache
RUN docker-php-ext-install opcache \
 && echo 'opcache.enable=1' >> $PHP_INI_DIR/conf.d/opcache-recommended.ini \
 && echo 'opcache.memory_consumption=128' >> $PHP_INI_DIR/conf.d/opcache-recommended.ini \
 && echo 'opcache.interned_strings_buffer=8' >> $PHP_INI_DIR/conf.d/opcache-recommended.ini \
 && echo 'opcache.max_accelerated_files=10000' >> $PHP_INI_DIR/conf.d/opcache-recommended.ini \
 && echo 'opcache.revalidate_freq=1' >> $PHP_INI_DIR/conf.d/opcache-recommended.ini \
 && echo 'opcache.validate_timestamps=0' >> $PHP_INI_DIR/conf.d/opcache-recommended.ini

# Copy Composer from official image (multi-stage)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure PHP for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 && echo 'memory_limit = 512M' >> "$PHP_INI_DIR"/conf.d/docker-php-memlimit.ini \
 && echo 'upload_max_filesize = 64M' >> "$PHP_INI_DIR"/conf.d/docker-php-upload.ini \
 && echo 'post_max_size = 64M' >> "$PHP_INI_DIR"/conf.d/docker-php-upload.ini \
 && echo 'max_execution_time = 600' >> "$PHP_INI_DIR"/conf.d/docker-php-upload.ini \
 && adduser -D -u 1000 -h /home/dev dev \
 && mkdir -p /home/dev/.composer \
 && chown -R dev:dev /home/dev \
 && chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html/

# Copy custom PHP-FPM configuration
COPY ./docker/app/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

COPY ./docker/app/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
COPY docker/fix-permissions.sh /usr/local/bin/fix-permissions.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh \
 && chmod 755 /usr/local/bin/fix-permissions.sh

ENTRYPOINT ["/usr/local/bin/fix-permissions.sh"]
CMD ["php-fpm"] 