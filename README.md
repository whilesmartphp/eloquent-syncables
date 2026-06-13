# Eloquent Syncables

[![Latest Version on Packagist](https://img.shields.io/packagist/v/whilesmart/eloquent-syncables.svg?style=flat-square)](https://packagist.org/packages/whilesmart/eloquent-syncables)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/whilesmart/eloquent-syncables/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/whilesmart/eloquent-syncables/actions?query=workflow%3Atests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/whilesmart/eloquent-syncables.svg?style=flat-square)](https://packagist.org/packages/whilesmart/eloquent-syncables)

**Eloquent Syncables** is a lightweight Laravel package that allows you to easily track when Eloquent models are synchronized with client devices. It supports tracking last synchronization times and mapping client-generated UUIDs and device tokens to database records.

---

## Installation

You can install the package via Composer:

```bash
composer require whilesmart/eloquent-syncables
```

You should publish and run the migrations:

```bash
php artisan vendor:publish --tag="syncables-migrations"
php artisan migrate
```

You can optionally publish the config file:

```bash
php artisan vendor:publish --tag="syncables-config"
```

This will create a `config/syncables.php` file in your application directory.

---

## Usage

### Preparing Your Models

Add the `Syncable` trait to any Eloquent model you want to track:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Whilesmart\Syncables\Traits\Syncable;

class Post extends Model
{
    use Syncable;
}
```

### Sync Lifecycle

#### Automatic Sync Creation
Whenever a model using the `Syncable` trait is created, an associated `ModelSyncState` is automatically created with the current timestamp:

```php
$post = Post::create(['title' => 'New Syncable Record']);
// A sync state is automatically created.
```

#### Mapping Client Generated IDs and Devices
You can record client-generated UUIDs and associate them with a user device.

**Method A: Delimited Value**
```php
$post->setClientGeneratedId('iphone_15_pro:763261a8-c2b6-4bfa-8739-9d52410a56f2', $user);
```

**Method B: Separate Client ID and Device Token**
```php
$post->setClientGeneratedId('763261a8-c2b6-4bfa-8739-9d52410a56f2', $user, 'iphone_15_pro');
```

> [!NOTE]
> Device association requires the suggested package `whilesmart/laravel-user-devices`. If not present, the package will safely bypass device creation and store only the client-generated ID.

#### Fetching Sync State Details
```php
// Retrieve the last sync date
$lastSynced = $post->last_synced_at; // e.g., "2026-06-13 16:00:00"

// Retrieve the colon-delimited client ID
$clientId = $post->client_generated_id; // e.g., "iphone_15_pro:763261a8-c2b6-4bfa-8739-9d52410a56f2"
```

#### Updating Synced Status
To update the `last_synced_at` timestamp on subsequent sync events:

```php
$post->markAsSynced();
```

---

## Testing

You can run the package test suite using:

```bash
composer test
```

---

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
