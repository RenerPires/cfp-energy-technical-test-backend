# Basic structure of a Laravel 10 REST Project

## Running the project

Clone this repository
```bash
git clone https://github.com/RenerPires/laravel-utils-base-project.git app-laravel
```
```bash
cd app-laravel/
```

Copy the .env.example file to .env
```bash
cp .env.example .env
```

Edit the .env file with your database settings
```dotenv
APP_NAME=Laravel
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=database_name
DB_USERNAME=db_user
DB_PASSWORD=db_password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Start the containers
```bash
docker-compose up -d
```

Access the container
```bash
docker-compose exec app bash
```

Install the dependencies
```bash
composer install
```

Generate the application key
```bash
php artisan key:generate
```

Access the application in your browser at http://localhost
