<?php

namespace Whilesmart\Syncables\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModelSyncState extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'client_generated_id',
        'last_synced_at',
        'device_id',
    ];

    public function syncable(): MorphTo
    {
        return $this->morphTo();
    }

    public function device(): ?BelongsTo
    {
        if (class_exists('\Whilesmart\UserDevices\Models\Device')) {
            // @phpstan-ignore-next-line
            return $this->belongsTo('\Whilesmart\UserDevices\Models\Device');
        }
        return null;
    }
}
