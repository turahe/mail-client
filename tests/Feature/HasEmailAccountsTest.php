<?php

namespace Turahe\MailClient\Tests\Feature;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Orchestra\Testbench\Factories\UserFactory;
use Turahe\MailClient\Models\EmailAccount;
use Turahe\MailClient\Tests\Factories\EmailAccountFactory;
use Turahe\MailClient\Tests\TestCase;

class HasEmailAccountsTest extends TestCase
{
    protected $testModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testModel = UserFactory::new()->createOne();

    }

    public function test_provides_a_email_account_relation(): void
    {
        $this->assertInstanceOf(MorphMany::class, $this->testModel->emails());
        $this->assertInstanceOf(Collection::class, $this->testModel->emails);
    }

    public function test_can_model_has_email_account(): void
    {
        $email = 'wachid@outlook.com';
        $emailAccount = $this->testModel->setEmail($email);
        $this->assertDatabaseHas('email_accounts', [
            'model_id' => $this->testModel->getKey(),
            'model_type' => $this->testModel->getMorphClass(),
            'email' => $email,
        ]);
        $this->assertEquals($this->testModel->getKey(), $emailAccount->model_id);
        $this->assertEquals($this->testModel->getMorphClass(), $emailAccount->model_type);
        $this->assertInstanceOf(EmailAccount::class, $emailAccount);
    }

    public function test_can_model_delete_email_account(): void
    {
        $this->testModel->emails()->saveMany(EmailAccountFactory::new()->count(3)->make());
        $deleted = $this->testModel->delete();

        EmailAccount::withTrashed()->get()->each(function (EmailAccount $emailAccount) {
            $this->assertDatabaseHas('email_accounts', [
                'model_id' => $this->testModel->getKey(),
                'model_type' => $this->testModel->getMorphClass(),
                'email' => $emailAccount->email,
            ]);
            $this->assertEquals($this->testModel->getKey(), $emailAccount->model_id);
            $this->assertEquals($this->testModel->getMorphClass(), $emailAccount->model_type);
            $this->assertNotNull($emailAccount->deleted_at);
        });
        $this->assertTrue($deleted);

    }
}
