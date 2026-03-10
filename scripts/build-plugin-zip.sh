#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
PLUGIN_DIR="$ROOT_DIR/wp-persian-calendar-pro"
OUTPUT_ZIP="$ROOT_DIR/wp-persian-calendar-pro.zip"

if [[ ! -d "$PLUGIN_DIR" ]]; then
  echo "Plugin directory not found: $PLUGIN_DIR" >&2
  exit 1
fi

rm -f "$OUTPUT_ZIP"
cd "$ROOT_DIR"
zip -r "$OUTPUT_ZIP" "wp-persian-calendar-pro" -x "*.DS_Store" "*/.git/*" >/dev/null

echo "Created: $OUTPUT_ZIP"
