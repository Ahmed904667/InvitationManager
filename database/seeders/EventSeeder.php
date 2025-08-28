<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Shared\Models\Event;
use App\Shared\Models\User;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first organizer user
        $organizer = User::where('role', 'organizer')->first();
        
        if (!$organizer) {
            $this->command->info('No organizer user found. Please create one first.');
            return;
        }

        Event::create([
            'name' => 'Summer Networking Mixer',
            'description' => 'Join us for an evening of networking, refreshments, and great conversations with industry professionals.',
            'start_date' => now()->addDays(30)->setTime(18, 0),
            'end_date' => now()->addDays(30)->setTime(22, 0),
            'location' => 'Downtown Conference Center',
            'venue_name' => 'Grand Ballroom',
            'venue_address' => "123 Main Street\nDowntown, City 12345",
            'parking_info' => 'Free parking available in the adjacent garage',
            'invitation_title' => "You're Invited!",
            'invitation_subtitle' => 'Summer Networking Mixer',
            'invitation_message' => 'We cordially invite you to join us for an evening of professional networking and socializing.',
            'rsvp_message' => 'Please confirm your attendance by responding to this invitation.',
            'rsvp_deadline' => 'One week before the event',
            'rsvp_contact' => 'events@company.com',
            'rsvp_enabled' => true,
            'qr_checkin_enabled' => true,
            'qr_code_url' => 'https://example.com/event-checkin',
            'qr_description' => 'Scan for quick check-in and event details',
            'hero_color1' => '#667eea',
            'hero_color2' => '#764ba2',
            'accent_color' => '#ff6b6b',
            'font_family' => 'Segoe UI',
            'message_template' => 'Dear {guestName}, you are invited to {eventName} on {eventDate} at {eventLocation}. We look forward to seeing you there!',
            'send_type' => 'now',
            'status' => 'draft',
            'user_id' => $organizer->id,
        ]);

        $this->command->info('Sample event created successfully!');
    }
}
