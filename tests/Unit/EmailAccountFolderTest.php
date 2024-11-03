<?php

namespace Turahe\MailClient\Tests\Unit;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Turahe\MailClient\Tests\Models\EmailAccount;
use Turahe\MailClient\Tests\Models\EmailAccountFolder;
use Turahe\MailClient\Tests\TestCase;

class EmailAccountFolderTest extends TestCase
{
    use WithFaker;

    protected $emailAccount;

    public function setUp(): void
    {
        parent::setUp();
        $this->emailAccount = EmailAccount::factory()->create();
    }

    #[Test]
    public function it_can_list_all_email_account_folder_folders(): void
    {

        EmailAccountFolder::factory(13)->create([
            'email_account_id' => $this->emailAccount->getKey(),
        ]);

        $this->assertInstanceOf(Collection::class, EmailAccountFolder::all());
        $this->assertCount(13, EmailAccountFolder::all()); // +1 in the TestCase
    }

    #[Test]
    public function it_can_delete_the_email_account_folder(): void
    {
        $email = EmailAccountFolder::factory()->create([
            'email_account_id' => $this->emailAccount->getKey(),
        ]);

        $deleted = $email->delete();

        $this->assertTrue($deleted);
        $this->assertSoftDeleted('email_account_folders', ['id' => $email->getKey()]);
    }

    #[Test]
    public function it_can_update_the_email_account_folder(): void
    {

        $email = EmailAccountFolder::factory()->create([
            'email_account_id' => $this->emailAccount->getKey(),
        ]);

        $data = [
            'name' => 'INBOX',
            'display_name' => 'inbox',
        ];

        $updated = $email->update($data);

        $bank = EmailAccountFolder::where('name', $data['name'])->first();

        $this->assertTrue($updated);
        $this->assertEquals($data['name'], $bank->name);
        $this->assertEquals($data['display_name'], $bank->display_name);
    }

    #[Test]
    public function it_can_create_a_email_account_folder(): void
    {
        $data = [
            //            'parent_id' => '',
            'name' => 'INBOX',
            'display_name' => 'inbox',
            'remote_id' => 1,
            'email_account_id' => $this->emailAccount->getKey(),
            'syncable' => true,
            'selectable' => true,
            'type' => 'folder',
            'support_move' => true,
        ];

        $folder = EmailAccountFolder::create($data);

        $this->assertEquals($data['name'], $folder->name);

    }

    #[Test]
    public function it_errors_creating_the_email_account_folder(): void
    {
        $this->expectException(\Exception::class);

        EmailAccountFolder::create([]);
    }
}
