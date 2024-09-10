<?php

namespace Turahe\MailClient\Client\Outlook;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Microsoft\Graph\Generated\Models\BodyType;
use Microsoft\Graph\Generated\Models\FileAttachment;
use Microsoft\Graph\Generated\Models\Message as MessageModel;
use Turahe\Core\Facades\MsGraph as Api;
use Turahe\Core\Mail\Headers\AddressHeader;
use Turahe\Core\Mail\Headers\HeadersCollection;
use Turahe\MailClient\Client\AbstractMessage;
use Turahe\MailClient\Client\Exceptions\ConnectionErrorException;
use Turahe\MailClient\Client\FolderIdentifier;

class Message extends AbstractMessage
{
    /**
     * @var \Turahe\Core\Common\Mail\Headers\HeadersCollection|null
     */
    protected $headers;

    /**
     * Get the message id
     *
     * @return string
     */
    public function getId()
    {
        return $this->getEntity()->getId();
    }

    /**
     * Get the message id
     *
     * @return string|null
     */
    public function getMessageId()
    {
        // Use the prop directory from Microsoft as the headers may not be
        // always included in the response
        return str_replace(['<', '>'], '', $this->getEntity()->getInternetMessageId());
    }

    /**
     * Get the message subject
     *
     * @return string|null
     */
    public function getSubject()
    {
        return $this->getEntity()->getSubject();
    }

    /**
     * Get the message date
     *
     * @return \Illuminate\Support\Carbon
     */
    public function getDate()
    {
        // For draft messages, we will use the last modified date
        // as Microsoft use the last modified date in the drafts folder
        $props = $this->getEntity()->getProperties();

        if ($this->isDraft()) {
            $date = $this->getEntity()->getLastModifiedDateTime();
        } else {
            // Microsoft adds the received date time for the sent items too
            // to be equal as the sentDateTime, hence, only receivedDateTime
            // can be used for the message date
            $date = $this->getEntity()->getReceivedDateTime();
        }

        $tz = config('src.timezone');

        return $date ? Carbon::parse($date)->tz($tz) : Carbon::now($tz);
    }

    /**
     * Get the Message text body
     *
     * Microsoft only return one type of body
     *
     * @return string|null
     */
    public function getTextBody()
    {
        $body = $this->getEntity()->getBody();

        if ($body && $body->getContentType()->is(BodyType::TEXT)) {
            return $body->getContent();
        }
    }

    /**
     * Get the message HTML body
     *
     * @return string|null
     */
    public function getHTMLBody()
    {
        $body = $this->getEntity()->getBody();

        if ($body && $body->getContentType()->is(BodyType::HTML)) {
            return $body->getContent();
        }
    }

    /**
     * Get the messsage attachments
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAttachments()
    {
        return $this->maskAttachments($this->getEntity()->getAttachments());
    }

    /**
     * Get message FROM
     *
     * @return AddressHeader
     */
    public function getFrom()
    {
        $from = $this->getEntity()->getFrom();

        if (! $from) {
            return null;
        }

        return new AddressHeader(
            'from',
            $from->getEmailAddress()->getAddress(),
            $from->getEmailAddress()->getName()
        );
    }

    /**
     * Get message TO
     *
     * @return \Turahe\Core\Common\Mail\Headers\AddressHeader|null
     */
    public function getTo()
    {
        return $this->parseAddresses('to', $this->getEntity()->getToRecipients());
    }

    /**
     * Get message CC
     *
     * @return \Turahe\Core\Common\Mail\Headers\AddressHeader|null
     */
    public function getCc()
    {
        return $this->parseAddresses('cc', $this->getEntity()->getCcRecipients());
    }

    /**
     * Get message BCC
     *
     * @return \Turahe\Core\Common\Mail\Headers\AddressHeader|null
     */
    public function getBcc()
    {
        return $this->parseAddresses('bcc', $this->getEntity()->getBccRecipients());
    }

    /**
     * Get message Reply-to
     *
     * @return AddressHeader
     */
    public function getReplyTo()
    {
        return $this->parseAddresses('reply-to', $this->getEntity()->getReplyTo());
    }

    /**
     * Get the message references
     *
     * @return array|null
     */
    public function getReferences()
    {
        $header = $this->getHeader('References');

        return $header ? $header->getIds() : null;
    }

    /**
     * Get message headers
     *
     * @return \Turahe\Core\Common\Mail\Headers\HeadersCollection
     */
    public function getHeaders()
    {
        if (! is_null($this->headers)) {
            return $this->headers;
        }

        $this->headers = new HeadersCollection;

        /**
         * @see https://github.com/microsoftgraph/microsoft-graph-docs/issues/2716
         */
        if ($headers = $this->getEntity()->getInternetMessageHeaders()) {
            foreach ($headers as $header) {
                $this->headers->pushHeader($header['name'], $header['value']);
            }
        } else {
            if ($singleValueExtendedProperties = $this->getEntity()->getSingleValueExtendedProperties()) {
                foreach ($singleValueExtendedProperties as $prop) {
                    if (! in_array($prop['id'], HeadersMap::MAP)) {
                        continue;
                    }

                    $headerName = array_flip(HeadersMap::MAP)[$prop['id']];
                    $this->headers->pushHeader($headerName, $prop['value']);
                }
            }

            // Try to set any headers via extensions
            $this->setHeadersViaExtensionsValues();
        }

        return $this->headers;
    }

