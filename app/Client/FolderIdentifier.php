<?php

namespace Modules\MailClient\Client;

class FolderIdentifier
{
    /**
     * Initialize new FolderIdentifier class
     *
     * @param  string  $key
     * @param  mixed  $value
     */
    public function __construct(public $key, public $value) {}
}
