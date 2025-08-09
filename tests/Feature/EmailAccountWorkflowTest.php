<?php

namespace Turahe\MailClient\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\Factories\UserFactory;
use PHPUnit\Framework\Attributes\Test;
use Turahe\MailClient\Enums\ConnectionType;
use Turahe\MailClient\Enums\EmailAccountType;
use Turahe\MailClient\Enums\SyncState;
use Turahe\MailClient\Models\EmailAccount;
use Turahe\MailClient\Models\EmailAccountFolder;
use Turahe\MailClient\Models\EmailAccountMessage;
use Turahe\MailClient\Tests\Factories\EmailAccountFactory;
use Turahe\MailClient\Tests\Factories\EmailAccountFolderFactory;
use Turahe\MailClient\Tests\Factories\EmailAccountMessageFactory;
use Turahe\MailClient\Tests\TestCase;

class EmailAccountWorkflowTest extends TestCase
{
    use WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = UserFactory::new()->createOne();
    }

    #[Test]
    public function it_can_create_complete_email_account_with_folders_and_messages(): void
    {
        // Create email account
        $emailAccount = EmailAccount::create([
            'model_id' => $this->user->id,
            'model_type' => $this->user->getMorphClass(),
            'email' => $this->faker->email,
            'alias_email' => $this->faker->email,
            'type' => EmailAccountType::Personal,
            'connection_type' => ConnectionType::Imap,
            'sync_state' => SyncState::Enabled,
            'access_token_id' => null,
            'name' => $this->faker->name,
            'username' => $this->faker->userName,
            'password' => encrypt('password'),
            'initial_sync_from' => now()->subDays(30),
        ]);

        $this->assertInstanceOf(EmailAccount::class, $emailAccount);
        $this->assertEquals($this->user->id, $emailAccount->model_id);

        // Create folders for the account
        $inboxFolder = EmailAccountFolder::create([
            'email_account_id' => $emailAccount->id,
            'name' => 'INBOX',
            'display_name' => 'Inbox',
            'remote_id' => 'INBOX',
            'type' => 'inbox',
            'syncable' => true,
        ]);

        $sentFolder = EmailAccountFolder::create([
            'email_account_id' => $emailAccount->id,
            'name' => 'SENT',
            'display_name' => 'Sent',
            'remote_id' => 'SENT',
            'type' => 'sent',
            'syncable' => true,
        ]);

        $this->assertCount(2, $emailAccount->folders);

        // Create messages in folders
        $message1 = EmailAccountMessage::create([
            'email_account_id' => $emailAccount->id,
            'remote_id' => $this->faker->uuid,
            'message_id' => $this->faker->uuid,
            'subject' => 'Welcome Email',
            'html_body' => '<p>Welcome to our service!</p>',
            'text_body' => 'Welcome to our service!',
            'is_read' => false,
            'date' => now(),
        ]);

        $message2 = EmailAccountMessage::create([
            'email_account_id' => $emailAccount->id,
            'remote_id' => $this->faker->uuid,
            'message_id' => $this->faker->uuid,
            'subject' => 'Follow-up Email',
            'html_body' => '<p>Thank you for joining!</p>',
            'text_body' => 'Thank you for joining!',
            'is_read' => true,
            'date' => now()->subHour(),
        ]);

        // Associate messages with folders
        $message1->folders()->attach($inboxFolder->id);
        $message2->folders()->attach($sentFolder->id);

        // Test complete workflow
        $this->assertCount(2, $emailAccount->messages);
        $this->assertCount(1, $inboxFolder->messages);
        $this->assertCount(1, $sentFolder->messages);
        
        // Test account relationships  
        $this->assertFalse($emailAccount->isSyncDisabled());
        $this->assertEquals('Personal', $emailAccount->type->value);
        $this->assertEquals('Imap', $emailAccount->connection_type->value);

        // Test message operations
        $unreadMessages = $emailAccount->messages()->where('is_read', false)->get();
        $this->assertCount(1, $unreadMessages);
        $this->assertEquals('Welcome Email', $unreadMessages->first()->subject);

        // Mark message as read
        $message1->update(['is_read' => true]);
        $this->assertTrue($message1->fresh()->is_read);

        // Test account deletion (should cascade)
        $accountId = $emailAccount->id;
        $emailAccount->delete();
        
        $this->assertSoftDeleted('email_accounts', ['id' => $accountId]);
    }

    #[Test]
    public function it_can_handle_email_account_sync_workflow(): void
    {
        $emailAccount = EmailAccountFactory::new()->create([
            'model_id' => $this->user->id,
            'model_type' => $this->user->getMorphClass(),
            'sync_state' => SyncState::Disabled,
        ]);

        // Test sync state transitions
        $this->assertTrue($emailAccount->isSyncDisabled());
        $this->assertFalse($emailAccount->isSyncStopped());

        // Enable sync
        $emailAccount->enableSync();
        $this->assertFalse($emailAccount->fresh()->isSyncDisabled());

        // Stop sync
        $emailAccount->update(['sync_state' => SyncState::Stopped]);
        $this->assertTrue($emailAccount->fresh()->isSyncStopped());

        // Test with folders
        $folders = EmailAccountFolderFactory::new()->count(3)->create([
            'email_account_id' => $emailAccount->id,
            'syncable' => true,
        ]);

        $activeFolders = $emailAccount->getActiveFolders();
        $this->assertCount(3, $activeFolders);

        // Test message creation workflow
        $messages = EmailAccountMessageFactory::new()->count(5)->create([
            'email_account_id' => $emailAccount->id,
        ]);

        foreach ($messages as $index => $message) {
            $message->folders()->attach($folders->random()->id);
            $message->update(['is_read' => $index % 2 === 0]); // Alternate read/unread
        }

        // Test message queries
        $readMessages = $emailAccount->messages()->read()->get();
        $unreadMessages = $emailAccount->messages()->unread()->get();
        
        $this->assertTrue($readMessages->count() > 0);
        $this->assertTrue($unreadMessages->count() > 0);
        $this->assertEquals(5, $readMessages->count() + $unreadMessages->count());
    }

    #[Test] 
    public function it_can_handle_complex_folder_hierarchy(): void
    {
        $emailAccount = EmailAccountFactory::new()->create([
            'model_id' => $this->user->id,
            'model_type' => $this->user->getMorphClass(),
        ]);

        // Create parent folder
        $parentFolder = EmailAccountFolder::create([
            'email_account_id' => $emailAccount->id,
            'name' => 'Projects',
            'display_name' => 'Projects',
            'remote_id' => 'Projects',
            'parent_id' => null,
        ]);

        // Create child folders
        $childFolder1 = EmailAccountFolder::create([
            'email_account_id' => $emailAccount->id,
            'name' => 'Projects/Client-A',
            'display_name' => 'Client A',
            'remote_id' => 'Projects/Client-A',
            'parent_id' => $parentFolder->id,
        ]);

        $childFolder2 = EmailAccountFolder::create([
            'email_account_id' => $emailAccount->id,
            'name' => 'Projects/Client-B', 
            'display_name' => 'Client B',
            'remote_id' => 'Projects/Client-B',
            'parent_id' => $parentFolder->id,
        ]);

        // Test folder relationships
        $childFolders = EmailAccountFolder::where('parent_id', $parentFolder->id)->get();
        $this->assertCount(2, $childFolders);
        $this->assertEquals($parentFolder->id, $childFolder1->parent_id);
        $this->assertEquals($parentFolder->id, $childFolder2->parent_id);

        // Test folder collection
        $allFolders = $emailAccount->folders;
        $this->assertCount(3, $allFolders);

        // Create messages in different folders
        $message1 = EmailAccountMessageFactory::new()->create([
            'email_account_id' => $emailAccount->id,
            'subject' => 'Client A Project Update',
        ]);

        $message2 = EmailAccountMessageFactory::new()->create([
            'email_account_id' => $emailAccount->id,
            'subject' => 'Client B Meeting Notes',
        ]);

        // Associate messages with specific folders
        $message1->folders()->attach($childFolder1->id);
        $message2->folders()->attach($childFolder2->id);

        // Test folder-specific message retrieval
        $clientAMessages = $childFolder1->messages;
        $clientBMessages = $childFolder2->messages;
        
        $this->assertCount(1, $clientAMessages);
        $this->assertCount(1, $clientBMessages);
        $this->assertEquals('Client A Project Update', $clientAMessages->first()->subject);
        $this->assertEquals('Client B Meeting Notes', $clientBMessages->first()->subject);
    }
}
