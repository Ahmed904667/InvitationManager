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
        // For SQLite, we need to recreate the table with new enum values
        if (DB::getDriverName() === 'sqlite') {
            // Create a new table with the updated schema
            Schema::create('notifications_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_id')->constrained()->onDelete('cascade');
                $table->foreignId('guest_id')->constrained('guests')->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->enum('type', ['guest_removal', 'event_update', 'event_reminder', 'custom']);
                $table->enum('channel', ['email', 'whatsapp', 'both']);
                $table->text('message');
                $table->enum('status', ['pending', 'queued', 'sending', 'sent', 'delivered', 'read', 'failed', 'bounced', 'undelivered', 'canceled']);
                $table->string('external_id')->nullable();
                $table->json('delivery_details')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->timestamps();
                
                $table->index(['event_id', 'guest_id']);
                $table->index(['status', 'channel']);
                $table->index(['sent_at']);
            });
            
            // Copy data from old table to new table
            DB::statement('INSERT INTO notifications_new SELECT * FROM notifications');
            
            // Drop old table and rename new table
            Schema::drop('notifications');
            Schema::rename('notifications_new', 'notifications');
        } else {
            // For MySQL/PostgreSQL, use ALTER TABLE
            DB::statement("ALTER TABLE notifications MODIFY COLUMN status ENUM('pending', 'queued', 'sending', 'sent', 'delivered', 'read', 'failed', 'bounced', 'undelivered', 'canceled') NOT NULL DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Recreate table with original enum values
            Schema::create('notifications_old', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_id')->constrained()->onDelete('cascade');
                $table->foreignId('guest_id')->constrained('guests')->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->enum('type', ['guest_removal', 'event_update', 'event_reminder', 'custom']);
                $table->enum('channel', ['email', 'whatsapp', 'both']);
                $table->text('message');
                $table->enum('status', ['pending', 'sent', 'delivered', 'failed', 'bounced']);
                $table->string('external_id')->nullable();
                $table->json('delivery_details')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->timestamps();
                
                $table->index(['event_id', 'guest_id']);
                $table->index(['status', 'channel']);
                $table->index(['sent_at']);
            });
            
            // Copy data back (only for statuses that exist in old enum)
            DB::statement('INSERT INTO notifications_old SELECT * FROM notifications WHERE status IN ("pending", "sent", "delivered", "failed", "bounced")');
            
            // Drop new table and rename old table
            Schema::drop('notifications');
            Schema::rename('notifications_old', 'notifications');
        } else {
            // For MySQL/PostgreSQL, revert ALTER TABLE
            DB::statement("ALTER TABLE notifications MODIFY COLUMN status ENUM('pending', 'sent', 'delivered', 'failed', 'bounced') NOT NULL DEFAULT 'pending'");
        }
    }
};
