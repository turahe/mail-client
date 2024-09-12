<?php

namespace Turahe\MailClient\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Turahe\MailClient\Client\FolderIdentifier;

class FolderIdentifierTest extends TestCase
{
    public function test_folder_identifier(): void
    {
        $identifier = new FolderIdentifier('id', 'INBOX');

        $this->assertSame('id', $identifier->key);
        $this->assertSame('INBOX', $identifier->value);
    }
}
