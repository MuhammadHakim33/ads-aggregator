FROM php:7.4-fpm

# Install dependencies for PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
# gd and zip are required by dompdf and phpspreadsheet
# mysqli is used as dbdriver in CodeIgniter database config
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install mysqli pdo_mysql zip

# Set the working directory
WORKDIR /var/www/html

# Copy the application code to the container
COPY . /var/www/html/

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP dependencies using Composer
# We allow superuser because we are running inside a docker container
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-interaction --no-dev --optimize-autoloader --no-scripts

# Change ownership of our applications to the www-data user
RUN chown -R www-data:www-data /var/www/html

# Expose port 9000 for PHP-FPM
EXPOSE 9000
