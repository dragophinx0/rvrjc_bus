<?php

namespace App\Console\Commands;

use App\Models\Poll;
use Illuminate\Console\Command;

class CreateDailyPolls extends Command
{
    protected $signature = 'poll:create-daily';
    protected $description = 'Create daily morning and evening polls';

    public function handle()
    {
        // Tomorrow's Morning Poll (Who will board tomorrow?)
        $tomorrow = now()->addDay()->toDateString();
        Poll::firstOrCreate([
            'type' => 'morning',
            'date' => $tomorrow
        ]);

        // Today's Evening Poll (Drop location for today?)
        $today = now()->toDateString();
        Poll::firstOrCreate([
            'type' => 'evening',
            'date' => $today
        ]);

        $this->info("Daily polls created for {$today} (Evening) and {$tomorrow} (Morning).");
    }
}
