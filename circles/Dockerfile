FROM node:19-alpine3.17 as builder

RUN set -ex && \
  apk update && \
  apk add --no-cache \
  # python3 \
  # make \
  # g++ \
  git

RUN git clone https://github.com/kcesar/focused-search-grid.git fsg
WORKDIR /fsg
RUN npm install
RUN npm run build

FROM nginx:1.23-alpine
COPY --from=builder /fsg/dist /usr/share/nginx/html

EXPOSE 80
