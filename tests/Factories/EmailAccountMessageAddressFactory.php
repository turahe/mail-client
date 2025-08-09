<?php

namespace Turahe\MailClient\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Turahe\MailClient\Models\EmailAccountMessageAddress;

class EmailAccountMessageAddressFactory extends Factory
{
    protected $model = EmailAccountMessageAddress::class;

    public function definition()
    {
        return [
            'message_id' => '01h0000000000000000000',
            'address' => $this->faker->email,
            'name' => $this->faker->name,
            'address_type' => $this->faker->randomElement(['from', 'to', 'cc', 'bcc', 'replyTo', 'sender']),
        ];
    }

    /**
     * Create an address for a specific message.
     */
    public function forMessage($message): static
    {
        return $this->state(fn (array $attributes) => [
            'message_id' => is_object($message) ? $message->id : $message,
        ]);
    }

    /**
     * Indicate that the address is a "from" address.
     */
    public function from(): static
    {
        return $this->state(fn (array $attributes) => [
            'address_type' => 'from',
        ]);
    }

    /**
     * Indicate that the address is a "to" address.
     */
    public function to(): static
    {
        return $this->state(fn (array $attributes) => [
            'address_type' => 'to',
        ]);
    }

    /**
     * Indicate that the address is a "cc" address.
     */
    public function cc(): static
    {
        return $this->state(fn (array $attributes) => [
            'address_type' => 'cc',
        ]);
    }

    /**
     * Indicate that the address is a "bcc" address.
     */
    public function bcc(): static
    {
        return $this->state(fn (array $attributes) => [
            'address_type' => 'bcc',
        ]);
    }

    /**
     * Indicate that the address is a "replyTo" address.
     */
    public function replyTo(): static
    {
        return $this->state(fn (array $attributes) => [
            'address_type' => 'replyTo',
        ]);
    }

    /**
     * Indicate that the address is a "sender" address.
     */
    public function sender(): static
    {
        return $this->state(fn (array $attributes) => [
            'address_type' => 'sender',
        ]);
    }

    /**
     * Indicate that the address has no name.
     */
    public function withoutName(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => null,
        ]);
    }
}
