FROM php:8.0-fpm-alpine

RUN set -ex && \
  apk add --update --no-cache \
  libzip-dev \
  libxml2-dev \
  libpng-dev \
  libjpeg-turbo-dev

RUN set -ex && \
  docker-php-ext-install \
  exif \
  zip \
  gd \
  soap \
  pdo_mysql \
  mysqli \
  bcmath

RUN set -ex && \
  wget -nv -O /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
  chmod +x /usr/local/bin/wp


RUN addgroup -g 1000 -S docker
RUN adduser -u 1000 -S -G docker docker

RUN mkdir -p /phpsocket
RUN chown -Rf docker:docker \
  /phpsocket

USER docker

CMD ["php-fpm", "--nodaemonize"]
