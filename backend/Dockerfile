FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    postgresql-dev \
    rabbitmq-c-dev \
    linux-headers \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    zip \
    bcmath \
    opcache

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Install AMQP extension for RabbitMQ
RUN pecl install amqp && docker-php-ext-enable amqp

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install dependencies (no dev for production, but we need dev for migrations)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Copy application code
COPY . .

# Run migrations
RUN php vendor/bin/doctrine-migrations migrate --no-interaction || true

# Create storage directory
RUN mkdir -p storage/images && chmod -R 775 storage

# Expose port 9000 for PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]