#!/usr/bin/env sh
# Backup harian: database + storage. Retensi 7 harian / 4 mingguan. Jadwalkan via cron di VPS.
set -eu
STAMP=$(date +%Y%m%d-%H%M%S)
DEST="${BACKUP_DIR:-./backups}"
mkdir -p "$DEST"
docker compose exec -T db sh -c 'mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' | gzip > "$DEST/db-$STAMP.sql.gz"
tar -czf "$DEST/storage-$STAMP.tar.gz" storage/app/public
find "$DEST" -name 'db-*.sql.gz' -mtime +7 -delete
find "$DEST" -name 'storage-*.tar.gz' -mtime +28 -delete
echo "Backup selesai: $STAMP (salin juga ke lokasi di luar VPS)"
