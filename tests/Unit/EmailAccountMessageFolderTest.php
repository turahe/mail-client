<?php

namespace Turahe\MailClient\Tests\Unit;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\Factories\UserFactory;
use PHPUnit\Framework\Attributes\Test;
use Turahe\MailClient\Models\EmailAccountMessageFolder;
use Turahe\MailClient\Tests\Factories\EmailAccountFactory;
use Turahe\MailClient\Tests\Factories\EmailAccountFolderFactory;
use Turahe\MailClient\Tests\Factories\EmailAccountMessageFactory;
use Turahe\MailClient\Tests\TestCase;

class EmailAccountMessageFolderTest extends TestCase
{
    use WithFaker;

    protected $testUser;

    protected $testAccount;

    protected $testFolder;

    protected $testMessage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testUser = UserFactory::new()->createOne();
        $this->testAccount = EmailAccountFactory::new()->forUser($this->testUser)->create();
        $this->testFolder = EmailAccountFolderFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);
        $this->testMessage = EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);
    }

    #[Test]
    public function it_can_list_all_email_account_message_folders(): void
    {
        EmailAccountMessageFolder::factory()->count(3)->create([
            'message_id' => $this->testMessage->id,
            'folder_id' => $this->testFolder->id,
        ]);

        $this->assertInstanceOf(Collection::class, EmailAccountMessageFolder::all());
        $this->assertCount(3, EmailAccountMessageFolder::all());
    }

    #[Test]
    public function it_can_create_an_email_account_message_folder(): void
    {
        $data = [
            'message_id' => $this->testMessage->id,
            'folder_id' => $this->testFolder->id,
        ];

        $pivot = EmailAccountMessageFolder::create($data);

        $this->assertInstanceOf(EmailAccountMessageFolder::class, $pivot);
        $this->assertEquals($data['message_id'], $pivot->message_id);
        $this->assertEquals($data['folder_id'], $pivot->folder_id);
    }

    // Note: Pivot models don't support traditional update operations
    // Instead, relationships are managed via attach/detach on the parent models
    #[Test]
    public function it_can_update_email_account_message_folder(): void
    {
        // Create initial relationship
        $this->testMessage->folders()->attach($this->testFolder->id);

        $anotherFolder = EmailAccountFolderFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        // Update relationship by detaching old and attaching new
        $this->testMessage->folders()->detach($this->testFolder->id);
        $this->testMessage->folders()->attach($anotherFolder->id);

        $this->assertDatabaseMissing('email_account_message_folders', [
            'message_id' => $this->testMessage->id,
            'folder_id' => $this->testFolder->id,
        ]);

        $this->assertDatabaseHas('email_account_message_folders', [
            'message_id' => $this->testMessage->id,
            'folder_id' => $anotherFolder->id,
        ]);
    }

    #[Test]
    public function it_can_delete_email_account_message_folder(): void
    {
        $pivot = EmailAccountMessageFolder::create([
            'message_id' => $this->testMessage->id,
            'folder_id' => $this->testFolder->id,
        ]);

        // First verify the record exists
        $this->assertDatabaseHas('email_account_message_folders', [
            'message_id' => $this->testMessage->id,
            'folder_id' => $this->testFolder->id,
        ]);

        // Delete via query builder for pivot tables
        $deleted = EmailAccountMessageFolder::where('message_id', $this->testMessage->id)
            ->where('folder_id', $this->testFolder->id)
            ->delete();

        $this->assertTrue($deleted > 0);
        $this->assertDatabaseMissing('email_account_message_folders', [
            'message_id' => $this->testMessage->id,
            'folder_id' => $this->testFolder->id,
        ]);
    }

    #[Test]
    public function it_does_not_have_timestamps(): void
    {
        $pivot = new EmailAccountMessageFolder;
        $this->assertFalse($pivot->timestamps);
    }

    #[Test]
    public function it_casts_attributes_correctly(): void
    {
        $pivot = new EmailAccountMessageFolder;
        $casts = $pivot->getCasts();

        $this->assertEquals('string', $casts['message_id']);
        $this->assertEquals('string', $casts['folder_id']);
    }

    #[Test]
    public function it_uses_correct_table(): void
    {
        $pivot = new EmailAccountMessageFolder;
        $this->assertEquals('email_account_message_folders', $pivot->getTable());
    }

    #[Test]
    public function it_is_a_pivot_model(): void
    {
        $pivot = new EmailAccountMessageFolder;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\Pivot::class, $pivot);
    }

    #[Test]
    public function it_can_relate_one_message_to_multiple_folders(): void
    {
        $folder2 = EmailAccountFolderFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);
        $folder3 = EmailAccountFolderFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        EmailAccountMessageFolder::create([
            'message_id' => $this->testMessage->id,
            'folder_id' => $this->testFolder->id,
        ]);

        EmailAccountMessageFolder::create([
            'message_id' => $this->testMessage->id,
            'folder_id' => $folder2->id,
        ]);

        EmailAccountMessageFolder::create([
            'message_id' => $this->testMessage->id,
            'folder_id' => $folder3->id,
        ]);

        $relationships = EmailAccountMessageFolder::where('message_id', $this->testMessage->id)->get();
        $this->assertCount(3, $relationships);

        $folderIds = $relationships->pluck('folder_id')->toArray();
        $this->assertContains($this->testFolder->id, $folderIds);
        $this->assertContains($folder2->id, $folderIds);
        $this->assertContains($folder3->id, $folderIds);
    }

    #[Test]
    public function it_can_relate_one_folder_to_multiple_messages(): void
    {
        $message2 = EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);
        $message3 = EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        EmailAccountMessageFolder::create([
            'message_id' => $this->testMessage->id,
            'folder_id' => $this->testFolder->id,
        ]);

        EmailAccountMessageFolder::create([
            'message_id' => $message2->id,
            'folder_id' => $this->testFolder->id,
        ]);

        EmailAccountMessageFolder::create([
            'message_id' => $message3->id,
            'folder_id' => $this->testFolder->id,
        ]);

        $relationships = EmailAccountMessageFolder::where('folder_id', $this->testFolder->id)->get();
        $this->assertCount(3, $relationships);

        $messageIds = $relationships->pluck('message_id')->toArray();
        $this->assertContains($this->testMessage->id, $messageIds);
        $this->assertContains($message2->id, $messageIds);
        $this->assertContains($message3->id, $messageIds);
    }

    #[Test]
    public function it_can_find_by_message_id(): void
    {
        $message2 = EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        EmailAccountMessageFolder::create([
            'message_id' => $this->testMessage->id,
            'folder_id' => $this->testFolder->id,
        ]);

        EmailAccountMessageFolder::create([
            'message_id' => $message2->id,
            'folder_id' => $this->testFolder->id,
        ]);

        $relationshipsForMessage1 = EmailAccountMessageFolder::where('message_id', $this->testMessage->id)->get();
        $relationshipsForMessage2 = EmailAccountMessageFolder::where('message_id', $message2->id)->get();

        $this->assertCount(1, $relationshipsForMessage1);
        $this->assertCount(1, $relationshipsForMessage2);
        $this->assertEquals($this->testMessage->id, $relationshipsForMessage1->first()->message_id);
        $this->assertEquals($message2->id, $relationshipsForMessage2->first()->message_id);
    }

    #[Test]
    public function it_can_find_by_folder_id(): void
    {
        $folder2 = EmailAccountFolderFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        EmailAccountMessageFolder::create([
            'message_id' => $this->testMessage->id,
            'folder_id' => $this->testFolder->id,
        ]);

        EmailAccountMessageFolder::create([
            'message_id' => $this->testMessage->id,
            'folder_id' => $folder2->id,
        ]);

        $relationshipsForFolder1 = EmailAccountMessageFolder::where('folder_id', $this->testFolder->id)->get();
        $relationshipsForFolder2 = EmailAccountMessageFolder::where('folder_id', $folder2->id)->get();

        $this->assertCount(1, $relationshipsForFolder1);
        $this->assertCount(1, $relationshipsForFolder2);
        $this->assertEquals($this->testFolder->id, $relationshipsForFolder1->first()->folder_id);
        $this->assertEquals($folder2->id, $relationshipsForFolder2->first()->folder_id);
    }
}
