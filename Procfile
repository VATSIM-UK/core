nova: php artisan nova:install
nova_waiting_lists: cd nova-components/WaitingListsManager && composer install && cd ../../
release: php artisan postdeploy:heroku
web: vendor/bin/heroku-php-apache2 public/
