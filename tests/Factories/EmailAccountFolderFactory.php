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
            'parent_id' => null,
            'name' => 'INBOX',
            'display_name' => 'inbox',
            'remote_id' => $this->faker->uuid,
            'email_account_id' => '01h0000000000000000000',
            'syncable' => true,
            'selectable' => true,
            'type' => 'folder',
            'support_move' => true,
        ];
    }
}
