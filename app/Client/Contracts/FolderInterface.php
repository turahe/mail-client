<?php

namespace Modules\MailClient\Client\Contracts;

interface FolderInterface
{
    /**
     * Get the folder unique remote id identifier
     *
     * @return null|int|string
     */
    public function getId();

    /**
     * Get folder messages
     *
     * @return \Illuminate\Support\Collection
     */
    public function getMessages();

    /**
     * Get folder message
     *
     *
     * @param  mixed  $uid
     * @return \Modules\MailClient\Client\Contracts\MessageInterface
     *
     * @throws \Modules\MailClient\Client\Exceptions\MessageNotFoundException
     */
    public function getMessage($uid);

    /**
     * Get the folder system name
     *
     * @return string
     */
    public function getName();

    /**
     * Get the folder display name
     *
     * @return string
     */
    public function getDisplayName();

    /**
     * Set folder children.
     *
     * @param  \Modules\MailClient\Client\FolderCollection|array  $children
     * @return static
     */
    public function setChildren($children = []);

    /**
     * Get folder children.
     *
     * @return \Modules\MailClient\Client\FolderCollection|array
     */
    public function getChildren();

    /**
     * Check whether the folder has child folders
     *
     * @return bool
     */
    public function hasChildren();

    /**
     * Check whether the folder is selectable
     *
     * @return bool
     */
    public function isSelectable();

    /**
     * Check whether a message can be moved to this folder
     *
     * @return bool
     */
    public function supportMove();

    /**
     * Get the folder type
     * e.q. sent, drafts, inbox, trash
     *
     * @return string
     */
    public function getType();

    /**
     * Get the folder unique identiier
     *
     * @return \Modules\MailClient\Client\FolderIdentifier
     */
    public function identifier();

    /**
     * Check whether the folder is inbox
     *
     * @return bool
     */
    public function isInbox();

    /**
     * Check whether the folder is draft
     *
     * @return bool
     */
    public function isDraft();

    /**
     * Check whether the folder is sent
     *
     * @return bool
     */
    public function isSent();

    /**
     * Check whether the folder is spam
     *
     * @return bool
     */
    public function isSpam();

    /**
     * Check whether the folder is trash
     *
     * @return bool
     */
    public function isTrash();

    /**
     * Check whether the folder is trash or spam
     *
     * @return bool
     */
    public function isTrashOrSpam();
}
