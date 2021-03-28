FROM nginx:1.19-alpine

RUN set -ex && \
  apk update && \
  apk add --no-cache \
  openssl \
  socat \
  openssh

# Set up global dhparam file
RUN openssl dhparam -dsaparam -out /etc/ssl/certs/dhparams.pem 4096
RUN ssh-keygen -A

COPY run_sshd.sh /docker-entrypoint.d/run_sshd.sh
COPY sshd_config /etc/ssh/sshd_config
COPY authorized_keys /root/.ssh/authorized_keys
RUN chmod 700 /root/.ssh && chmod 600 /root/.ssh/authorized_keys
RUN echo 'root:root' | chpasswd

EXPOSE 22
EXPOSE 80
EXPOSE 443
