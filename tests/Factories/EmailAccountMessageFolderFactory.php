<?php

namespace Turahe\MailClient\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Turahe\MailClient\Models\EmailAccountMessageFolder;

class EmailAccountMessageFolderFactory extends Factory
{
    protected $model = EmailAccountMessageFolder::class;

    public function definition()
    {
        return [
            'message_id' => '01h0000000000000000000',
            'folder_id' => '01h0000000000000000000',
        ];
    }

    /**
     * Set the message ID for the relationship.
     */
    public function forMessage($message): static
    {
        return $this->state(fn (array $attributes) => [
            'message_id' => is_object($message) ? $message->id : $message,
        ]);
    }

    /**
     * Set the folder ID for the relationship.
     */
    public function forFolder($folder): static
    {
        return $this->state(fn (array $attributes) => [
            'folder_id' => is_object($folder) ? $folder->id : $folder,
        ]);
    }

    /**
     * Set both message and folder for the relationship.
     */
    public function forMessageAndFolder($message, $folder): static
    {
        return $this->state(fn (array $attributes) => [
            'message_id' => is_object($message) ? $message->id : $message,
            'folder_id' => is_object($folder) ? $folder->id : $folder,
        ]);
    }
}
