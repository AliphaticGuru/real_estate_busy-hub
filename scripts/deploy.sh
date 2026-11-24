#!/usr/bin/env bash
# Production deploy script for busy-hub (single-server Docker Compose setup).
# Invoked over SSH from .github/workflows/deploy.yml as the dedicated non-root
# deploy user. Usage: deploy.sh <new-sha>
#
# Sequence: record previous SHA -> pg_dump pre-deploy -> docker compose pull ->
# one-off migrate --force -> config/route/view cache -> restart queue -> up -d
# -> wait for health -> smoke check /up and / (retry up to 2 minutes with
# backoff) -> success. On any failure: restore the previous image for both app
# and caddy and fail.
set -euo pipefail

NEW_SHA="${1:-}"
APP_DIR="${APP_DIR:-/var/www/busy-hub}"
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod.yml}"
ENV_FILE="${ENV_FILE:-.env}"
PREV_FILE="${APP_DIR}/deploy-state/previous-sha"
BACKUP_DIR="${APP_DIR}/backups"
APP_URL="${APP_URL:-https://localhost}"
SMOKE_ATTEMPTS="${SMOKE_ATTEMPTS:-12}"
SMOKE_WAIT_SECONDS="${SMOKE_WAIT_SECONDS:-10}"
DISK_CHECK_BYTES="${DISK_CHECK_BYTES:-2147483648}"

if [ -z "$NEW_SHA" ]; then
  echo "deploy.sh: usage: deploy.sh <new-sha>" >&2
  exit 2
fi

if ! printf '%s' "$NEW_SHA" | grep -Eq '^[0-9a-f]{40}$'; then
  echo "deploy.sh: refusing invalid deploy SHA: '${NEW_SHA}' (expected a 40 character hex commit SHA)" >&2
  exit 2
fi

OLD_SHA="$(cat "$PREV_FILE" 2>/dev/null || true)"
mkdir -p "${APP_DIR}/deploy-state"
printf '%s\n' "$NEW_SHA" > "$PREV_FILE"

rollback() {
  if [ -n "$OLD_SHA" ]; then
    echo "deploy.sh: rollback: redeploying previous release ${OLD_SHA}" >&2
    IMAGE_TAG="$OLD_SHA" \
      docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d >/dev/null 2>&1 || true
  fi
}

fail() {
  rollback
  echo "deploy.sh: deploy of ${NEW_SHA} failed; previous release: ${OLD_SHA:-none}" >&2
  exit 1
}

run_or_rollback() {
  if ! "$@"; then
    fail
  fi
}

run_or_rollback docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" pull
run_or_rollback mkdir -p "$BACKUP_DIR"

# Refuse to run a pre-deploy dump when free disk space is below 2 GB.
AVAILABLE_BYTES="$(df -P "$BACKUP_DIR" | awk 'NR==2 {print $4}')"
if [ "$AVAILABLE_BYTES" -lt "$DISK_CHECK_BYTES" ]; then
  echo "deploy.sh: refusing pre-deploy dump: only ${AVAILABLE_BYTES} bytes free under ${BACKUP_DIR}, need ${DISK_CHECK_BYTES} (2 GB)" >&2
  exit 1
fi
pg_dump_file="${BACKUP_DIR}/pre-deploy-${NEW_SHA}-$(date -u +%Y%m%dT%H%M%SZ).sql.gz"
if ! docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" exec -T db sh -c 'pg_dump -U "$POSTGRES_USER" -d "$POSTGRES_DB"' | gzip > "$pg_dump_file"; then
  fail
fi
chmod 600 "$pg_dump_file"
ls -1t "${BACKUP_DIR}"/pre-deploy-*.sql.gz 2>/dev/null | tail -n +6 | xargs -r -I {} rm -f {} || true
run_or_rollback docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" run --rm app php artisan migrate --force
run_or_rollback docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" run --rm app php artisan config:cache
run_or_rollback docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" run --rm app php artisan route:cache
run_or_rollback docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" run --rm app php artisan view:cache
run_or_rollback docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" restart queue
run_or_rollback docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d
run_or_rollback docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d --wait

SMOKE_OK=0
attempt=1
while [ "$attempt" -le "$SMOKE_ATTEMPTS" ]; do
  if curl -fsS "${APP_URL}/up" -o /dev/null && curl -fsS "${APP_URL}/" -o /dev/null; then
    SMOKE_OK=1
    break
  fi
  sleep "$SMOKE_WAIT_SECONDS"
  attempt=$((attempt + 1))
done
if [ "$SMOKE_OK" -ne 1 ]; then
  fail
fi

echo "deploy.sh: success ${NEW_SHA}"
