# Dockerfile for the local development environment.
# Builds a PHP 8.4 + Nginx image with the tools required to develop and run
# this application.

# PHP 8.4 development image with Nginx and common development tools.
FROM webdevops/php-nginx-dev:8.4

# Serve the application from its public directory.
ENV WEB_DOCUMENT_ROOT=/workspace/public

# Configure the NodeSource repository for Node.js 24.
RUN curl -sL https://deb.nodesource.com/setup_24.x | bash -

# Install Node.js and the MariaDB/MySQL command-line client.
RUN apt-get install -y nodejs mariadb-client

# Install globally available JavaScript development tools.
RUN npm install -g yarn @j178/prek
