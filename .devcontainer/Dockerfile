FROM webdevops/php-nginx-dev:8.2

ENV WEB_DOCUMENT_ROOT=/workspace/public

RUN curl -sL https://deb.nodesource.com/setup_20.x | bash -
RUN apt-get install -y nodejs mariadb-client

RUN npm install -g yarn
