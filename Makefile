cc:
	php bin/console cache:clear --env=prod --no-debug

cc_dev:
	php bin/console cache:clear --env=dev --no-debug

assets:
	php bin/console assets:install --symlink --relative

composer:
	composer update  --no-progress --profile --prefer-dist

test:
	php vendor/bin/phpunit --configuration phpunit.xml.dist

db_upd:
	php bin/console doctrine:schema:update --force

db_diff:
	php bin/console doctrine:schema:update --dump-sql

db_migration_blank:
	php bin/console doctrine:migrations:generate

db_migrate:
	php bin/console doctrine:migrations:migrate

clear_setup:
	php bin/console doctrine:database:drop --force --if-exists
	php bin/console doctrine:database:create --if-not-exists
	php bin/console doctrine:migrations:migrate -n