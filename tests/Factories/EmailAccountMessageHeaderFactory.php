<?php

namespace Turahe\MailClient\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Turahe\MailClient\Models\EmailAccountMessageHeader;

class EmailAccountMessageHeaderFactory extends Factory
{
    protected $model = EmailAccountMessageHeader::class;

    public function definition()
    {
        return [
            'message_id' => '01h0000000000000000000',
            'name' => $this->faker->randomElement([
                'Content-Type',
                'Message-ID',
                'Date',
                'Subject',
                'From',
                'To',
                'X-Custom-Header',
            ]),
            'value' => $this->faker->sentence,
            'header_type' => $this->faker->randomElement(['standard', 'custom', 'id', 'date', 'reply', 'references']),
        ];
    }

    /**
     * Create a header for a specific message.
     */
    public function forMessage($message): static
    {
        return $this->state(fn (array $attributes) => [
            'message_id' => is_object($message) ? $message->id : $message,
        ]);
    }

    /**
     * Indicate that the header is a content type header.
     */
    public function contentType(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Content-Type',
            'value' => $this->faker->randomElement([
                'text/html; charset=utf-8',
                'text/plain; charset=utf-8',
                'multipart/mixed',
                'multipart/alternative',
            ]),
            'header_type' => 'standard',
        ]);
    }

    /**
     * Indicate that the header is a message ID header.
     */
    public function messageId(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Message-ID',
            'value' => '<'.$this->faker->uuid.'@'.$this->faker->domainName.'>',
            'header_type' => 'id',
        ]);
    }

    /**
     * Indicate that the header is an In-Reply-To header.
     */
    public function inReplyTo(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'In-Reply-To',
            'value' => '<'.$this->faker->uuid.'@'.$this->faker->domainName.'>',
            'header_type' => 'reply',
        ]);
    }

    /**
     * Indicate that the header is a References header.
     */
    public function references(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'References',
            'value' => '<'.$this->faker->uuid.'@'.$this->faker->domainName.'> <'.$this->faker->uuid.'@'.$this->faker->domainName.'>',
            'header_type' => 'references',
        ]);
    }

    /**
     * Indicate that the header is a Date header.
     */
    public function date(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Date',
            'value' => $this->faker->dateTime->format('D, d M Y H:i:s O'),
            'header_type' => 'date',
        ]);
    }

    /**
     * Indicate that the header is a custom header.
     */
    public function custom(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'X-'.$this->faker->word,
            'value' => $this->faker->sentence,
            'header_type' => 'custom',
        ]);
    }

    /**
     * Indicate that the header is a subject header.
     */
    public function subject(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Subject',
            'value' => $this->faker->sentence,
            'header_type' => 'standard',
        ]);
    }

    /**
     * Indicate that the header is a from header.
     */
    public function from(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'From',
            'value' => $this->faker->name.' <'.$this->faker->email.'>',
            'header_type' => 'standard',
        ]);
    }

    /**
     * Indicate that the header is a to header.
     */
    public function to(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'To',
            'value' => $this->faker->name.' <'.$this->faker->email.'>',
            'header_type' => 'standard',
        ]);
    }
}
