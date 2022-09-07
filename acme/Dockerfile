FROM alpine:3.16
ARG LETS_ENCRYPT_EMAIL
ENV LETS_ENCRYPT_EMAIL $LETS_ENCRYPT_EMAIL
RUN apk --update add --no-cache openssl openssh socat curl

# Install acme.sh
RUN wget -O -  https://get.acme.sh | sh -s email=${LETS_ENCRYPT_EMAIL}


RUN mkdir -p /root/periodic
ADD ./renew.sh /root/periodic/renew.sh
ADD ./heartbeat.sh /root/periodic/heartbeat.sh
ADD ./create.sh /create.sh
RUN chmod +x /root/periodic/*
RUN touch /nginx_ssh_rsa && chmod 600 /nginx_ssh_rsa

# Hourly heartbeat
RUN echo '16  *  *  *  *  /root/periodic/heartbeat.sh' >> /etc/crontabs/root
# Update the certs monthly on the 8th of the month
RUN echo '18  8  8  *  *  /root/periodic/renew.sh' >> /etc/crontabs/root

CMD crond -l 2 -f
