.PHONY: help build up down restart logs shell artisan migrate seed fresh test lint format queue db redis npm cache-views octane-reload

COMPOSE = docker compose
APP = $(COMPOSE) exec app
NODE = $(COMPOSE) exec node
DB = $(COMPOSE) exec db
REDIS = $(COMPOSE) exec redis

help:
	@echo "Limit Order Exchange - Makefile Commands"
	@echo ""
	@echo "Docker Commands:"
	@echo "  make build         - Build Docker images"
	@echo "  make up            - Start all containers"
	@echo "  make down          - Stop all containers"
	@echo "  make restart       - Restart all containers"
	@echo "  make logs          - Tail container logs"
	@echo "  make logs-app      - Tail app container logs"
	@echo ""
	@echo "Laravel Commands:"
	@echo "  make shell         - Open shell in app container"
	@echo "  make artisan       - Run artisan command (make artisan cmd='migrate')"
	@echo "  make migrate       - Run database migrations"
	@echo "  make seed          - Run database seeders"
	@echo "  make fresh         - Drop all tables and re-run migrations + seeders"
	@echo "  make cache-clear   - Clear all Laravel caches + reload Octane"
	@echo "  make cache-views   - Clear compiled views only"
	@echo "  make octane-reload - Gracefully reload Octane workers"
	@echo "  make key-generate  - Generate application key"
	@echo ""
	@echo "Testing Commands:"
	@echo "  make test          - Run all tests"
	@echo "  make test-unit     - Run unit tests"
	@echo "  make test-int      - Run integration tests"
	@echo "  make test-feature  - Run feature tests"
	@echo "  make test-coverage - Run tests with coverage report"
	@echo ""
	@echo "Code Quality:"
	@echo "  make lint          - Run PHPStan static analysis"
	@echo "  make format        - Run Laravel Pint code formatter"
	@echo ""
	@echo "Frontend Commands:"
	@echo "  make npm           - Run npm command (make npm cmd='install')"
	@echo "  make npm-dev       - Run Vite dev server"
	@echo "  make npm-build     - Build frontend assets"
	@echo ""
	@echo "Database & Services:"
	@echo "  make db-shell      - Open psql shell"
	@echo "  make redis-cli     - Open redis-cli shell"
	@echo "  make queue         - Run queue worker"
	@echo "  make schedule      - Run scheduler"

build:
	$(COMPOSE) build

up:
	$(COMPOSE) up -d

down:
	$(COMPOSE) down

restart: down up

logs:
	$(COMPOSE) logs -f

logs-app:
	$(COMPOSE) logs -f app

shell:
	$(APP) bash

artisan:
	$(APP) php artisan $(cmd)

migrate:
	$(APP) php artisan migrate

seed:
	$(APP) php artisan db:seed

fresh:
	$(APP) php artisan migrate:fresh --seed

cache-clear:
	@echo "🗑️  Clearing all Laravel caches..."
	$(APP) php artisan config:clear
	$(APP) php artisan route:clear
	$(APP) php artisan view:clear
	$(APP) php artisan cache:clear
	@echo "🔄 Reloading Octane workers..."
	$(COMPOSE) kill -s SIGUSR1 app 2>/dev/null || true
	@echo "✅ All caches cleared!"

cache-views:
	@echo "🗑️  Clearing compiled views only..."
	$(APP) php artisan view:clear
	@echo "✅ View cache cleared!"

octane-reload:
	@echo "🔄 Reloading Octane workers (graceful via SIGUSR1)..."
	$(COMPOSE) kill -s SIGUSR1 app 2>/dev/null || true
	@echo "✅ Octane workers reloaded!"

key-generate:
	$(APP) php artisan key:generate

test:
	$(APP) php artisan test

test-unit:
	$(APP) php artisan test --testsuite=Unit

test-int:
	$(APP) php artisan test --testsuite=Integration

test-feature:
	$(APP) php artisan test --testsuite=Feature

test-coverage:
	$(APP) php artisan test --coverage

lint:
	$(APP) ./vendor/bin/phpstan analyse

format:
	$(APP) ./vendor/bin/pint

npm:
	$(NODE) npm $(cmd)

npm-dev:
	$(NODE) npm run dev

npm-build:
	$(NODE) npm run build
	@echo "🗑️  Clearing view cache..."
	$(APP) php artisan view:clear
	@echo "🔄 Reloading Octane workers..."
	$(COMPOSE) kill -s SIGUSR1 app 2>/dev/null || true
	@echo "✅ Build complete! Refresh your browser."

db-shell:
	$(DB) psql -U exchange -d exchange

redis-cli:
	$(REDIS) redis-cli

queue:
	$(APP) php artisan queue:work --sleep=3 --tries=3 --max-time=3600

schedule:
	$(APP) php artisan schedule:work

install:
	@echo "🚀 Setting up Limit Order Exchange..."
	@if [ ! -f .env ]; then \
		echo "📋 Creating .env from .env.example..."; \
		cp .env.example .env; \
	else \
		echo "📋 .env already exists, skipping copy..."; \
	fi
	@echo "🐳 Building Docker images..."
	$(COMPOSE) build
	@echo "🐳 Starting containers..."
	$(COMPOSE) up -d
	@echo "⏳ Waiting for services to be healthy..."
	@sleep 5
	@if [ ! -d vendor ]; then \
		echo "📦 Installing Composer dependencies..."; \
		$(APP) composer install --no-interaction; \
	else \
		echo "📦 Composer dependencies already installed, skipping..."; \
	fi
	@echo "🔑 Generating application key..."
	$(APP) php artisan key:generate --force
	@echo "🗄️  Running database migrations..."
	$(APP) php artisan migrate --force
	@echo "🌱 Seeding database with sample data..."
	$(APP) php artisan db:seed --force
	@echo "📦 Installing npm dependencies..."
	$(NODE) npm install
	@echo "🔨 Building frontend assets..."
	$(NODE) npm run build
	@echo ""
	@echo "✅ Installation complete!"
	@echo ""
	@echo "🌐 Application: http://localhost:8000"
	@echo ""
	@echo "📧 Demo Users (password: password):"
	@echo "   - trader1@example.com"
	@echo "   - trader2@example.com"
	@echo "   - trader3@example.com"
	@echo "   - trader4@example.com"
	@echo ""
	@echo "🔧 Useful commands:"
	@echo "   make logs    - View container logs"
	@echo "   make shell   - Open app shell"
	@echo "   make fresh   - Reset database"
	@echo ""

