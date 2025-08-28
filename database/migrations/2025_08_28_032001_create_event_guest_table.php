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
        Schema::create('event_guest', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('guest_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['active', 'removed', 'expired'])->default('active');
            $table->timestamp('removed_at')->nullable();
            $table->text('removal_reason')->nullable();
            $table->foreignId('removed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Ensure unique event-guest combinations
            $table->unique(['event_id', 'guest_id']);
            
            // Index for performance
            $table->index(['event_id', 'status']);
            $table->index(['guest_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_guest');
    }
};
