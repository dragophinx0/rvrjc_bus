<?php

namespace App\Jobs;

use App\Events\BusLocationUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class UpdateBusLocationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $busId,
        public float $lat,
        public float $lng,
        public ?float $speed,
        public int $driverId
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $locationData = [
            'bus_id' => $this->busId,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'speed' => $this->speed,
            'driver_id' => $this->driverId,
            'updated_at' => now()->toISOString()
        ];

        // Store in cache for real-time access
        Cache::put("bus_{$this->busId}_location", $locationData, now()->addMinutes(5));

        // Broadcast to WebSocket
        event(new BusLocationUpdated($locationData));
    }
}
