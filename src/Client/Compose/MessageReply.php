<?php

namespace Turahe\MailClient\Client\Compose;

use Turahe\MailClient\Client\Client;
use Turahe\MailClient\Client\FolderIdentifier;

class MessageReply extends AbstractComposer
{
    /**
     * Create new MessageReply instance.
     */
    public function __construct(
        Client $client,
        protected string|int $remoteId,
        protected FolderIdentifier $folder,
        ?FolderIdentifier $sentFolder = null
    ) {
        parent::__construct($client, $sentFolder);
    }

    /**
     * Reply to the message.
     *
     * @return \Turahe\MailClient\Client\Contracts\MessageInterface
     */
    public function send()
    {
        return $this->client->reply($this->remoteId, $this->folder);
    }
}
