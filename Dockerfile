ARG BASE_IMAGE=felipetimds/contatos-apache-php-mysql:1.0
FROM ${BASE_IMAGE} AS aula07_base

FROM ubuntu:24.04

ENV DEBIAN_FRONTEND=noninteractive \
    DB_HOST=127.0.0.1 \
    DB_NAME=agenda \
    DB_USER=agenda_user \
    DB_PASSWORD=agenda123

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        apache2 \
        git \
        libapache2-mod-php \
        mysql-server \
        php \
        php-mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && rm -rf /tmp/app \
    && git clone https://github.com/felipedisessa/posads-docker.git /tmp/app \
    && mkdir -p /var/lib/mysql /run/mysqld /var/lock/apache2 /var/log/apache2 \
    && chown -R mysql:mysql /var/lib/mysql /run/mysqld \
    && chown -R www-data:www-data /var/lock/apache2 /var/log/apache2 \
    && rm -f /var/www/html/index.html \
    && cp /tmp/app/cadastro_contatos.php /var/www/html/cadastro_contatos.php \
    && cp /tmp/app/banco_contatos.sql /var/www/html/banco_contatos.sql \
    && rm -rf /tmp/app

COPY --from=aula07_base /usr/local/share/app-template/index.php /var/www/html/index.php
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html

EXPOSE 80 3306

VOLUME ["/var/lib/mysql"]

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
