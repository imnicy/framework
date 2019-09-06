<?php

namespace Nicy\Framework\Support;

use League\Event\AbstractEvent;
use Nicy\Framework\Bindings\Events\Contracts\Event as EventInstance;

abstract class Event extends AbstractEvent implements EventInstance
{
    // Events class alias
}