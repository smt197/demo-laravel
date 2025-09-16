# Stage 1: Build assets and install Composer dependencies
FROM dunglas/frankenphp:latest-php8.3

RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    git \
    unzip \
    librabbitmq-dev \
    libpq-dev \
    supervisor

RUN install-php-extensions \
    gd \
    pcntl \
    opcache \
    pdo \
    pdo_mysql \
    redis \
        zip \
    intl 

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy the Laravel application files into the container.
COPY . .

# Copy configuration files
COPY ./supervisor/php.ini /usr/local/etc/php/
COPY ./supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Install PHP extensions
RUN pecl install xdebug

# Install Laravel dependencies using Composer.
RUN composer install

# Enable PHP extensions
RUN docker-php-ext-enable xdebug

# Set permissions for Laravel.
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80 443

# Start Supervisor.
CMD ["/usr/bin/supervisord", "-n", "-c",  "/supervisor/supervisord.conf"]