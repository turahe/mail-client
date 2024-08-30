<?php

namespace Modules\MailClient\Client\Contracts;

use Modules\MailClient\Client\Imap\Config;

interface Connectable
{
    /**
     * Connect to server
     *
     * @return mixed
     */
    public function connect();

    /**
     * Test the connection
     *
     * @return mixed
     */
    public function testConnection();

    /**
     * Get the connection config
     */
    public function getConfig(): Config;
}
