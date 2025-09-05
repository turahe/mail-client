<?php

namespace Turahe\MailClient\Tests\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Turahe\MailClient\Models\ScheduledEmail;

class ScheduledEmailFactory extends Factory
{
    protected $model = ScheduledEmail::class;

    public function definition()
    {
        return [
            'email_account_id' => '01h0000000000000000000',
            'subject' => $this->faker->sentence,
            'html_body' => $this->faker->paragraph,
            'to' => [$this->faker->email],
            'cc' => [],
            'bcc' => [],
            'type' => 'email',
            'scheduled_at' => Carbon::now()->addHour(),
            'sent_at' => null,
            'failed_at' => null,
            'status' => 'pending',
            'fail_reason' => null,
            'retries' => 0,
            'retry_after' => null,
            'related_message_id' => null,
            'associations' => [],
        ];
    }

    /**
     * Indicate that the email is scheduled for the past.
     */
    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => Carbon::now()->subHour(),
        ]);
    }

    /**
     * Indicate that the email is scheduled for the future.
     */
    public function future(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => Carbon::now()->addHour(),
        ]);
    }

    /**
     * Indicate that the email has been sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'sent_at' => Carbon::now(),
            'status' => 'sent',
        ]);
    }

    /**
     * Indicate that the email has failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'fail_reason' => $this->faker->sentence,
            'failed_at' => Carbon::now(),
        ]);
    }

    /**
     * Indicate that the email is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the email is sending.
     */
    public function sending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sending',
        ]);
    }

    /**
     * Set retry information.
     */
    public function withRetry($retries = 1, $retryAfter = null): static
    {
        return $this->state(fn (array $attributes) => [
            'retries' => $retries,
            'retry_after' => $retryAfter ?: Carbon::now()->addMinutes(30),
        ]);
    }
}
