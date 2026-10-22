FROM php:8.2-apache

# Install dependencies, MongoDB C driver, and enable mod_rewrite
RUN apt-get update && apt-get install -y libssl-dev pkg-config \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && a2enmod rewrite

# Copy application files into Apache root
COPY . /var/www/html/
WORKDIR /var/www/html/

EXPOSE 80
