FROM php:8.1-fpm-alpine

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

# Run wp-cron every 10 minutes. Note that cron doesnt actually run on the image
# So this works by overriding the entrypoint on a second container
# TODO: Downside is this runs as root, not as the docker user ()
RUN echo '*/10  *  *  *  *  cd /var/www/html; php /var/www/html/wp-cron.php' >> /etc/crontabs/root

USER docker

CMD ["php-fpm", "--nodaemonize"]
