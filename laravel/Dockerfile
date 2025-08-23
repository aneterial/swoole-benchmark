# Стадия для установки зависимостей
FROM composer:2.8.5 AS composer-deps

WORKDIR /app
COPY laravel/composer.* ./
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --no-progress \
    --prefer-dist

# Основной образ
FROM unit:1.34.1-php8.4

ARG UID=1000
ARG GID=1000
ENV UID=${UID}
ENV GID=${GID}
ARG USER=laravel

# Create non root user
RUN addgroup --gid "$GID" "$USER" && adduser --uid "$UID" --gid "$GID" --shell /bin/bash --home /home/"$USER"/ "$USER"

# Install dependencies
RUN apt-get update && apt-get --no-install-recommends --no-install-suggests -y install \
    autoconf \
    curl \
    g++ \
    gifsicle \
    git \
    gnupg \
    jpegoptim \
    libc-ares-dev \
    libc-dev \
    libcurl4-openssl-dev \
    libfreetype6-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libmemcached-dev \
    libonig-dev \
    libpng-dev \
    libpq-dev \
    librdkafka-dev \
    libssl-dev \
    libwebp-dev \
    libxml2-dev \
    libxpm-dev \
    libzip-dev \
    openssl \
    optipng \
    pngquant \
    procps \
    unzip \
    wget \
    zip \
    && apt-get clean  \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* /var/www/html/* \
    && ln -sf /dev/stdout /var/log/unit.log

# Install PHP extensions
RUN pecl channel-update pecl.php.net \
    && pecl install redis \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis

# Configure and install extensions
RUN docker-php-ext-configure gd --with-webp \
    && docker-php-ext-configure intl \
    && docker-php-ext-install gd pdo_pgsql iconv zip mbstring bcmath exif pcntl opcache sockets intl

# Install Composer
COPY --from=composer:2.8.5 /usr/bin/composer /usr/bin/composer

# PHP configuration
COPY ./laravel/php.ini /usr/local/etc/php/conf.d/

# Create work directory
WORKDIR /app

# Nginx unit configuration
COPY ./laravel/config.json /docker-entrypoint.d/config.json

# Копируем зависимости из стадии composer-deps
COPY --from=composer-deps /app/vendor ./vendor
COPY --from=composer-deps /app/composer.lock ./composer.lock

# Copy application code
COPY laravel/ .
RUN composer dump-autoload --no-scripts --optimize --classmap-authoritative
RUN php artisan config:cache && php artisan route:cache

# Create necessary directories for Unit
RUN mkdir -p /tmp/state \
    && chown -R "$USER":"$USER" /tmp/state

# Change ownership to non-root user
RUN chown -R "$USER":"$USER" /app
