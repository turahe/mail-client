<?php

namespace Turahe\MailClient\Tests\Unit;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\Factories\UserFactory;
use PHPUnit\Framework\Attributes\Test;
use Turahe\MailClient\Enums\ConnectionType;
use Turahe\MailClient\Models\EmailAccount;
use Turahe\MailClient\Tests\Factories\EmailAccountFactory;
use Turahe\MailClient\Tests\TestCase;

class EmailAccountTest extends TestCase
{
    use WithFaker;

    protected $testModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testModel = UserFactory::new()->createOne();

    }

    #[Test]
    public function it_can_list_all_email_accounts(): void
    {

        EmailAccountFactory::new()->count(13)->create([
            'model_id' => $this->testModel->getKey(),
            'model_type' => $this->testModel->getMorphClass(),
        ]);

        $this->assertInstanceOf(Collection::class, EmailAccount::all());
        $this->assertCount(13, EmailAccount::all()); // +1 in the TestCase
    }

    #[Test]
    public function it_can_delete_the_email_account(): void
    {
        $email = EmailAccountFactory::new()->create([
            'model_id' => $this->testModel->getKey(),
            'model_type' => $this->testModel->getMorphClass(),
        ]);

        $deleted = $email->delete();

        $this->assertTrue($deleted);
        $this->assertSoftDeleted('email_accounts', ['id' => $email->getKey()]);
    }

    #[Test]
    public function it_can_update_the_email_account(): void
    {

        $email = EmailAccountFactory::new()->create([
            'model_id' => $this->testModel->getKey(),
            'model_type' => $this->testModel->getMorphClass(),
        ]);

        $data = [
            'email' => $this->faker->email,
            'alias_email' => $this->faker->email,
        ];

        $updated = $email->update($data);

        $bank = EmailAccount::where('email', $data['email'])->first();

        $this->assertTrue($updated);
        $this->assertEquals($data['email'], $bank->email);
        $this->assertEquals($data['alias_email'], $bank->alias_email);
    }

    #[Test]
    public function it_can_create_a_email_account(): void
    {
        $data = [

            'model_id' => $this->testModel->getKey(),
            'model_type' => $this->testModel->getMorphClass(),
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
        ];

        $email = \Turahe\MailClient\Models\EmailAccount::create($data);

        $this->assertEquals($data['email'], $email->email);
        $this->assertEquals($data['alias_email'], $email->alias_email);
        $this->assertEquals($data['password'], $email->password);
        $this->assertEquals($data['connection_type'], $email->connection_type);
        $this->assertEquals($data['last_sync_at'], $email->last_sync_at);
        $this->assertEquals($data['requires_auth'], $email->requires_auth);
        $this->assertEquals($data['initial_sync_from'], $email->initial_sync_from);
        $this->assertEquals($data['sent_folder_id'], $email->sent_folder_id);
        $this->assertEquals($data['trash_folder_id'], $email->trash_folder_id);
        $this->assertEquals($data['create_contact'], $email->create_contact);

        $this->assertEquals($data['validate_cert'], $email->validate_cert);
        $this->assertEquals($data['username'], $email->username);
        $this->assertEquals($data['imap_server'], $email->imap_server);
        $this->assertEquals($data['imap_port'], $email->imap_port);
        $this->assertEquals($data['imap_encryption'], $email->imap_encryption);
        $this->assertEquals($data['smtp_server'], $email->smtp_server);
        $this->assertEquals($data['smtp_port'], $email->smtp_port);
        $this->assertEquals($data['smtp_encryption'], $email->smtp_encryption);

    }

    #[Test]
    public function it_errors_creating_the_email_account(): void
    {
        $this->expectException(Exception::class);

        EmailAccount::create([]);
    }
}
