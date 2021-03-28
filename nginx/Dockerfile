FROM nginx:1.19-alpine

RUN addgroup -g 1000 -S docker
RUN adduser -u 1000 -S -G docker docker

RUN mkdir -p /phpsocket
RUN touch /var/run/nginx.pid \
  && chown -Rf docker:docker \
  /var/run/nginx.pid \
  /var/cache/nginx \
  /var/log/nginx \
  /phpsocket

USER docker
