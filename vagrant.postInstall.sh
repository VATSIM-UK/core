#!/usr/bin/env bash
export PATH=/home/vagrant/.composer/vendor/bin:$PATH

cd ~/vukcore
composer install
yarn install
yarn run dev
./artisan key:generate
./artisan migrate
