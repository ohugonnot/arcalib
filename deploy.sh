git pull
php bin/console cache:clear
php bin/console cache:clear --env=prod
php bin/console fos:js-routing:dump
php bin/console fos:js-routing:dump --env=prod
chmod -R 777 ./var/cache
cd web
gulp build
cd ./..
