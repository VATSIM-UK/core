# -----------------------------------------------------------------------------
# Dockerfile
# -----------------------------------------------------------------------------
# A Dockerfile is a text file that contains a list of instructions for building
# a Docker image.
#
# Think of a Docker image as a blueprint or template for creating a container.
# A container is a lightweight, isolated environment that contains everything
# an application needs to run (operating system libraries, programming language,
# tools, dependencies, etc.).
#
# Docker reads this file from top to bottom and executes each instruction in
# order. Each instruction creates a new layer in the final image.
# -----------------------------------------------------------------------------

# -----------------------------------------------------------------------------
# FROM
# -----------------------------------------------------------------------------
# Every Dockerfile starts with a FROM instruction.
#
# This tells Docker which existing image to use as the starting point.
#
# In this case, we are using the "webdevops/php-nginx-dev" image with PHP 8.4.
# This image already includes:
#   - PHP 8.4
#   - Nginx (the web server)
#   - Development tools (e.g. Git)
#   - A Linux operating system
#
# Instead of building everything from scratch, we build on top of this image.
# -----------------------------------------------------------------------------
FROM webdevops/php-nginx-dev:8.4

# -----------------------------------------------------------------------------
# ENV
# -----------------------------------------------------------------------------
# ENV creates an environment variable inside the image.
#
# Environment variables are values that applications can read while running.
#
# WEB_DOCUMENT_ROOT tells the web server where the website's files are located.
#
# In this project, the application will be served from:
#     /workspace/public
#
# This is similar to configuring the "DocumentRoot" in Apache or the "root"
# directive in Nginx.
# -----------------------------------------------------------------------------
ENV WEB_DOCUMENT_ROOT=/workspace/public

# -----------------------------------------------------------------------------
# RUN
# -----------------------------------------------------------------------------
# RUN executes a command inside the image while it is being built.
#
# The results of the command become part of the final image.
#
# Here we download and execute the official NodeSource installation script.
#
# curl:
#   Downloads content from a URL.
#
# -s:
#   Silent mode (hide progress output).
#
# -L:
#   Follow redirects if the URL forwards somewhere else.
#
# | bash:
#   Sends the downloaded script directly to the Bash shell to execute it.
#
# The purpose of this script is to configure the Linux package manager so it
# knows where to download Node.js version 20.
#
# Without this step, Ubuntu/Debian may install an older version of Node.js.
# -----------------------------------------------------------------------------
RUN curl -sL https://deb.nodesource.com/setup_24.x | bash -

# -----------------------------------------------------------------------------
# RUN
# -----------------------------------------------------------------------------
# Install additional software using apt-get, the package manager for Debian-
# based Linux distributions.
#
# apt-get install:
#   Downloads and installs software packages.
#
# -y:
#   Automatically answers "Yes" to installation prompts so the build can run
#   without waiting for user input.
#
# Packages installed:
#
# nodejs
#   - Installs Node.js.
#   - Node.js allows JavaScript to run outside of a browser.
#   - Many modern PHP applications use Node.js to build frontend assets.
#
# mariadb-client
#   - Installs the MariaDB/MySQL command-line client.
#   - Lets developers connect to MySQL or MariaDB databases from inside the
#     container.
#   - This installs only the client tools, NOT the database server itself.
# -----------------------------------------------------------------------------
RUN apt-get install -y nodejs mariadb-client

# -----------------------------------------------------------------------------
# RUN
# -----------------------------------------------------------------------------
# npm is the Node.js package manager.
#
# npm install:
#   Downloads and installs JavaScript packages.
#
# -g:
#   "Global" installation.
#   The package becomes available anywhere inside the container instead of only
#   within a single project.
#
# yarn:
#   An alternative package manager for JavaScript projects.
#   Many projects use Yarn instead of npm because it provides features such as
#   dependency locking and workspace support.
#
# After this command completes, the "yarn" command can be used from anywhere
# inside the container.
# -----------------------------------------------------------------------------
RUN npm install -g yarn @j178/prek
