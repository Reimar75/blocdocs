git reset --hard && git clean -f
git pull
npm install
npm run build
composer install --ignore-platform-req=ext-gmp
composer dump-autoload --optimize --classmap-authoritative
php bin/console doctrine:schema:update --force --complete
php bin/console cache:clear --env=dev