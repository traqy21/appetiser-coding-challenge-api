FROM php:7.4-apache

RUN apt-get update
RUN apt-get install zip unzip
RUN apt-get install -y libfreetype6-dev zlib1g-dev libpng-dev libjpeg62-turbo-dev
RUN docker-php-ext-install pdo_mysql mysqli
RUN docker-php-ext-install exif
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# install stripe-cli for testing locally
RUN apt-get update && apt-get install -y gnupg2
RUN apt-key adv --keyserver hkp://pool.sks-keyservers.net:80 --recv-keys 379CE192D401AB61
RUN echo "deb https://dl.bintray.com/stripe/stripe-cli-deb stable main" | tee -a /etc/apt/sources.list
RUN apt-get update && apt-get install stripe

# allow url rewrites so index.php is not required in urls
RUN a2enmod rewrite

# overwrite apache conf to change document root
COPY /docker/apache /etc/apache2/sites-enabled

# Copy the application
COPY . /var/www/html

# change permissions on files so the api can function
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data
RUN chown -R www-data:www-data /var/www/html/bootstrap
RUN chown -R www-data:www-data /var/www/html/storage

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install

EXPOSE 80 443