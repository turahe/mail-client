<?php


namespace Turahe\MailClient\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Turahe\MailClient\Client\Contracts\MessageInterface;
use Turahe\MailClient\Models\EmailAccountMessage;

class EmailAccountMessageCreated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public EmailAccountMessage $message, public MessageInterface $remoteMessage) {}
}
