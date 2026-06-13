# Usage Guide

**Eloquent Syncables** allows you to track and manage when database records are synchronized with client devices. It tracks sync timestamps and correlates them with client-generated UUIDs and device tokens.

## Preparing Your Models

To make an Eloquent model syncable, import and use the `Syncable` trait on your model:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Whilesmart\Syncables\Traits\Syncable;

class Post extends Model
{
    use Syncable;
}
```

## How It Works

### 1. Automatic Sync State Lifecycle

When you create a model that uses the `Syncable` trait, the package automatically creates an associated `ModelSyncState` record with `last_synced_at` set to the current timestamp:

```php
$post = Post::create(['title' => 'My first syncable post']);

// Under the hood, this automatically creates:
// $post->syncState -> ModelSyncState (last_synced_at => now())
```

### 2. Tracking Client Generated IDs

Offline-first apps often generate temporary UUIDs on the client device before they are saved to the server. You can associate these IDs with your models using `setClientGeneratedId()`.

There are two ways to invoke this method:

#### Option A: Delimited Device Identifier and UUID
Pass a single string formatted as `device_identifier:client_generated_id`:

```php
$post->setClientGeneratedId('iphone_15_pro:763261a8-c2b6-4bfa-8739-9d52410a56f2', $user);
```

#### Option B: Separate UUID and Device Token
Pass the UUID as the first parameter, and the device token as the third parameter:

```php
$post->setClientGeneratedId('763261a8-c2b6-4bfa-8739-9d52410a56f2', $user, 'iphone_15_pro');
```

> [!NOTE]
> If the `whilesmart/laravel-user-devices` package is installed, the package will automatically find or create the `Device` model associated with the user and the given device token/identifier, updating the owner if necessary.

### 3. Retrieve Sync Information

The trait exposes helper accessors:

#### Last Synced Time
Retrieve the last synced timestamp:
```php
$lastSynced = $post->last_synced_at; // Returns e.g. "2026-06-13 16:00:00"
```

#### Client Generated ID
Retrieve the colon-delimited device identifier and client generated ID:
```php
$clientId = $post->client_generated_id; // Returns e.g. "iphone_15_pro:763261a8-c2b6-4bfa-8739-9d52410a56f2"
```

### 4. Updating Sync Status

Whenever you synchronize a model again, you can mark it as synced using `markAsSynced()` which updates the `last_synced_at` timestamp:

```php
$post->markAsSynced();
```
