FROM nginx:1.23-alpine
RUN set -ex && \
  apk update && \
  apk add --no-cache \
  git

RUN echo version 2
RUN git clone https://github.com/intentionally-left-nil/bread.git bread
RUN cp bread/index.html /usr/share/nginx/html/index.html

EXPOSE 80
