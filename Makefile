.PHONY: help build up down restart logs shell artisan migrate seed fresh test lint format queue db redis npm cache-views octane-reload web3-setup web3-clone web3-build web3-logs kafka-topics kafka-logs kafka-ui-logs

COMPOSE = docker compose
APP = $(COMPOSE) exec app
NODE = $(COMPOSE) exec node
DB = $(COMPOSE) exec db
REDIS = $(COMPOSE) exec redis
KAFKA = $(COMPOSE) exec kafka
WEB3_REPO_URL = git@github.com:ikarolaborda/limit-order-exchange-web3-component.git
WEB3_REPO_PATH = ../web3-service
KAFKA_TOPICS = logs.laravel logs.activities logs.web3-service

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
	@echo ""
	@echo "Web3 Service (Go):"
	@echo "  make web3-setup    - Clone and setup Go Web3 service (if not present)"
	@echo "  make web3-clone    - Clone the Web3 service repository"
	@echo "  make web3-build    - Build the Web3 service Docker image"
	@echo "  make web3-logs     - Tail Web3 service logs"
	@echo "  make ganache-logs  - Tail Ganache blockchain logs"
	@echo ""
	@echo "Kafka (Logging):"
	@echo "  make kafka-topics  - Create Kafka topics if they don't exist"
	@echo "  make kafka-logs    - Tail Kafka logs"
	@echo "  make kafka-ui-logs - Tail Kafka UI logs"
	@echo "  make kafka-list    - List all Kafka topics"
	@echo ""
	@echo "Kafka UI: http://localhost:8080"

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
	@echo "📋 Creating Kafka topics..."
	@$(MAKE) kafka-topics
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
	@echo "📊 Kafka UI (Logs): http://localhost:8080"
	@echo ""
	@echo "💡 For Web3/Ethereum features, run: make web3-setup"
	@echo ""

web3-clone:
	@echo "📥 Cloning Web3 service repository..."
	@if [ -d "$(WEB3_REPO_PATH)" ]; then \
		echo "⚠️  Directory $(WEB3_REPO_PATH) already exists."; \
		echo "   Remove it first or use 'make web3-build' to rebuild."; \
		exit 1; \
	fi
	@echo ""
	@echo "🔐 This repository requires SSH access to GitHub."
	@echo "   Make sure you have your SSH key configured."
	@echo ""
	@read -p "Press Enter to continue or Ctrl+C to cancel..." dummy
	git clone $(WEB3_REPO_URL) $(WEB3_REPO_PATH)
	@echo "✅ Repository cloned successfully!"

web3-build:
	@echo "🔨 Building Web3 service..."
	@if [ ! -d "$(WEB3_REPO_PATH)" ]; then \
		echo "❌ Web3 service not found at $(WEB3_REPO_PATH)"; \
		echo "   Run 'make web3-setup' first."; \
		exit 1; \
	fi
	$(COMPOSE) build web3-service
	@echo "✅ Web3 service built successfully!"

web3-setup:
	@echo "🚀 Setting up Web3 service..."
	@echo ""
	@if [ -d "$(WEB3_REPO_PATH)" ]; then \
		echo "✅ Web3 service already exists at $(WEB3_REPO_PATH)"; \
	else \
		echo "📥 Web3 service not found. Cloning repository..."; \
		echo ""; \
		echo "🔐 This requires SSH access to GitHub."; \
		echo "   Repository: $(WEB3_REPO_URL)"; \
		echo ""; \
		echo "   If you don't have SSH access, you can:"; \
		echo "   1. Use HTTPS: git clone https://github.com/ikarolaborda/limit-order-exchange-web3-component.git $(WEB3_REPO_PATH)"; \
		echo "   2. Or set up SSH keys: https://docs.github.com/en/authentication/connecting-to-github-with-ssh"; \
		echo ""; \
		read -p "Press Enter to clone via SSH or Ctrl+C to cancel..." dummy; \
		git clone $(WEB3_REPO_URL) $(WEB3_REPO_PATH); \
	fi
	@echo ""
	@echo "🐳 Building and starting Web3 services..."
	$(COMPOSE) up -d ganache web3-service
	@echo ""
	@echo "⏳ Waiting for services to start..."
	@sleep 5
	@echo ""
	@echo "✅ Web3 setup complete!"
	@echo ""
	@echo "🔗 Services:"
	@echo "   Ganache (Local Blockchain): http://localhost:8545"
	@echo "   Web3 Service API: http://localhost:8081 (internal)"
	@echo ""
	@echo "📖 API Documentation: http://localhost:8081/swagger/index.html"
	@echo ""

web3-logs:
	$(COMPOSE) logs -f web3-service

ganache-logs:
	$(COMPOSE) logs -f ganache

web3-restart:
	$(COMPOSE) restart web3-service ganache

kafka-topics:
	@echo "📋 Creating Kafka topics (if they don't exist)..."
	@for topic in $(KAFKA_TOPICS); do \
		if $(KAFKA) /opt/kafka/bin/kafka-topics.sh --bootstrap-server localhost:9092 --list 2>/dev/null | grep -q "^$$topic$$"; then \
			echo "   ✓ Topic '$$topic' already exists"; \
		else \
			echo "   ➕ Creating topic '$$topic'..."; \
			$(KAFKA) /opt/kafka/bin/kafka-topics.sh --bootstrap-server localhost:9092 --create --topic $$topic --partitions 1 --replication-factor 1 2>/dev/null || true; \
		fi \
	done
	@echo "✅ Kafka topics ready!"

kafka-list:
	@echo "📋 Kafka topics:"
	@$(KAFKA) /opt/kafka/bin/kafka-topics.sh --bootstrap-server localhost:9092 --list

kafka-logs:
	$(COMPOSE) logs -f kafka

kafka-ui-logs:
	$(COMPOSE) logs -f kafka-ui

