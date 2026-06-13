<?php

namespace Whilesmart\Syncables\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Log;
use Whilesmart\Syncables\Models\ModelSyncState;

// @phpstan-ignore-next-line
trait Syncable
{
    public static function bootSyncable(): void
    {
        static::created(function ($model) {
            $model->syncState()->create([
                'last_synced_at' => now(),
            ]);
        });
    }

    public function syncState(): MorphOne
    {
        return $this->morphOne(ModelSyncState::class, 'syncable');
    }

    public function setClientGeneratedId(string $value, Model|Authenticatable $user, ?string $deviceToken = null): void
    {
        if (empty($value)) {
            return;
        }

        $device = null;
        $randomId = $value;

        if (class_exists('\Whilesmart\UserDevices\Models\Device')) {
            if ($deviceToken !== null) {
                $deviceIdentifier = $deviceToken;
                $randomId = $value;
            } else {
                $parts = explode(':', $value);
                if (count($parts) !== 2) {
                    Log::info('[Sync] client_id not in expected format', ['value' => $value]);

                    return;
                }
                [$deviceIdentifier, $randomId] = $parts;
            }

            $device = \Whilesmart\UserDevices\Models\Device::where('device_identifier', $deviceIdentifier)->first();
            if (! $device) {
                $device = \Whilesmart\UserDevices\Models\Device::create([
                    'user_id' => $user->getAuthIdentifier(),
                    'device_identifier' => $deviceIdentifier,
                ]);
            } elseif ($device->user_id !== $user->getAuthIdentifier()) {
                $device->user_id = $user->getAuthIdentifier();
                $device->save();
            }
        }

        $this->syncState()->updateOrCreate([], [
            'client_generated_id' => $randomId,
            'device_id' => $device?->id,
            'last_synced_at' => now(),
        ]);
    }

    public function getLastSyncedAtAttribute(): ?string
    {
        return $this->syncState?->last_synced_at;
    }

    public function getClientGeneratedIdAttribute(): ?string
    {
        $deviceIdentifier = $this->syncState?->device?->device_identifier;
        $clientId = $this->syncState?->client_generated_id;
        if ($deviceIdentifier && $clientId) {
            return $deviceIdentifier . ':' . $clientId;
        }

        return $clientId;
    }

    public function markAsSynced(): ModelSyncState
    {
        return $this->syncState()->updateOrCreate([], ['last_synced_at' => now()]);
    }
}
