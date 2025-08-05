<?php

namespace Turahe\MailClient\Tests\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Turahe\MailClient\Enums\ConnectionType;
use Turahe\MailClient\Models\EmailAccount;

class EmailAccountFactory extends Factory
{
    protected $model = EmailAccount::class;

    public function definition()
    {
        return [
            'email' => $this->faker->email,
            'alias_email' => $this->faker->email,
            'password' => $this->faker->password,
            'connection_type' => ConnectionType::Imap,
            'last_sync_at' => Carbon::yesterday(),
            'requires_auth' => true,
            'initial_sync_from' => Carbon::yesterday(),
            'sent_folder_id' => 2,
            'trash_folder_id' => 2,
            'create_contact' => true,
            // imap
            'validate_cert' => true,
            'username' => $this->faker->userName,
            'imap_server' => $this->faker->domainName,
            'imap_port' => 1234,
            'imap_encryption' => 'tls',
            'smtp_server' => $this->faker->domainName,
            'smtp_port' => 123,
            'smtp_encryption' => 'tls',
            // polymorphic relationship
            'model_id' => null,
            'model_type' => null,
        ];
    }

    /**
     * Indicate that the account belongs to a user.
     */
    public function forUser($user): static
    {
        return $this->state(fn (array $attributes) => [
            'model_id' => $user->id,
            'model_type' => $user->getMorphClass(),
        ]);
    }

    /**
     * Indicate that the account is shared (no user).
     */
    public function shared(): static
    {
        return $this->state(fn (array $attributes) => [
            'model_id' => null,
            'model_type' => null,
        ]);
    }
}
