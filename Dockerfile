FROM php:8.2-apache

# Install MongoDB driver prerequisites and enable Apache rewrite
RUN apt-get update && apt-get install -y libssl-dev pkg-config \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && a2enmod rewrite

# Allow .htaccess rewrites
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Copy application files to web root
COPY . /var/www/html/
WORKDIR /var/www/html/

EXPOSE 80