<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\GuestList;

class CalculateGuestListHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guest-lists:calculate-health {--list-id= : Calculate health for specific guest list ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and store health information for guest lists';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $listId = $this->option('list-id');
        
        if ($listId) {
            $guestList = GuestList::find($listId);
            if (!$guestList) {
                $this->error("Guest list with ID {$listId} not found.");
                return 1;
            }
            
            $this->info("Calculating health for guest list: {$guestList->name}");
            $health = $guestList->calculateAndStoreHealth();
            $this->info("Health calculated: {$health['status']}");
            $this->info("Issues found: " . count($health['issues']));
            
            return 0;
        }
        
        $this->info("Calculating health for all guest lists...");
        $guestLists = GuestList::all();
        $bar = $this->output->createProgressBar($guestLists->count());
        $bar->start();
        
        foreach ($guestLists as $guestList) {
            $guestList->calculateAndStoreHealth();
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Health calculation completed for {$guestLists->count()} guest lists.");
        
        return 0;
    }
}
