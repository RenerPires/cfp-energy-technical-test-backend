# Technical Test – CFP Energy – Backend 🚀

## Used Technologies

This project uses the following technologies:

![Tecnologias](https://skillicons.dev/icons?i=php,laravel,mysql,docker)

## Requisites

To run this project you only need docker installed. Just that 🐋💕.

## Running the project ☕

Para iniciar o projeto você precisa clonar esse repositório com o seguinte comando:

```bash
git clone https://github.com/RenerPires/cfp-energy-technical-test-backend.git
cd cfp-energy-technical-test-backend
```

Don't forget to copy the content of the file `.env.example` to `.env`

```bash
cp .env.example .env
```

After that you only need to create a container to run the project `docker-compose` 🐋

```bash
docker-compose up -d
```

To access the containerized app use the following command:

```bash
docker-compose exec app bash
```

Install all dependencies via `composer`

```bash
composer install
```

Populate the database with initial data using `artisan`

```bash
php artisan migrate:fresh --seed
```

Finally, you can access the application in your browser at http://localhost

---

Use the following link to access the [Postman Collection and Docs](https://documenter.getpostman.com/view/2484339/2sA2r53k1x)
