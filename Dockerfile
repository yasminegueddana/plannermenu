# Utiliser l'image officielle PHP avec Apache
FROM php:8.2-apache

# Installer les extensions PHP nécessaires à Laravel
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Activer mod_rewrite pour Laravel
RUN a2enmod rewrite

# Définir DocumentRoot vers public/
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

# Copier le projet dans /var/www/html
COPY . /var/www/html

# Définir le répertoire de travail
WORKDIR /var/www/html

# Installer Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Installer les dépendances Laravel
RUN composer install

# Donner les permissions à Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Exposer le port Apache
EXPOSE 80

# Démarrer Apache
CMD ["apache2-foreground"]
