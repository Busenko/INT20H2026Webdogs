FROM php:8.2-apache
RUN apt-get update && apt-get install -y libpng-dev zip unzip && docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html
WORKDIR /var/www/html/private
RUN composer install --no-dev --optimize-autoloader
WORKDIR /var/www/html/
EXPOSE 80