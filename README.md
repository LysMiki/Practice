ddev start
ddev composer install
ddev exec php bin/console doctrine:migrations:migrate --no-interaction
# Api ready to use
