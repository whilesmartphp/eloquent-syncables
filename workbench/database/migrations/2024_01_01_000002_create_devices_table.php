<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('deviceable_type');
            $table->unsignedBigInteger('deviceable_id');
            $table->enum('type', ['mobile', 'web', 'desktop'])->default('mobile');
            $table->string('token')->unique();
            $table->string('identifier')->nullable();
            $table->string('platform')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('devices');
        Schema::enableForeignKeyConstraints();
    }
};
