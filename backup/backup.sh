echo "Running restic backup"
restic --verbose=3 backup /var/www/html/wp-content/updraft --exclude="log*"
restic --verbose=3 backup /etc/ssl/certs/private/terminal.space
# Purge anything older than a month
restic --verbose=3 forget --keep-within 1m
 