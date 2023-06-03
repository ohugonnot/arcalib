git pull
php bin/console cache:clear
php bin/console cache:clear --env=prod
php bin/console fos:js-routing:dump
php bin/console fos:js-routing:dump -eprod
sed -ri "s/version:.*/version: $(date '+%s')/g" ./app/config/parameters.yml
cat ./app/config/parameters.yml | grep "version"
cd web
gulp build
cd ./..
chown -R arcalib:arcalib /home/arcalib/
chmod -R 777 ./var/
