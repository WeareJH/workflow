FROM php:7.0-fpm
MAINTAINER Michael Woodward <michael@wearejh.com>

ARG BUILD_ENV=dev
ENV PROD_ENV=prod

RUN apt-get update \
  && apt-get install -y \
    cron \
    libfreetype6-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libpng12-dev \
    libxslt1-dev \
    gettext \
    msmtp \
    git \
    vim

RUN docker-php-ext-configure \
  gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/

RUN docker-php-ext-install \
    gd \
    intl \
    mbstring \
    mcrypt \
    pdo_mysql \
    xsl \
    zip \
    soap \
    bcmath \
    mysqli \
    opcache \
    pcntl

# Xdebug
RUN [ "$BUILD_ENV" != "$PROD_ENV" ] && pecl install -o -f xdebug-2.5.0; true

# Blackfire
RUN [ "$BUILD_ENV" != "$PROD_ENV" ] \
    && version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/amd64/$version \
    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp \
    && mv /tmp/blackfire-*.so $(php -r "echo ini_get('extension_dir');")/blackfire.so \
    && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707\n" > $PHP_INI_DIR/conf.d/blackfire.ini; \
    true

# Configuration files
COPY .docker/php/etc/custom.template .docker/php/etc/xdebug.template /usr/local/etc/php/conf.d/
COPY .docker/php/etc/msmtprc.template /etc/msmtprc.template

# Copy in Entrypoint file & Magento installation script
COPY .docker/php/bin/docker-configure .docker/php/bin/magento-install .docker/php/bin/magento-configure /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-configure /usr/local/bin/magento-install /usr/local/bin/magento-configure

# Composer
RUN  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www

RUN [ ! -d pub ] && mkdir pub
RUN [ ! -d var ] && mkdir var
RUN [ ! -d app/etc ] && mkdir -p app/etc

COPY composer.json composer.lock auth.json ./
COPY .docker/composer-cache .docker/composer-cache

RUN chsh -s /bin/bash www-data \
    && chown -R www-data:www-data ./

RUN [ "$BUILD_ENV" = "$PROD_ENV" ] \
    && su - www-data -c "COMPOSER_CACHE_DIR=.docker/composer-cache composer install --no-dev --no-interaction --prefer-dist -o" \
    || su - www-data -c "COMPOSER_CACHE_DIR=.docker/composer-cache composer install --no-interaction --prefer-dist -o"

COPY app app
COPY .data-migration .data-migration

RUN rm -rf \
    html \
    dev \
    phpserver \
    LICENSE*.txt \
    COPYING.txt \
    .user.ini \
    .travis.yml \
    .php_cs \
    .htaccess* \
    *.sample \
    .phpstorm.meta.php \
    *.md

RUN find . -user root | xargs chown www-data:www-data \
    && chmod +x bin/magento

VOLUME ["/var/www"]
ENTRYPOINT ["/usr/local/bin/docker-configure"]
CMD ["php-fpm"]