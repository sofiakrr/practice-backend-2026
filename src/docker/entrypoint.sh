#!/bin/sh
set -eu

cd /var/www

if [ ! -f .env ]; then
  cp .env.example .env
fi

set_env_var() {
  key="$1"
  value="$2"
  escaped_value=$(printf '%s' "$value" | sed 's/[&|]/\\&/g')

  if grep -q "^${key}=" .env; then
    sed -i "s|^${key}=.*|${key}=${escaped_value}|" .env
  else
    printf '\n%s=%s\n' "$key" "$value" >> .env
  fi
}

if [ -n "${APP_ENV:-}" ]; then set_env_var "APP_ENV" "${APP_ENV}"; fi
if [ -n "${APP_DEBUG:-}" ]; then set_env_var "APP_DEBUG" "${APP_DEBUG}"; fi
if [ -n "${APP_URL:-}" ]; then set_env_var "APP_URL" "${APP_URL}"; fi
if [ -n "${APP_KEY:-}" ]; then set_env_var "APP_KEY" "${APP_KEY}"; fi
if [ -n "${JWT_SECRET:-}" ]; then set_env_var "JWT_SECRET" "${JWT_SECRET}"; fi
if [ -n "${DB_CONNECTION:-}" ]; then set_env_var "DB_CONNECTION" "${DB_CONNECTION}"; fi
if [ -n "${DB_HOST:-}" ]; then set_env_var "DB_HOST" "${DB_HOST}"; fi
if [ -n "${DB_PORT:-}" ]; then set_env_var "DB_PORT" "${DB_PORT}"; fi
if [ -n "${DB_DATABASE:-}" ]; then set_env_var "DB_DATABASE" "${DB_DATABASE}"; fi
if [ -n "${DB_USERNAME:-}" ]; then set_env_var "DB_USERNAME" "${DB_USERNAME}"; fi
if [ -n "${DB_PASSWORD:-}" ]; then set_env_var "DB_PASSWORD" "${DB_PASSWORD}"; fi
if [ -n "${CACHE_STORE:-}" ]; then set_env_var "CACHE_STORE" "${CACHE_STORE}"; fi
if [ -n "${SESSION_DRIVER:-}" ]; then set_env_var "SESSION_DRIVER" "${SESSION_DRIVER}"; fi
if [ -n "${QUEUE_CONNECTION:-}" ]; then set_env_var "QUEUE_CONNECTION" "${QUEUE_CONNECTION}"; fi

if ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --force
fi

if [ "${DB_CONNECTION:-}" = "mysql" ]; then
  echo "Waiting for MySQL ${DB_HOST:-db}:${DB_PORT:-3306}..."
  until php -r '
$host = getenv("DB_HOST") ?: "db";
$port = getenv("DB_PORT") ?: "3306";
$user = getenv("DB_USERNAME") ?: "root";
$pass = getenv("DB_PASSWORD") ?: "";
$db   = getenv("DB_DATABASE") ?: "laravel";
try {
    new PDO("mysql:host=".$host.";port=".$port.";dbname=".$db, $user, $pass);
    exit(0);
} catch (Throwable $e) {
    exit(1);
}
'; do
    sleep 2
  done
fi

php artisan migrate:fresh --seed --force

exec php artisan serve --host=0.0.0.0 --port=8000
