.RECIPEPREFIX := >
.DEFAULT_GOAL := help
APP = docker compose exec app
RUN = docker compose run --rm app

help:
> @grep -E '^[a-z-]+:.*##' Makefile | awk -F':.*## ' '{printf "%-12s %s\n", $$1, $$2}'

env: ## salin .env.example ke .env bila belum ada
> @test -f .env || cp .env.example .env

up: env ## jalankan layanan
> docker compose up -d --build

down: ## hentikan layanan
> docker compose down

bootstrap: env ## PERTAMA KALI: buat skeleton Laravel 12 di folder ini
> docker compose up -d db
> $(RUN) sh -c "composer create-project laravel/laravel:^12.0 /tmp/laravel --no-interaction && cp -rn /tmp/laravel/. /var/www/html/"
> $(RUN) php artisan key:generate
> @echo "Selesai. Lanjut: make up && make migrate, lalu kerjakan T01 di docs/BACKLOG.md"

install: ## composer + npm install
> $(APP) composer install
> docker compose run --rm node sh -c "npm install"

dev: ## Vite dev server
> docker compose --profile dev up node

migrate: ## jalankan migration
> $(APP) php artisan migrate

fresh: ## migrate:fresh --seed
> $(APP) php artisan migrate:fresh --seed

test: ## Pest
> $(APP) env APP_ENV=testing php artisan test

lint: ## Pint (perbaiki)
> $(APP) vendor/bin/pint

stan: ## Larastan
> $(APP) vendor/bin/phpstan analyse --memory-limit=512M

check: lint stan test ## lint + stan + test

shell: ## shell container app
> $(APP) sh

logs: ## log layanan
> docker compose logs -f --tail=100

create-admin: ## buat akun admin (setelah T06)
> $(APP) php artisan npblog:create-admin
