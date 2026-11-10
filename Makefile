.PHONY: help up down build test lint migrate seed logs ps shell-backend shell-frontend shell-db

# Default target
help:
	@echo "Sherpa Product Import - Available commands:"
	@echo ""
	@echo "  make up              - Start all services (development)"
	@echo "  make down            - Stop all services"
	@echo "  make build           - Build all Docker images"
	@echo "  make test            - Run tests"
	@echo "  make lint            - Run linters"
	@echo "  make migrate         - Run database migrations"
	@echo "  make seed            - Seed database with test data"
	@echo "  make logs            - Show logs from all services"
	@echo "  make ps              - Show running containers"
	@echo "  make shell-backend   - Open shell in backend container"
	@echo "  make shell-frontend  - Open shell in frontend container"
	@echo "  make shell-db        - Open psql in database container"
	@echo "  make clean           - Remove all containers, volumes, and images"
	@echo ""

# Development
up:
	docker-compose up -d

down:
	docker-compose down

build:
	docker-compose build

# Logs
logs:
	docker-compose logs -f

logs-backend:
	docker-compose logs -f backend

logs-frontend:
	docker-compose logs -f frontend

logs-db:
	docker-compose logs -f db

logs-rabbitmq:
	docker-compose logs -f rabbitmq

# Status
ps:
	docker-compose ps

# Shell access
shell-backend:
	docker-compose exec backend sh

shell-frontend:
	docker-compose exec frontend sh

shell-db:
	docker-compose exec db psql -U ${DATABASE_USER:-sherpa} -d ${DATABASE_NAME:-sherpa}

shell-rabbitmq:
	docker-compose exec rabbitmq sh

# Database
migrate:
	docker-compose exec backend php vendor/bin/doctrine-migrations migrate --no-interaction

migrate-diff:
	docker-compose exec backend php vendor/bin/doctrine-migrations diff

migrate-status:
	docker-compose exec backend php vendor/bin/doctrine-migrations status

seed:
	docker-compose exec backend php vendor/bin/doctrine-fixtures:load --no-interaction

# Testing
test:
	docker-compose exec backend php vendor/bin/phpunit

test-frontend:
	docker-compose exec frontend npm run test:ci

# Linting
lint:
	docker-compose exec backend php vendor/bin/phpstan analyse src --level=5
	docker-compose exec backend php vendor/bin/php-cs-fixer fix --dry-run --diff --allow-risky=yes
	docker-compose exec frontend npm run lint

lint-fix:
	docker-compose exec backend php vendor/bin/php-cs-fixer fix --allow-risky=yes

# Generate Swagger
swagger:
	docker-compose exec backend php vendor/bin/openapi src -o public/swagger.json

# Clean up
clean:
	docker-compose down -v --rmi all --remove-orphans

# Production deployment
prod-up:
	docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d

prod-down:
	docker-compose -f docker-compose.yml -f docker-compose.prod.yml down

# Backup database
backup-db:
	docker-compose exec db pg_dump -U ${DATABASE_USER:-sherpa} ${DATABASE_NAME:-sherpa} > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore database
restore-db:
	@read -p "Enter backup file path: " file; \
	docker-compose exec -T db psql -U ${DATABASE_USER:-sherpa} ${DATABASE_NAME:-sherpa} < $$file