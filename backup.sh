#!/usr/bin/env bash
# backup.sh — Daily backup for busy-hub production

set -euo pipefail

BACKUP_DIR="./backups"
DATE=$(date +%Y%m%d_%H%M%S)
PG_HOST="${PG_HOST:-127.0.0.1}"
PG_USER="${PG_USER:-busyhub}"
PG_DB="${PG_DB:-busy_hub}"
PG_PASS="${PG_PASS:-secret}"

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Database dump
pg_dump -h "$PG_HOST" -U "$PG_USER" -d "$PG_DB" | gzip > "$BACKUP_DIR/busy_hub_$DATE.sql.gz"

# Keep only the last 7 days of backups
find "$BACKUP_DIR" -name "busy_hub_*.sql.gz" -mtime +7 -delete

echo "Backup completed: $BACKUP_DIR/busy_hub_$DATE.sql.gz"