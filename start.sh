#!/bin/bash

php artisan storage:link

# Start serve in background so healthcheck can pass immediately
php artisan serve --host=0.0.0.0 --port=$PORT &
SERVE_PID=$!

# Run migrations and caching in the background
php artisan migrate:fresh --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Wait for the serve process to keep container alive
wait $SERVE_PID
