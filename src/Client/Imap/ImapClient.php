<?php

namespace Turahe\MailClient\Client\Imap;

use Ddeboer\Imap\Exception\MailboxDoesNotExistException;
use Ddeboer\Imap\Server;
use Exception;
use Illuminate\Support\Str;
use Turahe\MailClient\Client\AbstractImapClient;
use Turahe\MailClient\Client\Contracts\Connectable;
use Turahe\MailClient\Client\Contracts\FolderInterface;
use Turahe\MailClient\Client\Contracts\MessageInterface;
use Turahe\MailClient\Client\Exceptions\ConnectionErrorException;
use Turahe\MailClient\Client\Exceptions\FolderNotFoundException;
use Turahe\MailClient\Client\FolderCollection;
use Turahe\MailClient\Client\FolderIdentifier;

class ImapClient extends AbstractImapClient implements Connectable
{
    /**
     * Ignored folders by name
     *
     * @var array
     */
    protected $excludeFolders = [
        'Bulk Mail',
    ];

    /**
     * @var \Ddeboer\Imap\Connection
     */
    protected $connection;

    /**
     * Create new ImapClient instance.
     */
    public function __construct(protected Config $config) {}

    /**
     * Get folder by name
     *
     *
     * @param  string  $name
     * @return \Turahe\MailClient\Client\Imap\Folder|null
     *
     * @throws \Turahe\MailClient\Client\Exceptions\FolderNotFoundException
     */
    public function getFolder($name)
    {
        $this->ensureConnected();

        try {
            return $this->maskFolder($this->connection->getMailbox($name));
        } catch (MailboxDoesNotExistException $e) {
            throw new FolderNotFoundException;
        }
    }

    /**
     * Retrieve the account available folders from remote server
     *
     * @param  string|null  $parentFolder
     * @return \Turahe\MailClient\Client\FolderCollection
     */
    public function retrieveFolders($parentFolder = null)
    {
        $this->ensureConnected();

        return $this->maskFolders($this->connection->getMailboxes())
            ->createTreeFromDelimiter();
    }

    /**
     * Move a given message to a given folder
     *
     * @param  \Turahe\MailClient\Client\Imap\Message  $message
     * @param  \Turahe\MailClient\Client\Imap\Folder  $folder
     * @return bool
     */
    public function moveMessage(MessageInterface $message, FolderInterface $folder)
    {
        $this->ensureConnected();

        $message->getEntity()->move($folder->getEntity());

        $this->connection->expunge();

        return true;
    }

    /**
     * Batch move messages to a given folder
     *
     * @param  array  $messages
     * @param  \Turahe\MailClient\Client\Imap\Folder  $from
     * @param  \Turahe\MailClient\Client\Imap\Folder  $to
     * @return array
     */
    public function batchMoveMessages($messages, FolderInterface $to, FolderInterface $from)
    {
        $this->ensureConnected();

        $nextUid = $to->getNextUid();

        $from->getEntity()->move($messages, $to->getEntity());

        $this->connection->expunge();

        $maps = [];

        foreach ($messages as $oldUid) {
            $maps[$oldUid] = $nextUid;
            $nextUid++;
        }

        return $maps;
    }

    /**
     * Permanently batch delete messages
     *
     * @param  array  $messages
     * @return void
     */
    public function batchDeleteMessages($messages)
    {
        $this->ensureConnected();

        /** @var \Turahe\MailClient\Client\Imap\Folder * */
        $trashFolder = $this->getTrashFolder();
        $messages = $trashFolder->getEntity()->getMessageSequence(implode(',', $messages));

        foreach ($messages as $message) {
            $message->delete();
        }

        $this->connection->expunge();
    }

    /**
     * Batch mark as read messages
     *
     * @param  array  $messages
     * @return bool
     *
     * @throws \Turahe\MailClient\Client\Exceptions\ConnectionErrorException
     * @throws \Turahe\MailClient\Client\Exceptions\FolderNotFoundException
     */
    public function batchMarkAsRead($messages, ?FolderIdentifier $folder = null)
    {
        return $this->getFolder($folder->value)->setFlag('\\Seen', $messages);
    }

