<?php

namespace Turahe\MailClient\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Turahe\MailClient\Models\MessageLinksClick;

class MessageLinksClickFactory extends Factory
{
    protected $model = MessageLinksClick::class;

    public function definition()
    {
        return [
            'url' => $this->faker->url,
            'message_id' => '01h0000000000000000000',
        ];
    }

    /**
     * Create a click for a specific URL.
     */
    public function forUrl(string $url): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => $url,
        ]);
    }

    /**
     * Create a click for a specific message.
     */
    public function forMessage($message): static
    {
        return $this->state(fn (array $attributes) => [
            'message_id' => is_object($message) ? $message->id : $message,
        ]);
    }

    /**
     * Create a click for an HTTPS URL.
     */
    public function https(): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => 'https://'.$this->faker->domainName.'/'.$this->faker->slug,
        ]);
    }

    /**
     * Create a click for an HTTP URL.
     */
    public function http(): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => 'http://'.$this->faker->domainName.'/'.$this->faker->slug,
        ]);
    }

    /**
     * Create a click for a URL with query parameters.
     */
    public function withParameters(): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => $this->faker->url.'?param1=value1&param2=value2',
        ]);
    }

    /**
     * Create a click for a long URL.
     */
    public function longUrl(): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => 'https://example.com/very/long/path/with/many/segments?'.
                     'param1=value1&param2=value2&param3=value3&param4=value4',
        ]);
    }
}
