# Sherpa Product Import System

A full-stack product import/management system built with **PHP/Slim** (backend) and **Angular/Material** (frontend).

## Features

- **User Authentication** - JWT-based login/register with refresh tokens
- **Product Management** - CRUD operations, pagination, search, filtering
- **Excel Import** - Async XLSX parsing with image download, validation, and batch processing
- **Image Handling** - Automatic download, storage, and CDN-ready URLs
- **REST API** - Clean JSON API with CORS support
- **PostgreSQL** - Doctrine ORM with UUID entities

## Tech Stack

**Backend:**
- PHP 8.2+, Slim 4
- Doctrine ORM 3.x, DBAL
- PostgreSQL 16
- Symfony Messenger (sync processing)
- PhpSpreadsheet for XLSX parsing
- Firebase JWT for authentication

**Frontend:**
- Angular 18, Material Design
- NgRx for state management
- RxJS, TypeScript
- Proxy config for API calls

**DevOps:**
- Docker Compose for local development
- Nginx reverse proxy
- RabbitMQ for async jobs

## Quick Start

### Prerequisites
- Docker & Docker Compose
- Node.js 18+ (for frontend dev)
- PHP 8.2+ (for backend dev)

### Using Docker (Recommended)

```bash
# Start all services
docker-compose up -d

# Run migrations
docker-compose exec backend php vendor/bin/doctrine-migrations migrate --no-interaction

# Access:
# Frontend: http://localhost:4200
# Backend API: http://localhost:8081
# RabbitMQ Management: http://localhost:15672 (guest/guest)
```

### Local Development (WSL/Linux)

**Backend:**
```bash
cd backend
composer install
cp .env.example .env  # Edit with your DB credentials
php vendor/bin/doctrine-migrations migrate --no-interaction
php -S 0.0.0.0:8081 -t public
```

**Frontend:**
```bash
cd frontend
npm install
npm start
# Opens http://localhost:4200
```

## Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `DATABASE_HOST` | PostgreSQL host | localhost |
| `DATABASE_PORT` | PostgreSQL port | 5432 |
| `DATABASE_NAME` | Database name | sherpa |
| `DATABASE_USER` | DB username | sherpa |
| `DATABASE_PASSWORD` | DB password | sherpa_password |
| `JWT_SECRET` | JWT signing key | change-in-production |
| `JWT_EXPIRATION` | Token TTL (seconds) | 3600 |
| `FRONTEND_URL` | CORS origin | http://localhost:4200 |
| `IMAGE_STORAGE_PATH` | Local image storage | /var/www/html/storage/images |
| `RABBITMQ_HOST` | RabbitMQ host | rabbitmq |

## API Endpoints

### Auth
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login
- `POST /api/auth/refresh` - Refresh access token

### Products
- `GET /api/products` - List products (paginated, searchable)
- `GET /api/products/{id}` - Get product with relations
- `POST /api/products` - Create product
- `PUT /api/products/{id}` - Update product
- `DELETE /api/products/{id}` - Delete product

### Import
- `POST /api/import` - Upload XLSX file (multipart/form-data)
- `GET /api/import/{jobId}/status` - Check import status
- `GET /api/import/{jobId}/report` - Get import report

### Health
- `GET /api/health` - Health check

## Import Format

XLSX file with Russian headers (mapped automatically):
- UUID → external_code
- Наименование → name
- Цена: Цена продажи → price
- etc.

Images: comma-separated URLs in "Доп. поле: Ссылки на фото"

## Project Structure

```
sherpa/
├── backend/                 # PHP Slim API
│   ├── src/
│   │   ├── Controller/      # HTTP handlers
│   │   ├── Entity/          # Doctrine entities
│   │   ├── Repository/      # Data access
│   │   ├── Service/         # Business logic
│   │   ├── Middleware/      # CORS, JWT, Error handling
│   │   └── Validator/       # Input validation
│   ├── config/              # DI, routes, middleware
│   └── public/              # Front controller
├── frontend/                # Angular app
│   ├── src/
│   │   ├── app/
│   │   │   ├── components/  # UI components
│   │   │   ├── services/    # API services
│   │   │   ├── store/       # NgRx state
│   │   │   ├── guards/      # Auth guards
│   │   │   └── interceptors/# HTTP interceptors
│   │   └── styles.scss      # Global styles
│   └── proxy.conf.json      # Dev proxy config
├── docker-compose.yml       # Local dev stack
├── nginx/                   # Reverse proxy config
└── Makefile                 # Common commands
```

## Development Commands

```bash
# Backend
make migrate          # Run migrations
make test             # PHPUnit tests
make lint             # PHPStan + CS Fixer
make seed             # Load fixtures

# Frontend
cd frontend && npm run build    # Production build
cd frontend && npm run test     # Karma tests
cd frontend && npm run lint     # ESLint
```

## License

MIT