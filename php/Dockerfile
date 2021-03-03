FROM php:7.4.15-fpm-alpine

RUN set -ex && \
  apk add --update --no-cache \
  libzip-dev \
  libxml2-dev \
  libpng-dev \
  libjpeg-turbo-dev

RUN set -ex && \
  docker-php-ext-install \
  zip \
  gd \
  soap \
  pdo_mysql \
  mysqli \
  bcmath

RUN addgroup -g 1000 -S docker
RUN adduser -u 1000 -S -G docker docker

RUN mkdir -p /phpsocket
RUN chown -Rf docker:docker \
  /phpsocket

USER docker

CMD ["php-fpm", "--nodaemonize"]
