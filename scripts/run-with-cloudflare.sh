#!/usr/bin/env bash
# Start the app, then run a Cloudflare quick tunnel to LOCAL_URL. Ctrl+C stops both.
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

LOCAL_URL="${LOCAL_URL:-http://127.0.0.1:8000}"
RUN_COMMAND="${RUN_COMMAND:-php artisan serve}"

if ! command -v cloudflared >/dev/null 2>&1; then
  echo "cloudflared not found. Install: https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/install-and-setup/installation/" >&2
  exit 1
fi

(
  cd "${PROJECT_ROOT}"
  exec bash -lc "${RUN_COMMAND}"
) &
APP_PID=$!

cleanup() {
  if kill -0 "${APP_PID}" 2>/dev/null; then
    kill "${APP_PID}" 2>/dev/null || true
    wait "${APP_PID}" 2>/dev/null || true
  fi
}
trap cleanup EXIT INT TERM

echo "Started (cwd=${PROJECT_ROOT}): ${RUN_COMMAND}"
echo "Quick tunnel -> ${LOCAL_URL}"
echo "Press Ctrl+C to stop."
cloudflared tunnel --url "${LOCAL_URL}" "$@"
