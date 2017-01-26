FROM php:7.0-fpm
MAINTAINER Michael Woodward <michael@wearejh.com>

ARG BUILD_ENV=dev
ENV PROD_ENV=prod

CMD ["php-fpm"]