<?php
namespace Modules\MailClient\Client\Compose;

class MessageForward extends MessageReply
{
    /**
     * Forward the message.
     *
     * @return \Modules\MailClient\Client\Contracts\MessageInterface
     */
    public function send()
    {
        return $this->client->forward($this->remoteId, $this->folder);
    }
}
