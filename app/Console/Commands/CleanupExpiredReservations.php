<?php

namespace App\Console\Commands;

use App\Models\SeatReservation;
use Illuminate\Console\Command;

class CleanupExpiredReservations extends Command
{
    protected $signature = 'bus:cleanup-reservations';
    protected $description = 'Cleanup expired seat reservations';

    public function handle()
    {
        $deleted = SeatReservation::where('status', 'reserved')
            ->where('expires_at', '<', now())
            ->delete();

        $this->info("Deleted {$deleted} expired reservations.");
    }
}
