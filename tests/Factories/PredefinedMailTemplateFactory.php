<?php

namespace Turahe\MailClient\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Turahe\MailClient\Models\PredefinedMailTemplate;

class PredefinedMailTemplateFactory extends Factory
{
    protected $model = PredefinedMailTemplate::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'subject' => $this->faker->sentence,
            'body' => '<p>' . $this->faker->paragraph . '</p>',
            'is_shared' => false,
        ];
    }

    /**
     * Indicate that the template is shared.
     */
    public function shared(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_shared' => true,
        ]);
    }

    /**
     * Indicate that the template is private (not shared).
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_shared' => false,
        ]);
    }



    /**
     * Create a welcome email template.
     */
    public function welcome(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Welcome Email',
            'subject' => 'Welcome to our platform!',
            'body' => '<h1>Welcome!</h1><p>We are excited to have you on board.</p>',
        ]);
    }

    /**
     * Create a follow-up email template.
     */
    public function followUp(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Follow Up Email',
            'subject' => 'Following up on our conversation',
            'body' => '<p>I wanted to follow up on our recent conversation.</p>',
        ]);
    }

    /**
     * Create a newsletter template.
     */
    public function newsletter(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Newsletter Template',
            'subject' => 'Monthly Newsletter - ' . now()->format('F Y'),
            'body' => '<h2>Newsletter</h2><p>Here are the latest updates...</p>',
        ]);
    }

    /**
     * Create a thank you email template.
     */
    public function thankYou(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Thank You Email',
            'subject' => 'Thank you!',
            'body' => '<p>Thank you for your business. We appreciate you!</p>',
        ]);
    }

    /**
     * Create a template with plain text body.
     */
    public function plainText(): static
    {
        return $this->state(fn (array $attributes) => [
            'body' => $this->faker->paragraph,
        ]);
    }

    /**
     * Create a template with rich HTML content.
     */
    public function richHtml(): static
    {
        return $this->state(fn (array $attributes) => [
            'body' => '
                <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; }
                            .header { background-color: #f8f9fa; padding: 20px; }
                            .content { padding: 20px; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1>' . $this->faker->sentence . '</h1>
                        </div>
                        <div class="content">
                            <p>' . $this->faker->paragraph . '</p>
                            <ul>
                                <li>' . $this->faker->sentence . '</li>
                                <li>' . $this->faker->sentence . '</li>
                            </ul>
                        </div>
                    </body>
                </html>
            ',
        ]);
    }

    /**
     * Create a template with a long subject line.
     */
    public function longSubject(): static
    {
        return $this->state(fn (array $attributes) => [
            'subject' => $this->faker->text(200),
        ]);
    }

    /**
     * Create a template with minimal content.
     */
    public function minimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Simple Template',
            'subject' => 'Hello',
            'body' => '<p>Hello!</p>',
        ]);
    }
}
