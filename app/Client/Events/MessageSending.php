<?php

namespace Modules\MailClient\Client\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\MailClient\Client\Client;

class MessageSending
{
    use Dispatchable;

    /**
     * Create new MessageSending instance.
     */
    public function __construct(public Client $client) {}
}
