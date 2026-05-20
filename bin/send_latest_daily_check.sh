#!/usr/bin/env bash
set -euo pipefail 

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
PHP_BIN="${PHP_BIN:-php8.1}"
APP_ENV="${APP_ENV:-production}"
OUTPUT_DIR=""
RECIPIENT=""

print_usage() {
  cat <<'TXT'
Usage:
  bash bin/send_latest_daily_check.sh [--project-root=/path/to/repo] [--output-dir=/home/ubuntu/checkresult] [--to=you@example.com]

Env examples:
  DAILY_CHECK_OUTPUT_DIR=/home/ubuntu/checkresult
  DAILY_CHECK_RECIPIENT=ops@example.com

Options:
  --project-root   Repository root. Default: parent of /bin
  --output-dir     Directory containing daily check logs. Default: /home/ubuntu/checkresult
  --to             Mail recipient. Default: webmaster from F3 config
  --help           Show this help
TXT
}

for arg in "$@"; do
  case "$arg" in
    --project-root=*) PROJECT_ROOT="${arg#*=}" ;;
    --output-dir=*) OUTPUT_DIR="${arg#*=}" ;;
    --to=*) RECIPIENT="${arg#*=}" ;;
    --help|-h)
      print_usage
      exit 0
      ;;
    *)
      echo "Unsupported argument: $arg" >&2
      print_usage >&2
      exit 1
      ;;
  esac
done

ENV_FILE="${PROJECT_ROOT%/}/.env"

if [[ -f "$ENV_FILE" ]]; then
  set -a
  # shellcheck disable=SC1090
  source "$ENV_FILE"
  set +a
fi

OUTPUT_DIR="${OUTPUT_DIR:-${DAILY_CHECK_OUTPUT_DIR:-}}"
RECIPIENT="${RECIPIENT:-${DAILY_CHECK_RECIPIENT:-}}"

CLI_ENTRY="${PROJECT_ROOT%/}/www/cli/index.php"

if ! command -v "$PHP_BIN" >/dev/null 2>&1; then
  echo "php executable not found. Set PHP_BIN or install PHP." >&2
  exit 1
fi

if [[ ! -f "$CLI_ENTRY" ]]; then
  echo "CLI entry not found: $CLI_ENTRY" >&2
  exit 1
fi

if [[ -n "$OUTPUT_DIR" ]]; then
  export DAILY_CHECK_OUTPUT_DIR="$OUTPUT_DIR"
fi

if [[ -n "$RECIPIENT" ]]; then
  export DAILY_CHECK_RECIPIENT="$RECIPIENT"
fi

exec env APP_ENV="$APP_ENV" "$PHP_BIN" "$CLI_ENTRY" /send-latest-daily-check