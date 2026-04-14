# GymFlow Mobile API

Laravel API for the GymFlow Mobile MVP.

## Scope

- Issue and revoke mobile access tokens with Sanctum
- Serve dashboard, workout, schedule, progress, and profile data
- Seed a local demo member and supporting sample records for development

## Local Development

```bash
composer install
php artisan migrate:fresh --seed
php artisan serve
```

API base URL: `http://127.0.0.1:8000/api/v1`

## Demo Credentials

- Email: `demo@gymflow.app`
- Password: `password123`

## Routes

All member-facing endpoints are exposed under `/api/v1`.

- `POST /auth/login`
- `POST /auth/logout`
- `GET /auth/me`
- `GET /dashboard`
- `GET /workouts`
- `GET /schedule`
- `GET /progress`
- `GET /profile`
- `PUT /profile`

## Tests

```bash
php artisan test
```
