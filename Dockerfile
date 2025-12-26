FROM php:8.2-apache

# Enable rewrite
RUN a2enmod rewrite

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Set Laravel public folder
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf

# Copy project
COPY . /var/www/html

WORKDIR /var/www/html

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache
RUN php artisan migrate --force || true
RUN php artisan optimize:clear

EXPOSE 80

CMD ["apache2-foreground"]
