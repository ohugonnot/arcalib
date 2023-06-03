sed -ri "s/version:.*/version: $(date '+%s')/g" ./app/config/parameters.yml
cat ./app/config/parameters.yml | grep "version"
php bin/console cache:clear
php bin/console cache:clear --env=prod
php bin/console fos:js-routing:dump
php bin/console fos:js-routing:dump --env=prod
cd web
gulp build
cd ./..
chown -R arcalib:arcalib ./
chmod -R 777 ./var/
chown -R www-data ./var/
