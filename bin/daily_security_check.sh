#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHP_BIN="${PHP_BIN:-php}"

if ! command -v "$PHP_BIN" >/dev/null 2>&1; then
  echo "php executable not found. Set PHP_BIN or install PHP." >&2
  exit 1
fi

exec "$PHP_BIN" "$SCRIPT_DIR/daily_security_check.php" "$@"