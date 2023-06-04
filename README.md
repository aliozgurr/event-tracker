# Event Tracker

This web application collects data from various event websites, stores it in a MySQL database, and presents it to users on a single website.

<img src="https://i.hizliresim.com/kogrv2w.png"/>

## Dependencies
* PHP 8.1
* Laravel 10
* Node.js 19
* MySQL 8
* Composer
* npm

## Installation

```sh
cp .env.example .env
```

Set up Database Configuration on .env
```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=database_name
DB_USERNAME=username
DB_PASSWORD=password
```

Then run these commands:
```sh
composer install
php artisan key:generate
php artisan migrate
npm install
npm run dev
```

## How does it work?

To crawl event data from a supported event platform, you can run `php artisan crawl-XXX` command.

For now, the project has limited count of event platforms that can be crawled. You can check the `app/Console/Commands` directory to see the commands of supported platforms.

