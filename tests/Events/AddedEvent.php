<?php

namespace Nicy\Tests\Events;

use League\Event\AbstractEvent as Event;

class AddedEvent extends Event
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function getName()
    {
        return 'user_added_event';
    }
}