FROM mcr.microsoft.com/devcontainers/php:1-8.4-bookworm@sha256:5b7eb0fa180636ba5a610bb0757a202c95de7665bb7e45e9f7961ee331889dd8

COPY --from=mlocati/php-extension-installer@sha256:edda5f9d936fba9b495bdc96ecd19cc8fda7c2574f85db4574795644ae57b456 /usr/bin/install-php-extensions /usr/local/bin/

RUN rm -f /etc/apt/sources.list.d/yarn.list /etc/apt/sources.list.d/yarn.sources \
    && apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get -y install --no-install-recommends \
        default-mysql-client \
        make \
        unzip \
        libcap2-bin \
        supervisor \
        python3 \
        python3-venv \
    && rm -rf /var/lib/apt/lists/*

COPY apache/core.conf /etc/apache2/sites-available/core.conf
COPY supervisor/supervisord.conf /etc/supervisor/supervisord.conf
RUN a2enmod rewrite \
    && a2dissite 000-default \
    && a2ensite core

RUN install-php-extensions pdo_mysql zip gd intl bcmath pcntl posix exif sodium

RUN printf 'xdebug.mode = off\nxdebug.start_with_request = yes\nxdebug.client_port = 9003\n' \
    > /usr/local/etc/php/conf.d/zz-xdebug-overrides.ini

RUN setcap 'cap_net_bind_service=+ep' /usr/local/bin/php

RUN curl -LsSf https://github.com/j178/prek/releases/latest/download/prek-installer.sh | sh \
    && install -m 755 "$(find /root /usr/local -name prek -type f -perm -u+x | head -n 1)" /usr/local/bin/prek \
    && prek --version
