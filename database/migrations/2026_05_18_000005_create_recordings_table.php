<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disease_id')->constrained('diseases')->cascadeOnDelete();
            $table->unsignedSmallInteger('session_number')->default(1);
            $table->json('title');
            $table->string('audio_path', 500)->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->boolean('is_general')->default(false);
            $table->unsignedInteger('plays_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['disease_id', 'session_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recordings');
    }
};
