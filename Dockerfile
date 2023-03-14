FROM php:8.1-cli-alpine

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apk add --update linux-headers
RUN apk add --no-cache $PHPIZE_DEPS openssl-dev
RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN printf "zend_extension=xdebug\n[xdebug]\nxdebug.mode=develop,debug\nxdebug.client_host=host.docker.internal\nxdebug.start_with_request=yes\n" >> /usr/local/etc/php/conf.d/xdebug.ini

WORKDIR /app