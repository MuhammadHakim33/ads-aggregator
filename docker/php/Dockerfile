FROM php:7.4-fpm

# install ext php
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    zip \
    gd \
    && apt-get clean

# set working directory
WORKDIR /var/www/html