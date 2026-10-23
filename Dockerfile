FROM php:8.3-apache

# Install build tools, MongoDB driver prerequisites, git, unzip, and Node.js
RUN apt-get update && apt-get install -y \
    libssl-dev \
    pkg-config \
    git \
    unzip \
    curl \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure Apache directory settings for /var/www/html/
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
    && echo '<Directory /var/www/html/>\n    Options Indexes FollowSymLinks\n    AllowOverride All\n    Require all granted\n</Directory>' >> /etc/apache2/apache2.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files to web root
COPY . /var/www/html/
WORKDIR /var/www/html/

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Build Tailwind CSS inside container to guarantee compiled stylesheet
RUN npm install && npm run build:css

# Set ownership to Apache user
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
