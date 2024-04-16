FROM php:8.2.13-fpm-alpine AS php

RUN apk add --update --no-cache autoconf g++ make git openssh rabbitmq-c-dev libtool libc6-compat libxml2-dev linux-headers icu-dev shadow

RUN docker-php-ext-install mysqli pdo pdo_mysql sockets soap
RUN pecl install redis amqp

RUN pecl install amqp redis \
    && docker-php-ext-enable amqp redis
RUN docker-php-ext-install opcache

ARG UID=1000
ARG GID=1000
RUN usermod  --uid "$UID" www-data
RUN groupmod --gid "$GID" www-data

COPY ./docker/php/docker-healthcheck /usr/local/bin/docker-healthcheck
COPY ./docker/php/ini/override.ini /usr/local/etc/php/conf.d/php.override.ini
COPY ./docker/php/ini/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY --from=composer:2.5.1 /usr/bin/composer /usr/local/bin/composer

RUN chmod +x /usr/local/bin/docker-healthcheck

COPY ./ /data
RUN chown -R www-data:www-data /data

STOPSIGNAL SIGQUIT
EXPOSE 9000
WORKDIR /data



FROM php AS development
RUN apk add --update --no-cache linux-headers \
    && pecl install xdebug && docker-php-ext-enable xdebug
ADD ./docker/php/ini/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

USER www-data
