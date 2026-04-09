#!/bin/bash
set -euo pipefail

WEB_ROOT="/var/www/html"
DB_DATA_DIR="/var/lib/mysql"
DB_READY_FILE="${DB_DATA_DIR}/.agenda_initialized"
DB_SOCKET="/var/run/mysqld/mysqld.sock"

mkdir -p "$WEB_ROOT" "$DB_DATA_DIR" /run/mysqld /var/lock/apache2 /var/log/apache2
chown -R www-data:www-data "$WEB_ROOT" /var/lock/apache2 /var/log/apache2
chown -R mysql:mysql "$DB_DATA_DIR" /run/mysqld

if [ ! -d "${DB_DATA_DIR}/mysql" ]; then
    mysqld --initialize-insecure --user=mysql --datadir="$DB_DATA_DIR"
fi

mysqld_safe --datadir="$DB_DATA_DIR" --socket="$DB_SOCKET" &

until mysqladmin --socket="$DB_SOCKET" ping --silent >/dev/null 2>&1; do
    sleep 2
done

if [ ! -f "$DB_READY_FILE" ]; then
    mysql --protocol=socket --socket="$DB_SOCKET" -uroot <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

    mysql --protocol=socket --socket="$DB_SOCKET" -uroot < "${WEB_ROOT}/banco_contatos.sql"
    touch "$DB_READY_FILE"
fi

exec apachectl -D FOREGROUND
