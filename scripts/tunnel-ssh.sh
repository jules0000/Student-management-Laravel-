#!/usr/bin/env bash
# SSH port forwarding (local -L or remote -R).
set -euo pipefail

MODE="${MODE:-Local}"           # Local | Remote
LISTEN_PORT="${LISTEN_PORT:-8080}"
FORWARD_HOST="${FORWARD_HOST:-127.0.0.1}"
FORWARD_PORT="${FORWARD_PORT:-8000}"
SERVER_LISTEN_PORT="${SERVER_LISTEN_PORT:-8080}"
CLIENT_HOST="${CLIENT_HOST:-127.0.0.1}"
CLIENT_PORT="${CLIENT_PORT:-8000}"
SSH_TARGET="${SSH_TARGET:-}"

usage() {
  cat <<'EOF'
Usage:
  MODE=Local LISTEN_PORT=8080 FORWARD_HOST=127.0.0.1 FORWARD_PORT=8000 SSH_TARGET=user@host ./scripts/tunnel-ssh.sh
  MODE=Remote SERVER_LISTEN_PORT=8080 CLIENT_HOST=127.0.0.1 CLIENT_PORT=8000 SSH_TARGET=user@host ./scripts/tunnel-ssh.sh

Or pass SSH_TARGET as the first argument:
  ./scripts/tunnel-ssh.sh user@host
EOF
}

if [[ -z "${SSH_TARGET}" ]]; then
  SSH_TARGET="${1:-}"
fi
if [[ -z "${SSH_TARGET}" ]]; then
  usage
  exit 1
fi

if [[ "${MODE}" == "Local" ]]; then
  SPEC="${LISTEN_PORT}:${FORWARD_HOST}:${FORWARD_PORT}"
  echo "SSH local forward: localhost:${LISTEN_PORT} -> (via ${SSH_TARGET}) -> ${FORWARD_HOST}:${FORWARD_PORT}"
  exec ssh -N -L "${SPEC}" "${SSH_TARGET}"
else
  SPEC="${SERVER_LISTEN_PORT}:${CLIENT_HOST}:${CLIENT_PORT}"
  echo "SSH remote forward: server :${SERVER_LISTEN_PORT} -> ${CLIENT_HOST}:${CLIENT_PORT} on this machine"
  exec ssh -N -R "${SPEC}" "${SSH_TARGET}"
fi
