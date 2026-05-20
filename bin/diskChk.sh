#!/usr/bin/env bash
set -euo pipefail

# Crontab example (run once every hour):
# 0 * * * * /Users/trevor/bitbucket/docker-f3cms/bin/diskChk.sh >> /var/log/diskChk.log 2>&1

TARGET_PATH="${TARGET_PATH:-/}"
BACKUP_DIR="${BACKUP_DIR:-/backup}"
MIN_FREE_PERCENT="${MIN_FREE_PERCENT:-5}"

if ! [[ "$MIN_FREE_PERCENT" =~ ^[0-9]+$ ]]; then
	echo "MIN_FREE_PERCENT must be an integer." >&2
	exit 1
fi

if [[ ! -d "$BACKUP_DIR" ]]; then
	echo "Backup directory not found: $BACKUP_DIR" >&2
	exit 1
fi

get_mtime() {
	local file_path="$1"

	if stat -c %Y "$file_path" >/dev/null 2>&1; then
		stat -c %Y "$file_path"
		return
	fi

	stat -f %m "$file_path"
}

used_percent="$(df -P "$TARGET_PATH" | awk 'NR==2 {gsub(/%/, "", $5); print $5}')"

if ! [[ "$used_percent" =~ ^[0-9]+$ ]]; then
	echo "Failed to read disk usage for: $TARGET_PATH" >&2
	exit 1
fi

free_percent=$((100 - used_percent))

if (( free_percent >= MIN_FREE_PERCENT )); then
	echo "Disk free space is ${free_percent}%. No backup deletion required."
	exit 0
fi

shopt -s nullglob
oldest_file=""
oldest_mtime=""

for candidate in "$BACKUP_DIR"/*; do
	[[ -f "$candidate" ]] || continue

	candidate_mtime="$(get_mtime "$candidate")"

	if [[ -z "$oldest_file" ]] || (( candidate_mtime < oldest_mtime )); then
		oldest_file="$candidate"
		oldest_mtime="$candidate_mtime"
	fi
done

shopt -u nullglob

if [[ -z "$oldest_file" ]]; then
	echo "Disk free space is ${free_percent}%, but no backup files were found in $BACKUP_DIR." >&2
	exit 1
fi

rm -f -- "$oldest_file"
echo "Disk free space is ${free_percent}%. Deleted oldest backup file: $oldest_file"
