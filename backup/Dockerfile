FROM alpine:3.14
RUN apk update
RUN apk add --no-cache restic

RUN mkdir -p /root/periodic
ADD ./backup.sh /root/periodic/backup.sh
ADD ./heartbeat.sh /root/periodic/heartbeat.sh
RUN chmod +x /root/periodic/*
RUN echo '16  *  *  *  *  /root/periodic/heartbeat.sh' >> /etc/crontabs/root
RUN echo '0  23  *  *  *  /root/periodic/backup.sh' >> /etc/crontabs/root

CMD crond -l 2 -f
