FROM webdevops/php-nginx-dev:8.1

ENV WEB_DOCUMENT_ROOT=/workspace/public

RUN curl -sL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get install -y nodejs

RUN npm install -g yarn
