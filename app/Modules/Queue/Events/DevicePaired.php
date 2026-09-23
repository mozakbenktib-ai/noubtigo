<?php

namespace App\Modules\Queue\Events;

use App\Modules\Queue\Models\DisplayDevice;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DevicePaired implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $device;
    public $token;

    /**
     * Create a new event instance.
     */
    public function __construct(DisplayDevice $device, string $token)
    {
        $this->device = $device;
        $this->token = $token;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('display.pairing.' . $this->device->uid),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'device.paired';
    }
}
