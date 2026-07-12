FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    default-mysql-client \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Node.js 22
COPY --from=node:22-bookworm-slim /usr/local/ /usr/local/

WORKDIR /var/www/html

# Copy the Laravel application
COPY . .

# Install PHP dependencies
RUN if [ -f composer.lock ]; then \
        composer install \
            --no-dev \
            --prefer-dist \
            --optimize-autoloader \
            --no-interaction; \
    fi

# Install frontend dependencies and build assets
RUN if [ -f package.json ]; then \
        npm install && npm run build; \
    fi

# Prepare Laravel directories
RUN mkdir -p storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Cache Laravel configuration (safe only if APP_KEY exists)
RUN php artisan config:cache || true \
    && php artisan route:cache || true \
    && php artisan view:cache || true

EXPOSE 9000

CMD ["php-fpm"]