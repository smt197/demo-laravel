# Stage 1: Build assets and install Composer dependencies
FROM dunglas/frankenphp:latest-php8.3 AS builder

# Install system dependencies for building assets and Composer packages
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
    zip \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js 22 (compatible avec Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

# Install PHP extensions required for Composer install and asset building
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
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

# Copy composer files and install dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy JS + Vite config first, then install npm deps
COPY package.json package-lock.json vite.config.js ./ 
RUN npm ci

# Copy the rest of the application code
COPY . .

# Build assets
RUN npm run build


# Set permissions for storage and bootstrap cache
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app/storage \
    && chmod -R 755 /app/bootstrap/cache


# Stage 2: Final production image
FROM dunglas/frankenphp:latest-php8.3

# Set working directory
WORKDIR /app

# Copy the Caddyfile
COPY --from=builder /app/Caddyfile /etc/caddy/Caddyfile

# Copy the application code from the builder stage
COPY --from=builder /app .

# Set proper permissions
RUN chown -R www-data:www-data /app

# Expose port 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:80/ || exit 1

# Start FrankenPHP
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]