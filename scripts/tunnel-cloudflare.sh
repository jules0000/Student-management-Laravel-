#!/usr/bin/env bash
# Cloudflare quick tunnel to a local HTTP URL.
set -euo pipefail

LOCAL_URL="${LOCAL_URL:-http://127.0.0.1:8000}"

if ! command -v cloudflared >/dev/null 2>&1; then
  echo "cloudflared not found. Install: https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/install-and-setup/installation/" >&2
  exit 1
fi

echo "Quick tunnel -> ${LOCAL_URL}"
exec cloudflared tunnel --url "${LOCAL_URL}" "$@"
