# Technical Assessment back-end engineer

The assessment test is available [Here](resources/assessment.md).

## Getting Started

* Development Requirements
* Installation
* Starting Devevelopment Server
* Documentation
* Testing

## Development Requirements

This application currently runs on <b>Laravel 9.19</b> and <b>PHP 8.0+</b> and the other development requirements to get
this application up and running are as follow:

* Sqlite
* MySQL
* git
* Composer

### Installation

#### Step 1: Clone the repository

```bash
git clone https://github.com/madewithlove/technical-assignment-back-end-engineer-ayodeleoniosun.git
```

#### Step 2: Switch to the repo folder

```bash
cd technical-assignment-back-end-engineer-ayodeleoniosun
```

#### Step 3: Install all composer dependencies

```bash
composer install
```

#### Step 4: Setup environment variable

- Copy `.env.example` to `.env` i.e `cp .env.example .env`
- Update all the variables as needed

#### Step 5: Generate a new application key

```bash
php artisan key:generate
``` 

## Starting Development Server

Docker via Laravel sail was used for the development server for this project. <br/>
To start your development server, your default database configuration in the .env should be as follow:

```bash
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=shopping
DB_USERNAME=sail
DB_PASSWORD=password
```

To spring up a docker container

```bash
./vendor/bin/sail up -d
```

However, if you want to change the DB username and password after springing forth a docker container using the laravel
sail, update the DB_USERNAME and DB_PASSWORD in the .env with the new details and then run these:

```bash
./vendor/bin/sail down -v
./vendor/bin/sail build --no-cache
./vendor/bin/sail up -d
```

#### Run database migration alongside the seeder

```bash
./vendor/bin/sail artisan migrate:fresh --seed
``` 

### Documentation

The Postman API collection is available [Here](resources/madewithlove.postman_collection.json). <br/>

### Testing

The project testing is done via Pest. You can check [Here](https://pestphp.com/docs/installation) for more
details. <br/>
To run the tests:

```bash
./vendor/bin/sail artisan test
```
