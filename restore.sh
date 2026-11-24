#!/usr/bin/env bash
# restore.sh — Restore a busy-hub backup into a NEW disposable database.
#
# Usage: restore.sh --confirm
# Never restores into the live database unless the disposable target is used.
# A disposable target database is always created fresh (`busy_hub_restore_<ts>`)
# and dropped again afterwards, so the live database is never touched by default.

set -euo pipefail

CONFIRM="${1:-}"
BACKUP_DIR="${BACKUP_DIR:-./backups}"
COMPOSE_PROJECT_NAME="${COMPOSE_PROJECT_NAME:-busy-hub}"
PG_HOST="${PG_HOST:-127.0.0.1}"
PG_USER="${PG_USER:-busyhub}"
PG_DB="${PG_DB:-busy_hub}"
GPG_PRIVKEY="${GPG_PRIVKEY:-}"
LIVE_DB="${LIVE_DB:-busy_hub}"

if [ "$CONFIRM" != "--confirm" ]; then
  echo "restore.sh: usage: restore.sh --confirm   (refusing without --confirm)" >&2
  exit 2
fi

TARGET_DB="${RESTORE_DB:-busy_hub_restore_$(date +%Y%m%dT%H%M%SZ)}"

if [ "$TARGET_DB" = "$LIVE_DB" ]; then
  echo "restore.sh: refusing to restore into the live database ${TARGET_DB}; use a disposable database name" >&2
  exit 3
fi

LATEST="$(ls -t "$BACKUP_DIR"/busy_hub_* 2>/dev/null | head -n 1)"
if [ -z "$LATEST" ]; then
  echo "restore.sh: no backup found in ${BACKUP_DIR}" >&2
  exit 1
fi
DUMP="${LATEST}"
if [[ "$DUMP" == *.gpg ]]; then
  DECRYPTED="${DUMP%.gpg}"
  if ! gpg --batch --yes --decrypt --output "$DECRYPTED" --recipient-key "$GPG_PRIVKEY" "$DUMP"; then
    echo "restore.sh: decryption failed for ${DUMP}" >&2
    exit 1
  fi
  DUMP="$DECRYPTED"
fi

TARGET_DB="${RESTORE_DB:-busy_hub_restore_$(date +%Y%m%dT%H%M%SZ)}"

echo "restore.sh: restoring ${DUMP} into disposable database ${TARGET_DB}"
docker compose -p "${COMPOSE_PROJECT_NAME}" exec -T db createdb -U "$PG_USER" "$TARGET_DB"
docker compose -p "${COMPOSE_PROJECT_NAME}" exec -T db sh -c 'psql -U "$PG_USER" -d "$TARGET_DB" -v ON_ERROR_STOP=1 -f "$DUMP"'

echo "restore.sh: row counts in ${TARGET_DB}"
docker compose -p "${COMPOSE_PROJECT_NAME}" exec -T db psql -U "$PG_USER" -d "$TARGET_DB" -tAc \
  "select 'users=' || count(*) from users; select 'listings=' || count(*) from listings; select 'inquiries=' || count(*) from inquiries;"

docker compose -p "${COMPOSE_PROJECT_NAME}" exec -T db dropdb -U "$PG_USER" "$TARGET_DB"
echo "restore.sh: restored disposable database dropped: ${TARGET_DB}"
