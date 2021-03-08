set -ex
export $(grep -v '^#' ../secrets/backup.env | xargs)
restic --verbose=3 restore latest --target ../secrets --path "/etc/ssl/certs/private/terminal.space"
mv ../secrets/etc/ssl/certs/private/terminal.space/* ../secrets/certs/
rm -rf ../secrets/etc
