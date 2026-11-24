#!/usr/bin/env bash
# backup.sh — Daily backup for busy-hub production.
#
# Pipeline (same code path as the deploy pre-dump): pg_dump inside the db
# container -> gzip -> gpg encrypt (public key recipient) -> timestamped file in
# ./backups. Upload to the S3-compatible bucket when S3_BUCKET is set, with
# remote retention configurable. Local retention default: 7 days.

set -euo pipefail

BACKUP_DIR="${BACKUP_DIR:-./backups}"
COMPOSE_PROJECT_NAME="${COMPOSE_PROJECT_NAME:-busy-hub}"
PG_HOST="${PG_HOST:-127.0.0.1}"
PG_USER="${PG_USER:-busyhub}"
PG_DB="${PG_DB:-busy_hub}"
PG_PASS="${PG_PASS:-secret}"
GPG_RECIPIENT="${GPG_RECIPIENT:-}"
S3_BUCKET="${S3_BUCKET:-}"
S3_ENDPOINT="${S3_ENDPOINT:-}"
AWS_ACCESS_KEY_ID="${AWS_ACCESS_KEY_ID:-}"
AWS_SECRET_ACCESS_KEY="${AWS_SECRET_ACCESS_KEY:-}"
LOCAL_RETENTION_DAYS="${LOCAL_RETENTION_DAYS:-7}"

mkdir -p "$BACKUP_DIR"
DATE=$(date +%Y%m%d_%H%M%S)
DUMP="${BACKUP_DIR}/busy_hub_${DATE}.sql.gz"

echo "backup.sh: dumping database ${PG_DB} from ${PG_HOST} into ${DUMP}"
if ! docker compose -p "${COMPOSE_PROJECT_NAME}" exec -T db env PGUSER="$PG_USER" PGHOST="$PG_HOST" POSTGRES_DB="$PG_DB" sh -c 'pg_dump -U "$PGUSER" -d "$POSTGRES_DB"' | gzip > "$DUMP"; then
  echo "backup.sh: database dump failed" >&2
  exit 1
fi
chmod 600 "$DUMP"

if [ -n "$GPG_RECIPIENT" ]; then
  echo "backup.sh: encrypting ${DUMP} with recipient ${GPG_RECIPIENT}"
  gpg --batch --yes --trust-model always --output "${DUMP}.gpg" --encrypt --recipient "$GPG_RECIPIENT" "$DUMP"
  rm -f "$DUMP"
  DUMP="${DUMP}.gpg"
fi

if [ -n "$S3_BUCKET" ]; then
  echo "backup.sh: uploading ${DUMP} to s3:${S3_ENDPOINT}/${S3_BUCKET}"
  rclone copyto "$DUMP" "s3:${S3_ENDPOINT}/${S3_BUCKET}/busy_hub/${DUMP##*/}" \
    --s3-no-check-bucket
fi

find "$BACKUP_DIR" -name "busy_hub_*.sql.gz*" -mtime +"$LOCAL_RETENTION_DAYS" -delete

echo "backup.sh: backup completed: ${DUMP}"
