#!/usr/bin/env bash
# Production deploy script for busy-hub (single-server Docker Compose setup).
# Invoked over SSH from .github/workflows/deploy.yml as the dedicated non-root
# deploy user. Usage: deploy.sh <new-sha>
#
# Sequence: record previous SHA -> pg_dump pre-deploy -> docker compose pull ->
# one-off migrate --force -> config/route/view cache -> restart queue -> up -d
# -> wait for health -> smoke check /up and /. On failure: restore the previous
# image for both app and caddy and fail.
set -euo pipefail

NEW_SHA="${1:-}"
APP_DIR="${APP_DIR:-/var/www/busy-hub}"
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod.yml}"
ENV_FILE="${ENV_FILE:-.env}"
PREV_FILE="${APP_DIR}/deploy-state/previous-sha"
BACKUP_DIR="${APP_DIR}/backups"
APP_URL="${APP_URL:-https://localhost}"

if [ -z "$NEW_SHA" ]; then
  echo "deploy.sh: usage: deploy.sh <new-sha>" >&2
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
pg_dump_file="${BACKUP_DIR}/pre-deploy-${NEW_SHA}-$(date -u +%Y%m%dT%H%M%SZ).sql"
run_or_rollback docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" exec -T db sh -c 'pg_dump -U "$POSTGRES_USER" -d "$POSTGRES_DB"' > "$pg_dump_file"
run_or_rollback docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" run --rm app php artisan migrate --force
run_or_rollback docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" run --rm app php artisan config:cache
run_or_rollback docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" run --rm app php artisan route:cache
run_or_rollback docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" run --rm app php artisan view:cache
run_or_rollback docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" restart queue
run_or_rollback docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d
run_or_rollback docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d --wait
run_or_rollback curl -fsS "${APP_URL}/up" -o /dev/null
run_or_rollback curl -fsS "${APP_URL}/" -o /dev/null

echo "deploy.sh: success ${NEW_SHA}"
