<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('model_sync_states', function (Blueprint $table) {
            $table->id();
            $table->morphs('syncable');
            $table->string('source')->nullable();
            $table->uuid('client_generated_id')->nullable()->index();
            $table->foreignId('device_id')->nullable()->cascadeOnDelete();
            $table->timestamp('last_synced_at');
            $table->timestamps();

            if (class_exists('\Whilesmart\UserDevices\Models\Device')) {
                $table->foreign('device_id')->references('id')->on(config('user-devices.db_table_name', 'devices'))->onDelete('cascade');
            }
            $table->unique(['syncable_type', 'syncable_id', 'device_id', 'client_generated_id'], 'sync_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_sync_states');
    }
};
