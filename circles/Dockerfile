FROM node:14-alpine as builder

RUN set -ex && \
  apk update && \
  apk add --no-cache \
  python2 \
  make \
  g++ \
  git

RUN git clone https://github.com/kcesar/focused-search-grid.git fsg
WORKDIR /fsg
RUN npm install
RUN npm run prod

FROM nginx:1.19-alpine
COPY --from=builder /fsg/dist /usr/share/nginx/html

EXPOSE 80
