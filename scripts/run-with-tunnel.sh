#!/usr/bin/env bash
# Start the app and keep an SSH tunnel open; stops the app when the tunnel exits.
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

TUNNEL_MODE="${TUNNEL_MODE:-Local}" # Local | Remote
LISTEN_PORT="${LISTEN_PORT:-8080}"
FORWARD_HOST="${FORWARD_HOST:-127.0.0.1}"
FORWARD_PORT="${FORWARD_PORT:-8000}"
SERVER_LISTEN_PORT="${SERVER_LISTEN_PORT:-8080}"
CLIENT_HOST="${CLIENT_HOST:-127.0.0.1}"
CLIENT_PORT="${CLIENT_PORT:-8000}"
RUN_COMMAND="${RUN_COMMAND:-php artisan serve}"
SSH_TARGET="${SSH_TARGET:-${1:-}}"

if [[ -z "${SSH_TARGET}" ]]; then
  echo "Usage: SSH_TARGET=user@host ./scripts/run-with-tunnel.sh" >&2
  echo "Or:    ./scripts/run-with-tunnel.sh user@host" >&2
  exit 1
fi

if ! command -v ssh >/dev/null 2>&1; then
  echo "ssh not found on PATH." >&2
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

if [[ "${TUNNEL_MODE}" == "Local" ]]; then
  SPEC="${LISTEN_PORT}:${FORWARD_HOST}:${FORWARD_PORT}"
  echo "SSH local forward: localhost:${LISTEN_PORT} -> ${FORWARD_HOST}:${FORWARD_PORT} (via ${SSH_TARGET})"
  ssh -N -L "${SPEC}" "${SSH_TARGET}"
else
  SPEC="${SERVER_LISTEN_PORT}:${CLIENT_HOST}:${CLIENT_PORT}"
  echo "SSH remote forward: server :${SERVER_LISTEN_PORT} -> ${CLIENT_HOST}:${CLIENT_PORT} (via ${SSH_TARGET})"
  ssh -N -R "${SPEC}" "${SSH_TARGET}"
fi
