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
        Schema::table('events', function (Blueprint $table) {
            // Remove design-related columns that are not used
            $table->dropColumn([
                'hero_color1',
                'hero_color2', 
                'accent_color',
                'font_family'
            ]);
            
            // Remove legacy/unused message columns
            $table->dropColumn([
                'message_template',
                'custom_messages',
                'attachments'
            ]);
            
            // Remove other unused columns
            $table->dropColumn([
                'ai_generated',
                'dates_in_utc',
                'additional_information'
            ]);
            
            // Remove QR code and invitation related columns
            $table->dropColumn([
                'qr_code_url',
                'qr_description',
                'invitation_subtitle',
                'invitation_message',
                'rsvp_message',
                'rsvp_deadline',
                'rsvp_contact',
                'parking_info'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Re-add design columns
            $table->string('hero_color1')->default('#ff6b6b')->after('qr_description');
            $table->string('hero_color2')->default('#ffa726')->after('hero_color1');
            $table->string('accent_color')->default('#667eea')->after('hero_color2');
            $table->string('font_family')->default('Segoe UI')->after('accent_color');
            
            // Re-add legacy message columns
            $table->text('message_template')->nullable()->after('font_family');
            $table->json('custom_messages')->nullable()->after('message_template');
            $table->json('attachments')->nullable()->after('custom_messages');
            
            // Re-add other columns
            $table->boolean('ai_generated')->default(false)->after('attachments');
            $table->boolean('dates_in_utc')->default(false)->after('ai_generated');
            $table->text('additional_information')->nullable()->after('dates_in_utc');
            
            // Re-add QR code and invitation related columns
            $table->string('qr_code_url')->nullable()->after('additional_information');
            $table->text('qr_description')->nullable()->after('qr_code_url');
            $table->string('invitation_subtitle')->nullable()->after('qr_description');
            $table->text('invitation_message')->nullable()->after('invitation_subtitle');
            $table->text('rsvp_message')->nullable()->after('invitation_message');
            $table->string('rsvp_deadline')->nullable()->after('rsvp_message');
            $table->string('rsvp_contact')->nullable()->after('rsvp_deadline');
            $table->text('parking_info')->nullable()->after('rsvp_contact');
        });
    }
};