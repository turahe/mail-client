<?php

namespace Turahe\MailClient\Tests\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Turahe\MailClient\Models\EmailAccountMessage;

class EmailAccountMessageFactory extends Factory
{
    protected $model = EmailAccountMessage::class;

    public function definition()
    {
        return [
            'email_account_id' => 1,
            'remote_id' => $this->faker->unique()->uuid,
            'subject' => $this->faker->sentence,
            'body' => $this->faker->paragraph,
            'from_address' => $this->faker->email,
            'from_name' => $this->faker->name,
            'to_addresses' => json_encode([$this->faker->email]),
            'cc_addresses' => json_encode([]),
            'bcc_addresses' => json_encode([]),
            'reply_to_addresses' => json_encode([]),
            'received_at' => Carbon::now(),
            'is_read' => false,
            'is_flagged' => false,
            'has_attachments' => false,
            'size' => $this->faker->numberBetween(1000, 100000),
            'message_id' => $this->faker->unique()->uuid,
            'in_reply_to' => null,
            'references' => json_encode([]),
            'headers' => json_encode([]),
        ];
    }

    /**
     * Indicate that the message is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
        ]);
    }

    /**
     * Indicate that the message is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => false,
        ]);
    }

    /**
     * Indicate that the message is flagged.
     */
    public function flagged(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_flagged' => true,
        ]);
    }

    /**
     * Indicate that the message has attachments.
     */
    public function withAttachments(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_attachments' => true,
        ]);
    }
} 