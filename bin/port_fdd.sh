#!/bin/sh

set -eu

SCRIPT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
REPO_ROOT=$(CDPATH= cd -- "$SCRIPT_DIR/.." && pwd)

usage() {
	cat <<'EOF'
Usage: ./bin/port_fdd.sh <target-directory>

Copy the FDD operating bundle into the target directory and initialize the
minimum document/spec structure required for FDD handoff.
EOF
}

if [ "$#" -ne 1 ]; then
	usage >&2
	exit 1
fi

TARGET_ROOT=$1

if [ -e "$TARGET_ROOT" ] && [ ! -d "$TARGET_ROOT" ]; then
	echo "Target exists but is not a directory: $TARGET_ROOT" >&2
	exit 1
fi

mkdir -p "$TARGET_ROOT"

copy_file() {
	REL_PATH=$1
	SRC_PATH="$REPO_ROOT/$REL_PATH"
	DEST_PATH="$TARGET_ROOT/$REL_PATH"

	if [ ! -f "$SRC_PATH" ]; then
		echo "Missing source file: $REL_PATH" >&2
		exit 1
	fi

	mkdir -p "$(dirname -- "$DEST_PATH")"
	cp "$SRC_PATH" "$DEST_PATH"
	echo "Copied $REL_PATH"
}

FILES_TO_COPY='document/flow.md
document/flow.llm.md
document/spec/prompts.md
document/guides/fdd_porting_guide.md
document/guides/idea_md_writing_guide.md
document/guides/idea_md_role_examples.md
.github/copilot-instructions.md
.github/prompts/fdd-focus.prompt.md
.github/prompts/fdd-sprint.prompt.md
.github/prompts/fdd-review.prompt.md
.github/prompts/fdd-retrospective.prompt.md
.github/prompts/fdd-flow-llm-align.prompt.md'

printf '%s
' "$FILES_TO_COPY" | while IFS= read -r REL_PATH; do
	[ -n "$REL_PATH" ] || continue
	copy_file "$REL_PATH"
done

mkdir -p "$TARGET_ROOT/document/spec"

CURRENT_SPEC_PATH="$TARGET_ROOT/document/spec/.current-spec.md"
if [ ! -f "$CURRENT_SPEC_PATH" ]; then
	cat > "$CURRENT_SPEC_PATH" <<'EOF'
# FDD Current Spec

active_spec: TODO_SET_WITH_FDD_FOCUS
spec_path: document/spec/TODO_SET_WITH_FDD_FOCUS
history_path: document/spec/TODO_SET_WITH_FDD_FOCUS/history.md
plan_path: document/spec/TODO_SET_WITH_FDD_FOCUS/plan.md
check_path: document/spec/TODO_SET_WITH_FDD_FOCUS/check.md
EOF
	echo "Initialized document/spec/.current-spec.md template"
fi

echo
echo "FDD bundle copied to: $TARGET_ROOT"
echo "Next steps:"
echo "1. Rewrite project-bound rules in .github/copilot-instructions.md and FDD prompts."
echo "2. Create a real feature folder under document/spec/<FeatureName>/."
echo "3. Update document/spec/.current-spec.md or run FDD Focus in the new project."