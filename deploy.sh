#!/bin/bash
# Saytın serverə köçürülməsi. cPanel → Git Version Control → "Deploy HEAD Commit" .cpanel.yml vasitəsilə
# bu skripti işə salır: deploy.sh /home/jetpark/public_html/
#
# Repozitoriyadakı fayllar sayt qovluğuna köçürülür, heç nə silinmir. Admin paneldə yaranan
# məlumatlar (storage/data, assets/uploads) git-də yoxdur, ona görə də toxunulmaz qalır.
set -euo pipefail

DEST="${1:-}"
DEST="${DEST%/}"
if [ -z "$DEST" ]; then
    echo "İstifadə: deploy.sh /home/USER/public_html" >&2
    exit 1
fi
SRC="$(cd "$(dirname "$0")" && pwd)"

# Sayta köçürülməyənlər.
SKIP=" .git .gitignore .gitattributes .cpanel.yml deploy.sh autodeploy.sh README.md .claude "

mkdir -p "$DEST"

# cPanel (MultiPHP) .htaccess-ə PHP versiyasını və php.ini parametrlərini yazır. Yeni .htaccess
# onları silməsin deyə köhnə fayldan götürüb yenisinin sonuna əlavə edirik.
cpanel_blocks=""
if [ -f "$DEST/.htaccess" ]; then
    cpanel_blocks="$(sed -nE '/^# (php -- )?BEGIN cPanel-generated/,/^# (php -- )?END cPanel-generated/p' "$DEST/.htaccess")"
fi

cd "$SRC"
shopt -s dotglob nullglob
for item in *; do
    case "$SKIP" in *" $item "*) continue ;; esac
    cp -R "$item" "$DEST/"
done

if [ -n "$cpanel_blocks" ]; then
    printf '\n%s\n' "$cpanel_blocks" >> "$DEST/.htaccess"
fi

mkdir -p "$DEST/storage/data" "$DEST/assets/uploads"
echo "Deploy OK: $(git rev-parse --short HEAD 2>/dev/null || echo '-') → $DEST"
