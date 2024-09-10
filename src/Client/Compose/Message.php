<?php

namespace Turahe\MailClient\Client\Compose;

class Message extends AbstractComposer
{
    /**
     * Send a new message.
     *
     * @return \Turahe\MailClient\Client\Contracts\MessageInterface
     */
    public function send()
    {
        return $this->client->send();
    }
}
