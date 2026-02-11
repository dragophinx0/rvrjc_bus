<?php

namespace App\Console\Commands;

use App\Models\Bus;
use App\Models\BusLocationHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class StoreBusLocationHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bus:store-history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store current bus locations from cache into database history';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $buses = Bus::where('is_active', true)->get();

        foreach ($buses as $bus) {
            $location = Cache::get("bus_{$bus->id}_location");

            if ($location) {
                BusLocationHistory::create([
                    'bus_id' => $bus->id,
                    'driver_id' => $location['driver_id'],
                    'latitude' => $location['lat'],
                    'longitude' => $location['lng'],
                    'speed' => $location['speed'],
                    'recorded_at' => $location['updated_at'],
                ]);

                $this->info("Stored history for Bus #{$bus->bus_number}");
            }
        }
    }
}
