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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('unique_id')->nullable();
            $table->string('username')->nullable();
            $table->boolean('is_bot')->nullable();
            $table->boolean('is_premium')->nullable();

            $table->unique(['unique_id']);
            $table->unique(['username']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['unique_id']);
            $table->dropUnique(['username']);

            $table->dropColumn('unique_id');
            $table->dropColumn('username');
            $table->dropColumn('is_bot');
            $table->dropColumn('is_premium');
        });
    }
};
