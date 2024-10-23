up:
	docker compose up -d
	docker compose exec php-fpm composer install --no-dev --optimize-autoloader --no-scripts --no-progress --no-interaction

down:
	docker compose down

bash:
	docker compose exec -it php-fpm bash

build:
	docker compose build --no-cache

restart:
	docker compose down && docker compose up -d

logs:
	docker compose logs -f

phpcs:
	docker compose exec -it php-fpm bash -c "composer phpcs"

phpcbf:
	docker compose exec -it php-fpm bash -c "composer phpcbf"
