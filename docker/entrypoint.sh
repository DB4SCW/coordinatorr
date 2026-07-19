#!/usr/bin/env sh
set -eu

cd /var/www/html

mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    database

# Resolve sqlite path from environment and ensure file exists with writable permissions.
sqlite_db_value="${DB_DATABASE:-database.sqlite}"
case "$sqlite_db_value" in
    /*) sqlite_db_path="$sqlite_db_value" ;;
    *) sqlite_db_path="/var/www/html/database/$sqlite_db_value" ;;
esac

mkdir -p "$(dirname "$sqlite_db_path")"
if [ ! -f "$sqlite_db_path" ]; then
    touch "$sqlite_db_path"
fi

chown -R coordinatorr:www-data storage bootstrap/cache database
chmod -R ug+rwX storage bootstrap/cache database
chown coordinatorr:www-data "$sqlite_db_path"
chmod 664 "$sqlite_db_path"

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

# Remove stale cached manifests that may reference dev-only service providers.
rm -f \
    bootstrap/cache/packages.php \
    bootstrap/cache/services.php \
    bootstrap/cache/config.php \
    bootstrap/cache/events.php \
    bootstrap/cache/routes*.php

# Resolve APP_KEY from environment first, then from .env file.
app_key_value="${APP_KEY:-}"
if [ -z "$app_key_value" ] && [ -f .env ]; then
    app_key_value="$(awk -F= '/^APP_KEY=/{print substr($0,9); exit}' .env || true)"
fi

# Generate and persist a key when missing to avoid runtime boot failures.
if [ -z "$app_key_value" ]; then
    app_key_value="$(su -s /bin/sh coordinatorr -c "php artisan key:generate --show --no-interaction")"
    export APP_KEY="$app_key_value"

    if [ -f .env ]; then
        escaped_app_key="$(printf '%s' "$app_key_value" | sed 's/[\\/&]/\\\\&/g')"
        if grep -q '^APP_KEY=' .env; then
            sed -i "s/^APP_KEY=.*/APP_KEY=${escaped_app_key}/" .env
        else
            printf '\nAPP_KEY=%s\n' "$app_key_value" >> .env
        fi
    fi
else
    export APP_KEY="$app_key_value"
fi

su -s /bin/sh coordinatorr -c "php artisan migrate --force --no-interaction"

if [ ! -L public/storage ]; then
    su -s /bin/sh coordinatorr -c "php artisan storage:link --no-interaction"
fi

exec "$@"
