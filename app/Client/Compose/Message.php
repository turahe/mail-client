<?php

namespace Modules\MailClient\Client\Compose;

class Message extends AbstractComposer
{
    /**
     * Send a new message.
     *
     * @return \Modules\MailClient\Client\Contracts\MessageInterface
     */
    public function send()
    {
        return $this->client->send();
    }
}
