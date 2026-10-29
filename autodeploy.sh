#!/bin/bash
# Avtomatik deploy. cPanel → Cron Jobs hər 5 dəqiqədən bir işə salır:
#   /bin/bash /home/jetpark/repositories/jetpark.az/autodeploy.sh
# GitHub-da (origin/main) yeni commit varsa, repozitoriyanı yeniləyir və deploy.sh ilə sayta köçürür.
# Yenilik yoxdursa heç nə etmir və heç nə yazmır, ona görə cron e-poçt göndərmir.
# Nəticələr ~/jetpark-deploy.log faylına yazılır.

main() {
    set -uo pipefail
    local repo dest git target deployed
    local state="$HOME/.jetpark-deployed" log="$HOME/jetpark-deploy.log"
    repo="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)" || return 1
    # Sayt qovluğu .cpanel.yml-dən götürülür — əl ilə deploy ilə eyni yer.
    dest="${1:-$(sed -n 's/.*DEPLOYPATH=\([^[:space:]]*\).*/\1/p' "$repo/.cpanel.yml" | head -n 1)}"
    git="$(command -v git || echo /usr/local/cpanel/3rdparty/bin/git)"

    # Eyni anda iki deploy işləməsin.
    if command -v flock >/dev/null; then
        exec 9>"$HOME/.jetpark-deploy.lock"
        flock -n 9 || return 0
    fi

    cd "$repo" || return 1
    if ! "$git" fetch --quiet origin main 2>>"$log"; then
        echo "$(date '+%F %T') GitHub-dan yeniləmə alınmadı" >>"$log"
        return 1
    fi
    target="$("$git" rev-parse origin/main)" || return 1
    deployed="$(cat "$state" 2>/dev/null || true)"
    [ "$target" != "$deployed" ] || return 0

    # Son uğurlu deploy yalnız sonda qeyd olunur: alınmasa, növbəti dəfə yenidən cəhd olunur.
    if {
        echo "$(date '+%F %T') ${target:0:7}: deploy başlayır" &&
        "$git" reset --quiet --hard "$target" &&
        /bin/bash "$repo/deploy.sh" "$dest" &&
        echo "$target" >"$state"
    } >>"$log" 2>&1; then
        return 0
    fi
    echo "$(date '+%F %T') ${target:0:7}: deploy alınmadı" >>"$log"
    return 1
}

# main bütövlükdə oxunduqdan sonra işləyir — git reset bu faylı dəyişsə də, işləyən skriptə təsir etmir.
main "$@"
exit
