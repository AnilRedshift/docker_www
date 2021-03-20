#!/bin/sh
/root/.acme.sh/acme.sh --issue --dns dns_gandi_livedns -d terminal.space -d *.terminal.space
ssh -i /nginx_ssh_rsa root@nginx_reverse_proxy 'nginx -s reload'
