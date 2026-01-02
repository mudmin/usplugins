#!/usr/bin/env bash
set -euo pipefail

SRC="/var/www/html/usplugins/src"
DEST1="/var/www/html/plgdev/usersc/plugins"
DEST2="/var/www/html/usplugins/zip"

WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT

mkdir -p "$DEST1" "$DEST2"

PLUGINS=(
  mysql
  stripe
  payments
  webhooks
  steam_login
  spicebin
  refer
  store
  tasks
  messaging
  profile_pic
  uptime
  downloads
  cms
  cronpro
  chat
  notifications
  membership
  reports
  sendinblue
  game_show
  rememberme
  localhostlogin
  forum
  pushover
  facebook_login
  facebook_login_legacy
  apibuilder
  forms
  messages
)

REPORT_CSV="$DEST2/packaged_plugins_report.csv"
MISSING_JAN1="$DEST2/not_packaged_but_january1_release.txt"

# Extract <tag>...</tag> content from XML, even if it spans lines.
extract_tag() {
  local xml="$1"
  local tag="$2"
  [[ -f "$xml" ]] || { echo ""; return 0; }

  perl -0777 -ne "
    if (m{<\Q$tag\E>\s*(.*?)\s*</\Q$tag\E>}s) {
      \$v=\$1;
      \$v =~ s/\r?\n/ /g;
      \$v =~ s/\s+/ /g;
      print \$v;
    }
  " "$xml"
}

# Base64 of RAW sha256 bytes (matches PHP: base64_encode(hash_file('sha256', $file, true)))
sha256_b64() {
  local f="$1"
  # openssl is common; try it first.
  if command -v openssl >/dev/null 2>&1; then
    openssl dgst -sha256 -binary "$f" | openssl base64 -A
    return 0
  fi
  # fallback: python (almost always available)
  python3 - <<'PY' "$f"
import base64, hashlib, sys
p=sys.argv[1]
with open(p,'rb') as fh:
    h=hashlib.sha256(fh.read()).digest()
print(base64.b64encode(h).decode('ascii'))
PY
}

in_list() {
  local needle="$1"; shift
  local item
  for item in "$@"; do
    [[ "$item" == "$needle" ]] && return 0
  done
  return 1
}

echo "Packaging from: $SRC"
echo "Copying zips to:"
echo "  1) $DEST1"
echo "  2) $DEST2"
echo

# CSV header (added hash)
echo "folder,version,release,hash" > "$REPORT_CSV"

packaged_count=0
missing_count=0

for plugin in "${PLUGINS[@]}"; do
  plugin_dir="$SRC/$plugin"
  if [[ ! -d "$plugin_dir" ]]; then
    echo "WARN: folder not found, skipping: $plugin_dir"
    ((missing_count++)) || true
    continue
  fi

  zipfile="$WORK/${plugin}.zip"

  # Build zip from inside SRC so contents are plugin/...
  ( cd "$SRC" && zip -rq "$zipfile" "$plugin" )

  # Compute hash BEFORE copying/removing
  hash="$(sha256_b64 "$zipfile")"

  # Copy to both destinations
  cp -f "$zipfile" "$DEST1/${plugin}.zip"
  cp -f "$zipfile" "$DEST2/${plugin}.zip"

  # Remove working zip
  rm -f "$zipfile"

  # Pull version + release from info.xml
  info="$plugin_dir/info.xml"
  version="$(extract_tag "$info" "version" || true)"
  release="$(extract_tag "$info" "release" || true)"

  # CSV row
  echo "${plugin},${version},${release},${hash}" >> "$REPORT_CSV"

  echo "OK: ${plugin}.zip -> copied to both destinations"
  ((packaged_count++)) || true
done

echo
echo "Packaged: $packaged_count"
echo "Missing folders from list: $missing_count"
echo "Report CSV: $REPORT_CSV"
echo

# ---------- scan other folders for January 1 release ----------
: > "$MISSING_JAN1"
echo "Folders NOT packaged but with release containing 'January 1':" | tee -a "$MISSING_JAN1"
echo "----------------------------------------------------------" | tee -a "$MISSING_JAN1"

found=0
shopt -s nullglob
for dir in "$SRC"/*; do
  [[ -d "$dir" ]] || continue
  folder="$(basename "$dir")"

  # Skip ones we packaged
  if in_list "$folder" "${PLUGINS[@]}"; then
    continue
  fi

  info="$dir/info.xml"
  [[ -f "$info" ]] || continue

  rel="$(extract_tag "$info" "release" || true)"
  if [[ "$rel" == *"January 1"* ]]; then
    ver="$(extract_tag "$info" "version" || true)"
    printf "%s  (version: %s, release: %s)\n" "$folder" "${ver:-}" "${rel:-}" | tee -a "$MISSING_JAN1"
    found=1
  fi
done
shopt -u nullglob

if [[ "$found" -eq 0 ]]; then
  echo "(none found)" | tee -a "$MISSING_JAN1"
fi

echo
echo "Scan output: $MISSING_JAN1"
