#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
PHP_BIN="${PHP_BIN:-php8.1}"
APP_ENV="${APP_ENV:-production}"

to_env_key() {
  local option_key="$1"
  option_key="${option_key//-/_}"
  option_key="$(printf '%s' "$option_key" | tr '[:lower:]' '[:upper:]')"
  printf 'DAILY_SECURITY_CHECK_%s' "$option_key"
}

print_usage() {
  cat <<'TXT'
Usage:
  bash bin/daily_security_check.sh [--project-root=/path/to/repo] [--output-dir=/home/ubuntu/checkresult]

Options:
  --project-root   Repository root. Default: parent of /bin
  --output-dir     Directory for report files. Default: /home/ubuntu/checkresult
  --dry-run        Show which checks would run and print the report to STDOUT without writing files
  --only=1,4,7     Run only the specified check IDs
  --disk-min-gb    Warning threshold for free disk space in GB. Default: 10
  --disk-min-pct   Warning threshold for free disk percentage. Default: 15
  --mem-warn-pct   Warning threshold for memory usage percentage. Default: 85
  --cpu-warn-pct   Warning threshold for CPU usage percentage. Default: 85
  --help           Show this help
TXT
}

for arg in "$@"; do
  case "$arg" in
    --project-root=*) PROJECT_ROOT="${arg#*=}" ;;
    --help|-h)
      print_usage
      exit 0
      ;;
    --*=*)
      key="${arg%%=*}"
      value="${arg#*=}"
      key="${key#--}"
      env_key="$(to_env_key "$key")"
      export "$env_key=$value"
      ;;
    --*)
      key="${arg#--}"
      env_key="$(to_env_key "$key")"
      export "$env_key=1"
      ;;
    *)
      echo "Unsupported argument: $arg" >&2
      print_usage >&2
      exit 1
      ;;
  esac
done

export DAILY_SECURITY_CHECK_PROJECT_ROOT="$PROJECT_ROOT"
CLI_ENTRY="${PROJECT_ROOT%/}/www/cli/index.php"

if ! command -v "$PHP_BIN" >/dev/null 2>&1; then
  echo "php executable not found. Set PHP_BIN or install PHP." >&2
  exit 1
fi

if [[ ! -f "$CLI_ENTRY" ]]; then
  echo "CLI entry not found: $CLI_ENTRY" >&2
  exit 1
fi

exec env APP_ENV="$APP_ENV" "$PHP_BIN" "$CLI_ENTRY" /run-daily-security-check