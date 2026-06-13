# Installation

You can install the **Eloquent Syncables** package via composer:

```bash
composer require whilesmart/eloquent-syncables
```

## Publishing Assets

After installing the package, you should publish and run the migrations:

```bash
php artisan vendor:publish --tag="syncables-migrations"
php artisan migrate
```

You can optionally publish the configuration file to customize the default behavior:

```bash
php artisan vendor:publish --tag="syncables-config"
```

This will create a `config/syncables.php` file in your application.