    /**
     * Batch mark as unread messages
     *
     * @param  array  $messages
     * @return bool
     *
     * @throws \Turahe\MailClient\Client\Exceptions\ConnectionErrorException
     * @throws \Turahe\MailClient\Client\Exceptions\FolderNotFoundException
     */
    public function batchMarkAsUnread($messages, ?FolderIdentifier $folder = null)
    {
        return $this->getFolder($folder->value)->clearFlag('\\Seen', $messages);
    }

    /**
     * Get message by message identifier
     *
     *
     * @param  int  $id
     * @return \Turahe\MailClient\Client\Imap\Message
     *
     * @throws \Turahe\MailClient\Client\Exceptions\MessageNotFoundException|\Turahe\MailClient\Client\Exceptions\FolderNotFoundException
     */
    public function getMessage($id, ?FolderIdentifier $folder = null)
    {
        return $this->getFolder($folder->value)->getMessage($id);
    }

    /**
     * Add a just sent message into the sent folder
     *
     * NOTE: Gmail IMAP accounts automatically add the message to sent folder
     *
     * For Gmail, even if we invoke this function, the message won't be added
     * multiple times to the sent folder
     *
     * @param  string  $messageMIME
     * @return bool
     */
    public function addMessageToSentFolder($messageMIME)
    {
        /** @var \Turahe\MailClient\Client\Imap\Folder * */
        if ($folder = $this->getSentFolder()) {
            return $folder->addMessage($messageMIME, '\\Seen');
        }
    }

    /**
     * Get the latest message from the sent folder
     *
     * @return \Turahe\MailClient\Client\Imap\Message|null
     */
    public function getLatestSentMessage()
    {
        /** @var \Turahe\MailClient\Client\Imap\Folder * */
        if ($folder = $this->getSentFolder()) {
            return $folder->getLatestMessage();
        }
    }

    /**
     * Connect to IMAP
     *
     * @return mixed
     */
    public function connect()
    {
        $server = new Server(
            $this->getConfig()->host(),
            $this->getConfig()->port(),
            $this->getConnectionFlags()
        );

        $username = $this->getConfig()->username() ?? $this->getConfig()->email();

        return $this->connection = $server->authenticate($username, $this->getConfig()->password());
    }

    /**
     * Test the IMAP client connection
     *
     * @return void
     */
    public function testConnection()
    {
        $this->connect();
    }

    /**
     * Get the IMAP configuration
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Ensure that the client is connected
     *
     * @return \Ddeboer\Imap\Connection
     *
     * @throws \Turahe\MailClient\Client\Exceptions\ConnectionErrorException
     */
    protected function ensureConnected()
    {
        if ($this->connection) {
            return $this->connection;
        }

        try {
            return $this->connect();
        } catch (Exception $e) {
            throw new ConnectionErrorException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get full address of mailbox.
     *
     * @return string
     */
    protected function getConnectionFlags()
    {
        $flags = '/imap';

        if (in_array($this->getConfig()->encryption(), ['tls', 'notls', 'ssl'])) {
            $flags .= '/'.$this->getConfig()->encryption();
        } elseif ($this->getConfig()->encryption() === 'starttls') {
            $flags .= '/tls';
        }

        if (! $this->getConfig()->validateCertificate()) {
            $flags .= '/novalidate-cert';
        } else {
            $flags .= '/validate-cert';
        }

        return $flags;
    }

    /**
     * Mask folders
     *
     * @param  array  $folders
     * @return \Turahe\MailClient\Client\FolderCollection
     */
    protected function maskFolders($folders)
    {
        if (! $folders) {
            $folders = [];
        }

        $draft = null;

        return (new FolderCollection($folders))->map(function ($folder) {
            return $this->maskFolder($folder);
        })->reject(function ($folder) use (&$draft) {
            // We will exclude the draft and all sub folders in the draft folder
            // e.q. Drafts and Drafts/Templates
            // the isDraft method will return true only for the main parent folder Draft
            if ($folder->isDraft() ||
            ($draft && Str::startsWith($folder->getName(), $draft->getName()))) {
                $draft = $folder;

                return true;
            }

            return in_array($folder->getName(), $this->excludeFolders);
        })->values();
    }

    /**
     * Mask folder
     *
     * @param  mixed  $folder
     * @return \Turahe\MailClient\Client\Imap\Folder
     */
    protected function maskFolder($folder)
    {
        return new Folder($folder);
    }
}
