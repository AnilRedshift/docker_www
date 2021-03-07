echo "Running restic backup"
restic --verbose=3 backup /var/www/html/wp-content/updraft --exclude="log*"
# Purge anything older than a month
restic --verbose=3 forget --keep-within 1m
 