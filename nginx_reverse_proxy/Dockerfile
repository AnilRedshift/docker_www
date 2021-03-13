FROM nginx:1.19.8-alpine
RUN apk update
RUN apk add --no-cache openssl socat

# Set up global dhparam file
RUN openssl dhparam -dsaparam -out /etc/ssl/certs/dhparams.pem 4096
