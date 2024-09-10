<?php

namespace Turahe\MailClient\Client\Gmail;

use Turahe\Core\Facades\Google as Client;
use Turahe\Core\OAuth\AccessTokenProvider;
use Turahe\MailClient\Client\AbstractSmtpClient;
use Turahe\MailClient\Client\Compose\PreparesSymfonyMessage;
use Turahe\MailClient\Client\FolderIdentifier;

class SmtpClient extends AbstractSmtpClient
{
    use PreparesSymfonyMessage;

    /**
     * Create new SmtpClient instance.
     */
    public function __construct(protected AccessTokenProvider $token)
    {
        Client::connectUsing($token);
    }

    /**
     * Send mail message
     *
     * @return \Turahe\MailClient\Client\Contracts\MessageInterface
     */
    public function send()
    {
        $message = $this->prepareSymfonyMessage(
            Client::message()->sendMail(),
            $this->token->getEmail()
        );

        return new Message($message->send()->load());
    }

    /**
     * Reply to a given mail message
     *
     * @param  string  $remoteId
     * @return \Turahe\MailClient\Client\Contracts\MessageInterface
     */
    public function reply($remoteId, ?FolderIdentifier $folder = null)
    {
        /** @var \Turahe\MailClient\Client\Gmail\Message * */
        $remoteMessage = $this->imap->getMessage($remoteId);

        $message = $this->prepareSymfonyMessage($remoteMessage->reply(), $this->token->getEmail());

        /*
        $quote = $this->createQuoteOfPreviousMessage(
            $remoteMessage,
            $this->createInlineImagesProcessingFunction($message)
        );

        $message->setBody($message->getBody() . $quote, $this->getContentType());
        */

        // When there is no subject set, we will just
        // create a reply subject from the original message
        if (! $this->subject) {
            $message->subject($this->createReplySubject($remoteMessage->getSubject()));
        }

        return new Message($message->send()->load());
    }

    /**
     * Forward the given message
     *
     * @param  int  $remoteId
     * @return \Turahe\MailClient\Client\Contracts\MessageInterface
     */
    public function forward($remoteId, ?FolderIdentifier $folder = null)
    {
        /** @var \Turahe\MailClient\Client\Gmail\Message * */
        $remoteMessage = $this->imap->getMessage($remoteId);

        $message = $this->prepareSymfonyMessage($remoteMessage->reply(), $this->token->getEmail());

        /*
        $inlineMessage = $this->inlineMessage(
            $remoteMessage,
            $this->createInlineImagesProcessingFunction($message)
        );

        $message->setBody($message->getBody() . $inlineMessage, $this->getContentType());
        */

        // When there is no subject set, we will just
        // create a reply subject from the original message
        if (! $this->subject) {
            $message->subject($this->createForwardSubject($remoteMessage->getSubject()));
        }

        return new Message($message->send()->load());
    }
}
