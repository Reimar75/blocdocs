git reset --hard && git clean -f
git pull
composer install --ignore-platform-req=ext-gmp
composer dump-autoload --optimize --classmap-authoritative
npm install
npm run build
php bin/console doctrine:schema:update --force --complete
php bin/console cache:clear --env=prod