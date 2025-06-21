# Recipes API

A RESTful API for managing recipes, built with Laravel and Laravel Sanctum for authentication.

## Features

- **User Authentication**: Register, login, logout, and token management
- **Recipe Management**: CRUD operations for recipes with image upload
- **Categories**: Organize recipes by categories
- **Favorites**: Users can favorite/unfavorite recipes
- **Ratings**: Rate recipes from 1-5 stars
- **Search & Filtering**: Search recipes by name/description, filter by category and difficulty
- **API Documentation**: Complete OpenAPI/Swagger documentation

## API Documentation

The complete API documentation is available at:
- **Swagger UI**: `/api/documentation`
- **JSON Schema**: `/docs/api-docs.json`

## Quick Start

### Prerequisites

- PHP 8.1+
- Composer
- MySQL/PostgreSQL
- Laravel Sanctum

### Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```

3. Copy environment file:
   ```bash
   cp .env.example .env
   ```

4. Configure your database in `.env`

5. Run migrations:
   ```bash
   php artisan migrate
   ```

6. Generate application key:
   ```bash
   php artisan key:generate
   ```

7. Generate API documentation:
   ```bash
   php artisan l5-swagger:generate
   ```

8. Start the development server:
   ```bash
   php artisan serve
   ```

## API Endpoints

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Register a new user |
| POST | `/api/auth/login` | Login user |
| POST | `/api/auth/logout` | Logout user (requires auth) |
| GET | `/api/auth/user` | Get current user info (requires auth) |
| POST | `/api/auth/refresh` | Refresh token (requires auth) |

### Recipes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/recipes` | Get all recipes (with search/filter) |
| GET | `/api/recipes/{id}` | Get specific recipe |
| POST | `/api/recipes` | Create new recipe (requires auth) |
| PUT | `/api/recipes/{id}` | Update recipe (requires auth) |
| DELETE | `/api/recipes/{id}` | Delete recipe (requires auth) |

### Categories

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/categories` | Get all categories |
| POST | `/api/categories` | Create category (requires auth) |
| GET | `/api/categories/{id}` | Get specific category |
| PUT | `/api/categories/{id}` | Update category (requires auth) |
| DELETE | `/api/categories/{id}` | Delete category (requires auth) |

### Favorites

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/favorites` | Get user's favorites (requires auth) |
| POST | `/api/recipes/{id}/favorite` | Add to favorites (requires auth) |
| DELETE | `/api/recipes/{id}/favorite` | Remove from favorites (requires auth) |
| GET | `/api/recipes/{id}/favorite` | Check if favorited (requires auth) |

### Ratings

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/recipes/{id}/rate` | Rate a recipe (requires auth) |
| GET | `/api/recipes/{id}/rate` | Get user's rating (requires auth) |
| DELETE | `/api/recipes/{id}/rate` | Remove rating (requires auth) |

## Authentication

The API uses Laravel Sanctum for token-based authentication.

### Getting a Token

1. Register or login to get a token:
   ```bash
   curl -X POST http://localhost:8000/api/auth/login \
     -H "Content-Type: application/json" \
     -d '{"email": "user@example.com", "password": "password"}'
   ```

2. Use the token in subsequent requests:
   ```bash
   curl -X GET http://localhost:8000/api/recipes \
     -H "Authorization: Bearer YOUR_TOKEN_HERE"
   ```

## Example Usage

### Create a Recipe

```bash
curl -X POST http://localhost:8000/api/recipes \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Spaghetti Carbonara",
    "description": "Classic Italian pasta dish",
    "ingredients": "Pasta, eggs, cheese, pancetta",
    "instructions": "1. Cook pasta...",
    "cooking_time": 30,
    "servings": 4,
    "difficulty": "medium",
    "categories": [1, 2]
  }'
```

### Search Recipes

```bash
curl -X GET "http://localhost:8000/api/recipes?search=pasta&category=1&difficulty=medium"
```

## Response Format

All API responses follow a consistent JSON format:

### Success Response
```json
{
  "message": "Recipe created successfully",
  "recipe": {
    "id": 1,
    "name": "Spaghetti Carbonara",
    "description": "Classic Italian pasta dish",
    // ... other fields
  }
}
```

### Error Response
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."]
  }
}
```

## Development

### Running Tests
```bash
php artisan test
```

### Code Style
```bash
./vendor/bin/pint
```

### Regenerate API Documentation
```bash
php artisan l5-swagger:generate
```

