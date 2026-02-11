<?php

namespace App\Console\Commands;

use App\Models\BusLocationHistory;
use Illuminate\Console\Command;

class CleanupOldLocationHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bus:cleanup-history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete bus location history older than 7 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deleted = BusLocationHistory::where('recorded_at', '<', now()->subDays(7))->delete();
        $this->info("Deleted {$deleted} old location records.");
    }
}
