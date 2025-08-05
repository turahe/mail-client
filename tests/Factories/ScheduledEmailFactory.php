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
            'email_account_id' => 1,
            'subject' => $this->faker->sentence,
            'body' => $this->faker->paragraph,
            'to_addresses' => json_encode([$this->faker->email]),
            'cc_addresses' => json_encode([]),
            'bcc_addresses' => json_encode([]),
            'from_address' => $this->faker->email,
            'from_name' => $this->faker->name,
            'reply_to_addresses' => json_encode([]),
            'scheduled_at' => Carbon::now()->addHour(),
            'sent_at' => null,
            'status' => 'pending',
            'error_message' => null,
            'attempts' => 0,
            'max_attempts' => 3,
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
            'error_message' => $this->faker->sentence,
        ]);
    }
} 