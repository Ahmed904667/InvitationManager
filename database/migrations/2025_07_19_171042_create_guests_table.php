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
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_list_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->string('email', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('language', 30)->nullable();
            $table->foreignId('group_id')->nullable()->constrained('guest_groups')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
