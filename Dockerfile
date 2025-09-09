FROM dunglas/frankenphp:latest-php8.3

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libzip-dev \
    libicu-dev \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure GD extension
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mysqli \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    soap

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configure OpCache for production
RUN echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=4000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=2" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.enable_cli=1" >> /usr/local/etc/php/conf.d/opcache.ini

# Set working directory
WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy package.json and package-lock.json
COPY package.json package-lock.json ./

# Install Node.js dependencies (including dev dependencies for build)
RUN npm ci

# Copy application code
COPY . .

# Set proper permissions for Laravel
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app/storage \
    && chmod -R 755 /app/bootstrap/cache

# Create storage directories if they don't exist
RUN mkdir -p /app/storage/logs \
    && mkdir -p /app/storage/framework/cache \
    && mkdir -p /app/storage/framework/sessions \
    && mkdir -p /app/storage/framework/views

# Build assets
RUN npm run build

# Remove dev dependencies after build to reduce image size
RUN npm ci --only=production && npm cache clean --force

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Create FrankenPHP configuration
COPY <<EOF /etc/caddy/Caddyfile
{
    frankenphp
}

:80 {
    root * /app/public
    
    # Enable FrankenPHP for PHP files
    php_server
    
    # Handle Laravel routes - try files first, then fallback to index.php
    try_files {path} {path}/ /index.php?{query}
    
    # Serve static files
    file_server
    
    # Security headers
    header {
        X-Content-Type-Options nosniff
        X-Frame-Options DENY
        X-XSS-Protection "1; mode=block"
        Strict-Transport-Security "max-age=31536000; includeSubDomains"
    }
    
    # Disable access to sensitive files
    @forbidden {
        path /.env*
        path /composer.json
        path /composer.lock
        path /package.json
        path /package-lock.json
        path /.git*
    }
    respond @forbidden 403
    
    # Handle PHP files explicitly
    @php {
        path *.php
        file {
            try_files {path} /index.php
        }
    }
    handle @php {
        php
    }
}
EOF

# Expose port 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Set entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]

# Start FrankenPHP
CMD ["frankenphp", "run"]