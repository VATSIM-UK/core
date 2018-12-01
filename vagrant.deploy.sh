#!/usr/bin/env bash

vagrant box add laravel/homestead
vagrant plugin install vagrant-hostsupdater
vagrant up
