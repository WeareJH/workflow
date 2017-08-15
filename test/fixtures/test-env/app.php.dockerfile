FROM php:7.0-fpm
MAINTAINER Michael Woodward <michael@wearejh.com>

WORKDIR /var/www
RUN chown www-data:www-data /var/www

CMD ["php-fpm"]

