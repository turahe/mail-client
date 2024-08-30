<?php

namespace Modules\MailClient\Client\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\MailClient\Client\Client;

class MessageSent
{
    use Dispatchable;

    /**
     * Create new MessageSent instance.
     */
    public function __construct(public Client $client) {}
}
