FROM serversideup/php:8.2-fpm-apache

# Set working directory
WORKDIR /var/www/html

# Copy application code into container
COPY --chown=www-data:www-data . /var/www/html

# Install Composer production dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Set permissions
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Environment defaults for production Laravel
ENV AUTORUN_ENABLED="true"
ENV AUTORUN_LARAVEL_MIGRATION="true"
ENV AUTORUN_LARAVEL_STORAGE_LINK="true"
ENV AUTORUN_LARAVEL_CONFIG_CACHE="true"
ENV AUTORUN_LARAVEL_ROUTE_CACHE="true"
ENV AUTORUN_LARAVEL_VIEW_CACHE="true"

EXPOSE 8080
