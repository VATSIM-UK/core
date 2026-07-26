.PHONY: dev docker npm build serve

DOCKER_COMPOSE=.devcontainer/load-dotenv.sh docker-compose -f .devcontainer/docker-compose.dev.yml

dev: docker npm build serve

docker:
	@echo "Ensuring Docker services are running..."
	@$(DOCKER_COMPOSE) up -d

npm:
	@echo "Installing npm dependencies..."
	@npm ci

build:
	@echo "Building frontend assets..."
	@composer install
	@npm run build

serve:
	@echo "Starting Laravel development server..."
	@php artisan serve --host=core.test --port=8080
