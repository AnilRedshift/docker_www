FROM php:7.4.15-fpm-alpine

RUN addgroup -g 1000 -S docker
RUN adduser -u 1000 -S -G docker docker

RUN mkdir -p /phpsocket
RUN chown -Rf docker:docker \
  /phpsocket

USER docker

CMD ["php-fpm", "--nodaemonize"]
