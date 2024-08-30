<?php

namespace Modules\MailClient\Client\Contracts;

use Modules\MailClient\Client\FolderIdentifier;

interface ImapInterface
{
    /**
     * Get account folder
     *
     *
     * @param  string|int  $folder  Folder identifier
     * @return \App\MailClient\Client\Contracts\Masks\Folder
     *
     * @throws \Modules\MailClient\Client\Exceptions\FolderNotFoundException
     */
    public function getFolder($folder);

    /**
     * Retrieve the account available folders from remote server
     *
     * @return \Modules\MailClient\Client\FolderCollection
     */
    public function retrieveFolders();

    /**
     * Provides the account folders
     *
     * @return \Modules\MailClient\Client\FolderCollection
     */
    public function getFolders();

    /**
     * Get message by message identifier
     *
     *
     * @param  mixed  $id
     * @param  null|\Modules\MailClient\Client\FolderIdentifier  $folder  The folder identifier if necessary
     * @return \Modules\MailClient\Client\Contracts\MessageInterface
     *
     * @throws \Modules\MailClient\Client\Exceptions\MessageNotFoundException
     */
    public function getMessage($id, ?FolderIdentifier $folder = null);

    /**
     * Move a given message to a given folder
     *
     * @return bool
     */
    public function moveMessage(MessageInterface $message, FolderInterface $folder);

    /**
     * Batch move messages to a given folder
     *
     * @param  array  $messages
     * @return bool|array
     *
     * If the method return array, it should return maps of old remote_id's with new one
     *
     * [
     *  $old => $new
     * ]
     */
    public function batchMoveMessages($messages, FolderInterface $to, FolderInterface $from);

    /**
     * Permanently batch delete messages
     *
     * @param  array  $messages
     * @return void
     */
    public function batchDeleteMessages($messages);

    /**
     * Batch mark as read messages
     *
     * @param  array  $messages
     * @return bool
     *
     * @throws \Modules\MailClient\Client\Exceptions\ConnectionErrorException
     * @throws \Modules\MailClient\Client\Exceptions\FolderNotFoundException
     */
    public function batchMarkAsRead($messages, ?FolderIdentifier $folder = null);

    /**
     * Batch mark as unread messages
     *
     * @param  array  $messages
     * @return bool
     *
     * @throws \Modules\MailClient\Client\Exceptions\ConnectionErrorException
     * @throws \Modules\MailClient\Client\Exceptions\FolderNotFoundException
     */
    public function batchMarkAsUnread($messages, ?FolderIdentifier $folder = null);

    /**
     * Set the IMAP sent folder
     */
    public function setSentFolder(FolderInterface $folder): static;

    /**
     * Get the sent folder
     *
     * @return \Modules\MailClient\Client\Contracts\FolderInterface
     */
    public function getSentFolder();

    /**
     * Set the IMAP trash folder
     *
     * @return static
     */
    public function setTrashFolder(FolderInterface $folder);

    /**
     * Get the trash folder
     *
     * @return \Modules\MailClient\Client\Contracts\FolderInterface
     */
    public function getTrashFolder();

    /**
     * Get the latest message from the sent folder
     *
     * @return \Modules\MailClient\Client\Contracts\MessageInterface|null
     *
     * @throws \Modules\MailClient\Client\Exceptions\ConnectionErrorException
     */
    public function getLatestSentMessage();
}
