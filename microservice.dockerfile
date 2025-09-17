# User Management Microservice - Production Image
FROM dunglas/frankenphp:latest-php8.3

# Labels pour la métadonnée du conteneur
LABEL maintainer="microservice@example.com"
LABEL description="User Management Microservice"
LABEL version="1.0.0"

# Installer les extensions PHP nécessaires pour le microservice
RUN install-php-extensions \
   pdo_sqlite \
   pdo_mysql \
   mbstring \
   xml \
   zip \
   bcmath \
   gd \
   redis \
   opcache \
   pcntl

# Installer composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Installer curl pour health checks et supervisor pour la gestion des processus
RUN apt-get update && apt-get install -y \
    curl \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Définir le répertoire de travail
WORKDIR /app

# Copier les fichiers de configuration en premier pour optimiser le cache Docker
COPY composer.json composer.lock ./
COPY artisan ./

# Installer les dépendances PHP (cache Docker optimisé)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copier le reste de l'application
COPY . /app

# Supprimer les fichiers de développement non nécessaires pour la production
RUN rm -rf tests/ \
    && rm -rf node_modules/ \
    && rm -f package.json package-lock.json

# Configuration spécifique pour le microservice
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Créer les répertoires nécessaires et définir les permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# Copier la configuration supervisor
COPY supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copier et configurer le script de démarrage
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Exposer les ports pour l'API microservice
EXPOSE 80 443

# Health check pour vérifier que le microservice fonctionne
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/api/health || exit 1

# Utiliser le script de démarrage qui lance supervisor
CMD ["/usr/local/bin/docker-entrypoint.sh"]