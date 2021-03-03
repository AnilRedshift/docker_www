FROM alpine:3.13
ARG LETS_ENCRYPT_EMAIL
ENV LETS_ENCRYPT_EMAIL $LETS_ENCRYPT_EMAIL
RUN apk update
RUN apk add --no-cache openssl socat

# Install acme.sh
RUN wget -O -  https://get.acme.sh | sh -s email=${LETS_ENCRYPT_EMAIL}
COPY ./create.sh /create.sh

CMD ["sleep", "infinity"]
