# Growmodo Client Portal

## Requirements

### All you need is [Docker](https://docker.com).

Or, for manual setup: Download and install the following requirements.

| Name            | Version  |
| --------------- | -------- |
| PHP and PHP-FPM | > 8.x    |
| Composer        | > 2.2.x  |
| NGINX           | > 1.1x.x |
| PostgreSQL      | > 15.x   |

## Notes:

-   You must also have GIT and SSH installed
-   `We recommend to use Docker for fast development setup`
-   `Do no commit directly on Master branch, just open a PR and wait for the reviewers to approve`
-   Never include any environment variables in the commit
-   Always use Laravel Migrations for easy database table management
-   Do not update docker-compose.yml when you change port or anything for local purposes only, you can create docker-compose.override.yml to override the script. [Please check this documentation.](https://docs.docker.com/compose/extends/)

#

## Clone the repository

`Note:` This is a private repository and you need to have your ssh key added on your github account.

See [Generating a new SSH key and adding it to the ssh-agent](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent?platform=windows)

```
git clone git@github.com:Admin-growmodo/GrowmodoClientPortal.git
```

## Steps to Install `with Docker`

Download and Install [Docker](https://docker.com) and make sure it is running.
You can check if you it is working from `terminal` or `cmd` and execute the command:

```
docker run hello-world
```

Open `terminal` or `cmd` and navigate to the project directory

```bash
cd /_path_to_repo_/GrowmodoClientPortal
```

Copy `.env.example` file and rename it to `.env`

Then update the values in .env

```bash
cp .env.example .env
```

Run docker-compose.yml. You can also update file to change the exposed ports if ports `80`, `443`, `5050` or `5432` is not available or used by other services.

```bash
# PORT 80 - HTTP
# PORT 443 - HTTPS
# PORT 5050 - PGAdmin WebUI
# PORT 5432 - PostgreSQL Database

docker compose up

# or run in Detached mode
# no terminal output
# you can check the logs in Docker Desktop or with CLI commands
docker compose up -d
```

When you see the message `API is Ready`, you can now run the [initialization script](.docker/init.sh).

This will run all the initial setup needed including database seeders.

```bash
sh ./.docker/init.sh
```

Done! Try visiting on your browser [https://localhost](https://localhost) or with custom port if updated in [docker-compose.yml](docker-compose.yml).

`Note:` When you see `502 Bad Gateway` after you run the docker compose. You should check the log if it says `API is Ready`, if not, then you should wait. Or, if you see and error please send us a direct message for us to investigate.

#

## Steps to Install `without Docker`

Copy `.env.example` file and rename it to `.env`

Then update the values in .env

```bash
cp .env.example .env
```

Install the require packages.

```bash
composer install
```

Run `php artisan key:generate`

```bash
php artisan key:generate
```

Run the migration

```bash
php artisan migrate
```

Run `php artisan passport:install`

```bash
php artisan passport:install
```

Run the seeders

```bash
php artisan db:seed AclSeeder;
php artisan db:seed RolesSeeder1;
php artisan db:seed BrandCategorySeeder;
php artisan db:seed CompanyTypesSeeder;
php artisan db:seed EmployeeCountSeeder;
php artisan db:seed OptionsSeeder;
php artisan db:seed PlanTypeSeeder;
php artisan db:seed PlatformsSeeder;
php artisan db:seed ProjectDirSeeder;
php artisan db:seed TaskDirSeeder;
php artisan db:seed SubTalentSeeder;
php artisan db:seed DynamicQuestions;
```

Run `php artisan optimize:clear`

```bash
php artisan optimize:clear
```

Done!

#

## When pulling updates

### Restarting docker compose or the `app` container will automatically perform the following commands

1. Run `composer install`
2. Run `composer dump-autoload`
3. Run `php artisan migrate`
4. Run `php artisan optimize:clear`

#

### ©[Growmodo, GmbH](https://growmodo.com)