    /**
     * Get message header
     *
     * @param  string  $name
     * @return \Turahe\Core\Common\Mail\Headers\Header|\Turahe\Core\Common\Mail\Headers\AddressHeader|\Turahe\Core\Common\Mail\Headers\IdHeader|\Turahe\Core\Common\Mail\Headers\DateHeader|null
     */
    public function getHeader($name)
    {
        return $this->getHeaders()->find($name);
    }

    /**
     * Get message Sender
     *
     * @return AddressHeader|null
     */
    public function getSender()
    {
        $sender = $this->getEntity()->getSender();

        if (! $sender) {
            return null;
        }

        return new AddressHeader(
            'sender',
            $sender->getEmailAddress()->getAddress(),
            $sender->getEmailAddress()->getName()
        );
    }

    /**
     * Check if the message has been read/seen
     *
     * @return bool
     */
    public function isRead()
    {
        return (bool) $this->getEntity()->getIsRead();
    }

    /**
     * Check whether the message is draft
     *
     * @return bool
     */
    public function isDraft()
    {
        return (bool) $this->getEntity()->getIsDraft();
    }

    /**
     * Mark the message as read
     *
     * @return void
     *
     * @throws \Turahe\MailClient\Client\Exceptions\ConnectionErrorException
     */
    public function markAsRead()
    {
        try {
            $message = new MessageModel;
            $message->setIsRead(true);

            Api::createPatchRequest("/me/messages/{$this->getId()}", $message)->execute();
        } catch (IdentityProviderException $e) {
            throw new ConnectionErrorException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Mark the message as unread
     *
     * @return void
     *
     * @throws \Turahe\MailClient\Client\Exceptions\ConnectionErrorException
     */
    public function markAsUnread()
    {
        try {
            $message = new \Microsoft\Graph\Generated\Models\Message;
            $message->setIsRead(false);

            Api::createPatchRequest("/me/messages/{$this->getId()}", $message)->execute();
        } catch (IdentityProviderException $e) {
            throw new ConnectionErrorException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get the message folders remote identifiers
     *
     * @return array
     */
    public function getFolders()
    {
        return [new FolderIdentifier('id', $this->getEntity()->getParentFolderId())];
    }

    /**
     * Get the Outlook message conversation id
     *
     * @return string|null
     */
    public function getConversationId()
    {
        return $this->getEntity()->getConversationId();
    }

    /**
     * Check whether the message is removed
     *
     * NOTE: Applicable only fetching messages via delta
     * the removed property will exists when fetching via existing delta link
     *
     * @return bool
     */
    public function isRemoved()
    {
        return isset($this->getEntity()->getProperties()['@removed']);
    }

    /**
     * Set headers via extensions
     */
    protected function setHeadersViaExtensionsValues()
    {
        if ($extensions = $this->getEntity()->getExtensions()) {
            foreach ($extensions as $headers) {
                if ($this->isExtensionHoldsHeaders($headers)) {
                    $this->removeUnwantedExtensionProperties($headers);

                    foreach ($headers as $headerName => $headerValue) {
                        $this->headers->pushHeader($headerName, $headerValue);
                    }
                }
            }
        }
    }

    /**
     * Remove the not required extension properties
     *
     * @param  array  $extension
     * @return void
     */
    protected function removeUnwantedExtensionProperties(&$extension)
    {
        Arr::forget($extension, ['@odata.type', 'id']);
    }

    /**
     * Check whether the given extension has header
     *
     * @param  array  $extension
     * @return bool
     */
    protected function isExtensionHoldsHeaders($extension)
    {
        return str_ends_with($extension['id'], SmtpClient::OPEN_EXTENSION_HEADERS_ID);
    }

    /**
     * Mask attachments
     *
     * @param  array  $attachments
     * @return \Illuminate\Support\Collection
     */
    protected function maskAttachments($attachments)
    {
        if (! $attachments) {
            $attachments = [];
        }

        return collect($attachments)->map(function ($attachment) {
            return $this->maskAttachment($attachment);
        })->values();
    }

    /**
     * Mask attachment
     *
     * @param  array  $attachment
     * @return \Turahe\MailClient\Client\Outlook\Attachment
     */
    protected function maskAttachment($attachment)
    {
        if (! $attachment instanceof FileAttachment) {
            $attachment = new FileAttachment($attachment);
        }

        return new Attachment($attachment);
    }

    /**
     * Parse Addresses
     *
     * @param  string  $type
     * @param  array|null  $addresses
     * @return \Turahe\Core\Common\Mail\Headers\AddressHeader|null
     */
    protected function parseAddresses($type, $addresses)
    {
        if (! $addresses || is_array($addresses) && count($addresses) === 0) {
            return null;
        }

        $all = [];

        foreach ($addresses as $address) {
            $all[$address['emailAddress']['address']] = $address['emailAddress']['name'];
        }

        return new AddressHeader($type, $all);
    }
}
