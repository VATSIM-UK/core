release: php artisan postdeploy:heroku && heroku config:set APP_URL=$(heroku info -s | grep web_url | cut -d= -f2)
web: vendor/bin/heroku-php-apache2 public/