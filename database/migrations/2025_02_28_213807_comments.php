<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('comments', static function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->morphs('commentable');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
