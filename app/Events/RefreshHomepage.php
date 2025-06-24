<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RefreshHomepage implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $session;

    public function __construct($session)
    {
        $this->session = $session;
    }

    public function broadcastOn()
    {
        return new Channel('homepage-refresh');
    }

    public function broadcastWith()
    {
        return ['session' => $this->session];
    }
}
