<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('ratings', static function (Blueprint $table) {
            $table->id();
            $table->integer('score');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->morphs('rateable');
            $table->timestamps();
            $table->unique(['user_id', 'rateable_id', 'rateable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
