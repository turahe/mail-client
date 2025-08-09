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
            'email_account_id' => '01h0000000000000000000',
            'remote_id' => $this->faker->unique()->uuid,
            'message_id' => $this->faker->unique()->uuid,
            'subject' => $this->faker->sentence,
            'html_body' => '<p>' . $this->faker->paragraph . '</p>',
            'text_body' => $this->faker->paragraph,
            'is_read' => false,
            'is_draft' => false,
            'date' => Carbon::now(),
            'is_sent_via_app' => false,
            'hash' => hash('sha256', $this->faker->text),
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
     * Indicate that the message is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => true,
        ]);
    }

    /**
     * Indicate that the message was sent via app.
     */
    public function sentViaApp(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_sent_via_app' => true,
        ]);
    }

    /**
     * Set a specific date for the message.
     */
    public function withDate($date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }
} 