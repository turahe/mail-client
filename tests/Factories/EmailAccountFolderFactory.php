<?php

namespace Turahe\MailClient\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Turahe\MailClient\Models\EmailAccountFolder;

class EmailAccountFolderFactory extends Factory
{
    protected $model = EmailAccountFolder::class;

    public function definition(): array
    {
        return [
            //            'parent_id' => '',
            'name' => 'INBOX',
            'display_name' => 'inbox',
            'remote_id' => 1,
            //            'email_account_id' => '',
            'syncable' => true,
            'selectable' => true,
            'type' => 'folder',
            'support_move' => true,
        ];
    }
}
