FROM php:7.2-apache

RUN apt-get update && apt-get install -y zip git libfreetype6-dev libjpeg62-turbo-dev libpng-dev vim libxml2-dev

COPY . /var/www/html
COPY .docker/php/vhost.conf /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /var/www/html
RUN a2enmod rewrite headers

RUN apt-get update && apt-get install -y libpng-dev
RUN apt-get install -y \
    libwebp-dev \
    libjpeg62-turbo-dev \
    libpng-dev libxpm-dev \
    libfreetype6-dev

RUN docker-php-ext-configure gd \
    --with-gd \
    --with-webp-dir \
    --with-jpeg-dir \
    --with-png-dir \
    --with-zlib-dir \
    --with-xpm-dir \
    --with-freetype-dir
#    --with-freetype-dir \
#    --enable-gd-native-ttf

RUN docker-php-ext-install -j$(nproc) gd
RUN docker-php-ext-install pdo_mysql mysqli
RUN docker-php-ext-install soap
RUN docker-php-ext-install zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN chown -R www-data:www-data /usr/local/bin/php

COPY .docker/php/php.ini /usr/local/etc/php/php.ini
RUN chmod u+x /usr/local/etc/php/php.ini

WORKDIR /var/www/html
RUN touch storage/logs/lumen.log && \
    chmod 777 storage/logs/lumen.log && \
    composer install --optimize-autoloader
