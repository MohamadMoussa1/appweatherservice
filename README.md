# Weather API Service

A simple RESTful API service that provides weather information for cities using the OpenWeather API.

## Features

- Get current weather for any city
- Clean, consistent JSON responses
- Error handling and input validation
- Rate limiting protection
- API documentation with OpenAPI/Swagger

## Requirements

- PHP 8.2+
- Composer
- OpenWeather API key (free tier available at [OpenWeather](https://openweathermap.org/api))

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/weather-api.git
   cd weather-api
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Copy the environment file and configure:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Add your OpenWeather API key to `.env`:
   ```env
   OPENWEATHER_API_KEY=your_api_key_here
   ```

5. (Optional) Configure other settings in `.env` if needed:
   ```env
   OPENWEATHER_BASE_URL=https://api.openweathermap.org/data/2.5
   OPENWEATHER_TIMEOUT=10
   ```

## API Documentation

### Get Weather for a City

**Endpoint:** `GET /api/v1/weather/{city}`

**Parameters:**
- `city` (required): City name and optional country code (e.g., "London,uk" or "New York,us")

**Example Request:**
```http
GET /api/v1/weather/London,uk
```

**Success Response (200 OK):**
```json
{
    "city": "London",
    "temperature": 18.5,
    "condition": "Clouds",
    "humidity": 75
}
```

**Error Responses:**
- 400 Bad Request: Invalid input
- 401 Unauthorized: Invalid API key
- 404 Not Found: City not found
- 429 Too Many Requests: Rate limit exceeded
- 500 Internal Server Error: Server error

## Development

### Running Tests

```bash
composer test
```

### Code Style

This project follows PSR-12 coding standards. To check and fix code style:

```bash
composer lint
composer fix
```

## Security Vulnerabilities

If you discover a security vulnerability, please send an e-mail to your-email@example.com. All security vulnerabilities will be promptly addressed.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
