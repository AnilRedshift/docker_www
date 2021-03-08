set -ex
export $(grep -v '^#' ../secrets/backup.env | xargs)
restic --verbose=3 restore latest --target ../secrets --path "/var/www/html/wp-content/updraft"
mv ../secrets/var/www/html/wp-content/updraft/* ../www/html/wp-content/updraft
rm -rf ../secrets/var
