up:
	docker-compose build
	docker-compose up -d

stop:
	docker-compose stop

ps:
	docker-compose ps

rm:
	docker rm $(docker ps -aq)

rmi:
	docker rmi $(docker ps -aq)

setup:
	docker-compose exec php php /var/www/symfony/bin/console doctrine:database:drop --force --if-exists
	docker-compose exec php php /var/www/symfony/bin/console doctrine:database:create --if-not-exists
	docker-compose exec php php /var/www/symfony/bin/console doctrine:migrations:migrate

php:
	docker-compose exec php bash

cc:
	docker-compose exec php php /var/www/symfony/bin/console cache:clear --env=prod --no-debug

cc_dev:
	docker-compose exec php php /var/www/symfony/bin/console cache:clear --env=dev --no-debug

pg:
	docker-compose exec postgres psql -h localhost  -p 5432 -U postgres