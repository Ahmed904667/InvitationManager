<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we need to recreate the table to modify the enum
        if (DB::getDriverName() === 'sqlite') {
            // Create a new table with the updated enum
            Schema::create('notifications_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('guest_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
                $table->enum('type', ['guest_removal', 'event_update', 'event_reminder', 'event_start_reminder', 'rsvp_response', 'custom']);
                $table->enum('channel', ['email', 'whatsapp', 'both']);
                $table->text('message');
                $table->enum('status', ['pending', 'queued', 'sending', 'sent', 'delivered', 'read', 'failed', 'bounced', 'undelivered', 'canceled'])->default('pending');
                $table->string('external_id')->nullable();
                $table->json('delivery_details')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->timestamps();
            });

            // Copy data from old table to new table
            DB::statement('INSERT INTO notifications_temp SELECT * FROM notifications');

            // Drop old table and rename new table
            Schema::drop('notifications');
            Schema::rename('notifications_temp', 'notifications');
        } else {
            // For MySQL/PostgreSQL, use MODIFY COLUMN
            DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('guest_removal', 'event_update', 'event_reminder', 'event_start_reminder', 'rsvp_response', 'custom') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Recreate table with original enum values
            Schema::create('notifications_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('guest_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
                $table->enum('type', ['guest_removal', 'event_update', 'event_reminder', 'custom']);
                $table->enum('channel', ['email', 'whatsapp', 'both']);
                $table->text('message');
                $table->enum('status', ['pending', 'queued', 'sending', 'sent', 'delivered', 'read', 'failed', 'bounced', 'undelivered', 'canceled'])->default('pending');
                $table->string('external_id')->nullable();
                $table->json('delivery_details')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->timestamps();
            });

            // Copy data back (filtering out new types)
            DB::statement("INSERT INTO notifications_temp SELECT * FROM notifications WHERE type IN ('guest_removal', 'event_update', 'event_reminder', 'custom')");

            Schema::drop('notifications');
            Schema::rename('notifications_temp', 'notifications');
        } else {
            DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('guest_removal', 'event_update', 'event_reminder', 'custom') NOT NULL");
        }
    }
};