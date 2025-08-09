<?php

namespace Turahe\MailClient\Tests\Unit;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\Factories\UserFactory;
use PHPUnit\Framework\Attributes\Test;
use Turahe\MailClient\Client\FolderIdentifier;
use Turahe\MailClient\Enums\ConnectionType;
use Turahe\MailClient\Models\EmailAccount;
use Turahe\MailClient\Models\EmailAccountFolder;
use Turahe\MailClient\Tests\Factories\EmailAccountFactory;
use Turahe\MailClient\Tests\Factories\EmailAccountFolderFactory;
use Turahe\MailClient\Tests\TestCase;

class EmailAccountFolderTest extends TestCase
{
    use WithFaker;

    protected $testUser;
    protected $testAccount;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testUser = UserFactory::new()->createOne();
        $this->testAccount = EmailAccountFactory::new()->forUser($this->testUser)->create();
    }

    #[Test]
    public function it_can_list_all_email_account_folders(): void
    {
        EmailAccountFolderFactory::new()->count(5)->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        $this->assertInstanceOf(Collection::class, EmailAccountFolder::all());
        $this->assertCount(5, EmailAccountFolder::all());
    }

    #[Test]
    public function it_can_create_an_email_account_folder(): void
    {
        $data = [
            'name' => 'INBOX',
            'display_name' => 'Inbox',
            'remote_id' => $this->faker->uuid,
            'email_account_id' => $this->testAccount->id,
            'syncable' => true,
            'selectable' => true,
            'type' => 'inbox',
            'support_move' => true,
        ];

        $folder = EmailAccountFolder::create($data);

        $this->assertInstanceOf(EmailAccountFolder::class, $folder);
        $this->assertEquals($data['name'], $folder->name);
        $this->assertEquals($data['display_name'], $folder->display_name);
        $this->assertEquals($data['remote_id'], $folder->remote_id);
        $this->assertEquals($data['email_account_id'], $folder->email_account_id);
        $this->assertTrue($folder->syncable);
        $this->assertTrue($folder->selectable);
        $this->assertTrue($folder->support_move);
    }

    #[Test]
    public function it_can_update_email_account_folder(): void
    {
        $folder = EmailAccountFolderFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        $newName = 'Updated Folder Name';
        $updated = $folder->update(['display_name' => $newName]);

        $this->assertTrue($updated);
        $this->assertEquals($newName, $folder->fresh()->display_name);
    }

    #[Test]
    public function it_can_delete_email_account_folder(): void
    {
        $folder = EmailAccountFolderFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        $deleted = $folder->delete();

        $this->assertTrue($deleted);
        $this->assertSoftDeleted('email_account_folders', ['id' => $folder->id]);
    }

    #[Test]
    public function it_belongs_to_email_account(): void
    {
        $folder = EmailAccountFolderFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        $this->assertInstanceOf(EmailAccount::class, $folder->account);
        $this->assertEquals($this->testAccount->id, $folder->account->id);
    }

    #[Test]
    public function it_can_get_folder_identifier_for_imap(): void
    {
        $this->testAccount->update(['connection_type' => ConnectionType::Imap]);
        $folder = EmailAccountFolderFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'name' => 'INBOX',
        ]);

        $identifier = $folder->identifier();

        $this->assertInstanceOf(FolderIdentifier::class, $identifier);
        $this->assertEquals('name', $identifier->key);
        $this->assertEquals('INBOX', $identifier->value);
    }

    #[Test]
    public function it_can_get_folder_identifier_for_outlook(): void
    {
        $this->testAccount->update(['connection_type' => ConnectionType::Outlook]);
        $remoteId = $this->faker->uuid;
        $folder = EmailAccountFolderFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'remote_id' => $remoteId,
        ]);

        $identifier = $folder->identifier();

        $this->assertInstanceOf(FolderIdentifier::class, $identifier);
        $this->assertEquals('id', $identifier->key);
        $this->assertEquals($remoteId, $identifier->value);
    }

    #[Test]
    public function it_can_mark_folder_as_not_selectable(): void
    {
        $folder = EmailAccountFolderFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'syncable' => true,
            'selectable' => true,
        ]);

        $folder->markAsNotSelectable();

        $this->assertFalse($folder->fresh()->syncable);
        $this->assertFalse($folder->fresh()->selectable);
    }

    #[Test]
    public function it_can_count_unread_messages(): void
    {
        $folder = EmailAccountFolderFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        $count = $folder->countUnreadMessages();

        $this->assertEquals(0, $count);
    }

    #[Test]
    public function it_can_count_read_messages(): void
    {
        $folder = EmailAccountFolderFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        $count = $folder->countReadMessages();

        $this->assertEquals(0, $count);
    }

    #[Test]
    public function it_has_display_name_attribute(): void
    {
        $folder = EmailAccountFolderFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'display_name' => 'Test Folder',
        ]);

        $this->assertEquals('Test Folder', $folder->display_name);
    }

    #[Test]
    public function it_uses_correct_table(): void
    {
        $folder = new EmailAccountFolder();
        $this->assertEquals('email_account_folders', $folder->getTable());
    }

    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $expectedFillable = [
            'parent_id', 'name', 'display_name', 'remote_id',
            'email_account_id', 'syncable', 'selectable', 'type', 'support_move',
        ];

        $folder = new EmailAccountFolder();
        $this->assertEquals($expectedFillable, $folder->getFillable());
    }

    #[Test]
    public function it_casts_attributes_correctly(): void
    {
        $folder = new EmailAccountFolder();
        $casts = $folder->getCasts();

        $this->assertEquals('boolean', $casts['selectable']);
        $this->assertEquals('boolean', $casts['syncable']);
        $this->assertEquals('boolean', $casts['support_move']);
        $this->assertEquals('int', $casts['parent_id']);
        $this->assertEquals('string', $casts['email_account_id']);
    }
}