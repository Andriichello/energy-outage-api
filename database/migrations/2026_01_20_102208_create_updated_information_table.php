<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('updated_information', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('url');
            $table->text('content');
            $table->string('content_hash', 64);
            $table->json('metadata')->nullable();
            $table->timestamp('fetched_at');
            $table->timestamps();

            $table->index(['provider', 'fetched_at']);
            $table->index(['content_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('updated_information');
    }
};
