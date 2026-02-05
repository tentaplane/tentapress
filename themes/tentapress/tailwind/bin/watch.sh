#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

cd "${ROOT_DIR}"

if command -v bun >/dev/null 2>&1; then
  bun install
  bun run watch
  exit 0
fi

if command -v npm >/dev/null 2>&1; then
  npm install
  npm run watch
  exit 0
fi

echo "Bun or npm is required to watch theme assets." >&2
exit 1
