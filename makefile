.PHONY: dev docker npm build serve

DOCKER_COMPOSE=.devcontainer/load-dotenv.sh docker-compose -f .devcontainer/docker-compose.dev.yml
PORT ?= 80

dev: docker npm build serve

docker:
	@echo "Ensuring Docker services are running..."
	@$(DOCKER_COMPOSE) up -d

npm:
	@echo "Installing npm dependencies..."
	@npm ci

build:
	@echo "Installing Composer dependencies..."
	@composer install
	@echo "Building frontend assets..."
	@npm run build

serve:
	@echo "Starting Laravel development server on port $(PORT)..."
	@php artisan serve --host=core.test --port=$(PORT)
