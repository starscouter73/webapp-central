FROM php:8.2-apache

RUN a2enmod rewrite headers expires
RUN mkdir -p /tmp/php-sessions && chown -R www-data:www-data /tmp/php-sessions

COPY docker/php/dev.ini /usr/local/etc/php/conf.d/zz-dev.ini

WORKDIR /var/www/html
