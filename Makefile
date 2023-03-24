include .env
-include Makefile.local

default: up

install:
	make build

build:
	docker-compose up -d --build --force-recreate
up:
	docker-compose up -d
recreate:
	docker-compose up -d --build --force-recreate
stop:
	docker-compose stop
rm:
	docker-compose rm
logs:
	docker-compose logs

in:
	docker exec -it ${PHP} bash
indb:
	docker exec -it ${PHP}-db bash

export:
	docker exec ${PHP} mysqldump ${MYSQL_DATABASE} -hdb -u${MYSQL_USER} -p${MYSQL_PASSWORD} > db/latest.backup
import:
	docker cp *.sql ${PHP}-db:.
	docker exec -i ${PHP}-db bash -c 'cat *.sql | mysql ${MYSQL_DATABASE} -u${MYSQL_USER} -p${MYSQL_PASSWORD}'
	@make uli
	@make cr

composer:
	docker exec -it ${PHP} composer u
composeri:
	docker exec -it ${PHP} composer i

purge:
	docker-compose down --rmi all

# User login options
uli:
	@docker-compose exec -T drupal bash -c "drush uli -l localhost:${APACHE_PORT}"

# Drush commands
cr:
	docker-compose exec -T drupal bash -c "drush cr"
cim:
	docker-compose exec -T drupal bash -c "drush cim -y"
cex:
	docker-compose exec -T drupal bash -c "drush cex -y"
st:
	docker-compose exec -T drupal bash -c "drush st"
sql:
	docker-compose exec -T drupal bash -c "drush sqlc"
ws:
	docker-compose exec -T drupal bash -c "drush ws"
wd:
	docker-compose exec -T drupal bash -c "drush wd-del all --yes"
updb:
	docker-compose exec -T drupal bash -c "drush updb -y"
