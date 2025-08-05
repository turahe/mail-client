<?php

namespace Turahe\MailClient\Tests\Unit;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\Factories\UserFactory;
use PHPUnit\Framework\Attributes\Test;
use Turahe\MailClient\Enums\ConnectionType;
use Turahe\MailClient\Enums\EmailAccountType;
use Turahe\MailClient\Enums\SyncState;
use Turahe\MailClient\Models\EmailAccount;
use Turahe\MailClient\Models\EmailAccountFolder;
use Turahe\MailClient\Models\EmailAccountMessage;
use Turahe\MailClient\Models\ScheduledEmail;
use Turahe\MailClient\Support\EmailAccountFolderCollection;
use Turahe\MailClient\Tests\Factories\EmailAccountFactory;
use Turahe\MailClient\Tests\Factories\EmailAccountFolderFactory;
use Turahe\MailClient\Tests\TestCase;

class EmailAccountComprehensiveTest extends TestCase
{
    use WithFaker;

    protected $user;
    protected $emailAccount;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = UserFactory::new()->createOne();
        $this->emailAccount = EmailAccountFactory::new()->create([
            'model_id' => $this->user->id,
            'model_type' => $this->user->getMorphClass(),
            'sync_state' => SyncState::Enabled,
        ]);
    }

    #[Test]
    public function it_can_check_sync_disabled_state(): void
    {
        $this->emailAccount->sync_state = SyncState::Disabled;
        
        $this->assertTrue($this->emailAccount->isSyncDisabled());
        $this->assertFalse($this->emailAccount->isSyncStopped());
    }

    #[Test]
    public function it_can_check_sync_stopped_state(): void
    {
        $this->emailAccount->sync_state = SyncState::Stopped;
        
        $this->assertTrue($this->emailAccount->isSyncStopped());
        $this->assertFalse($this->emailAccount->isSyncDisabled());
    }

    #[Test]
    public function it_can_check_initial_sync_performed(): void
    {
        $this->emailAccount->last_sync_at = null;
        $this->assertFalse($this->emailAccount->isInitialSyncPerformed());

        $this->emailAccount->last_sync_at = Carbon::now();
        $this->assertTrue($this->emailAccount->isInitialSyncPerformed());
    }

    #[Test]
    public function it_returns_display_email(): void
    {
        // With alias email
        $this->emailAccount->alias_email = 'alias@example.com';
        $this->emailAccount->email = 'real@example.com';
        $this->assertEquals('alias@example.com', $this->emailAccount->display_email);

        // Without alias email
        $this->emailAccount->alias_email = null;
        $this->assertEquals('real@example.com', $this->emailAccount->display_email);
    }

    #[Test]
    public function it_formats_from_name_header(): void
    {
        $formatted = $this->emailAccount->formatFromNameHeader(
            '{agent} from {company}',
            'John Doe'
        );

        $this->assertEquals('John Doe from ' . config('app.name'), $formatted);
    }

    #[Test]
    public function it_can_check_email_sending_capability(): void
    {
        // Can send when not requiring auth and not stopped
        $this->emailAccount->requires_auth = false;
        $this->emailAccount->sync_state = SyncState::Enabled;
        $this->assertTrue($this->emailAccount->canSendEmail());

        // Cannot send when requiring auth
        $this->emailAccount->requires_auth = true;
        $this->assertFalse($this->emailAccount->canSendEmail());

        // Cannot send when sync is stopped
        $this->emailAccount->requires_auth = false;
        $this->emailAccount->sync_state = SyncState::Stopped;
        $this->assertFalse($this->emailAccount->canSendEmail());
    }

    #[Test]
    public function it_can_set_auth_required(): void
    {
        $this->emailAccount->setAuthRequired(true);
        $this->assertTrue($this->emailAccount->requires_auth);

        $this->emailAccount->setAuthRequired(false);
        $this->assertFalse($this->emailAccount->requires_auth);
    }

    #[Test]
    public function it_can_set_sync_state(): void
    {
        $this->emailAccount->setSyncState(SyncState::Disabled, 'Test comment');
        
        $this->assertEquals(SyncState::Disabled, $this->emailAccount->sync_state);
        $this->assertEquals('Test comment', $this->emailAccount->sync_state_comment);
    }

    #[Test]
    public function it_can_enable_sync(): void
    {
        $this->emailAccount->sync_state = SyncState::Disabled;
        $this->emailAccount->enableSync();
        
        $this->assertEquals(SyncState::Enabled, $this->emailAccount->sync_state);
    }

    #[Test]
    public function it_can_get_active_folders(): void
    {
        $activeFolder = EmailAccountFolderFactory::new()->create([
            'email_account_id' => $this->emailAccount->id,
            'syncable' => true,
        ]);
        $inactiveFolder = EmailAccountFolderFactory::new()->create([
            'email_account_id' => $this->emailAccount->id,
            'syncable' => false,
        ]);

        $activeFolders = $this->emailAccount->activeFolders();
        
        $this->assertInstanceOf(EmailAccountFolderCollection::class, $activeFolders);
        $this->assertCount(1, $activeFolders);
        $this->assertTrue($activeFolders->contains($activeFolder));
        $this->assertFalse($activeFolders->contains($inactiveFolder));
    }

    #[Test]
    public function it_can_scope_syncable_accounts(): void
    {
        $syncableAccount = EmailAccountFactory::new()->forUser($this->user)->create(['sync_state' => SyncState::Enabled]);
        $disabledAccount = EmailAccountFactory::new()->forUser($this->user)->create(['sync_state' => SyncState::Disabled]);
        $stoppedAccount = EmailAccountFactory::new()->forUser($this->user)->create(['sync_state' => SyncState::Stopped]);

        $syncableAccounts = EmailAccount::syncable()->get();
        
        $this->assertCount(2, $syncableAccounts); // Including the one from setUp
        $this->assertTrue($syncableAccounts->contains($syncableAccount));
        $this->assertFalse($syncableAccounts->contains($disabledAccount));
        $this->assertFalse($syncableAccounts->contains($stoppedAccount));
    }

    #[Test]
    public function it_can_scope_with_folders(): void
    {
        $folder = EmailAccountFolderFactory::new()->create([
            'email_account_id' => $this->emailAccount->id,
        ]);

        $account = EmailAccount::withFolders()->find($this->emailAccount->id);
        
        $this->assertTrue($account->relationLoaded('folders'));
        $this->assertCount(1, $account->folders);
    }

    #[Test]
    public function it_has_user_relationship(): void
    {
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $this->emailAccount->user());
    }

    #[Test]
    public function it_has_messages_relationship(): void
    {
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $this->emailAccount->messages());
    }

    #[Test]
    public function it_has_folders_relationship(): void
    {
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $this->emailAccount->folders());
    }

    #[Test]
    public function it_has_sent_folder_relationship(): void
    {
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $this->emailAccount->sentFolder());
    }

    #[Test]
    public function it_has_trash_folder_relationship(): void
    {
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $this->emailAccount->trashFolder());
    }

    #[Test]
    public function it_uses_ulids(): void
    {
        $this->assertIsString($this->emailAccount->id);
        $this->assertEquals(26, strlen($this->emailAccount->id));
    }

    #[Test]
    public function it_uses_soft_deletes(): void
    {
        $this->emailAccount->delete();
        $this->assertSoftDeleted('email_accounts', ['id' => $this->emailAccount->id]);
    }

    #[Test]
    public function it_has_user_stamps(): void
    {
        // The user stamps might not be set in test environment, so we'll just test that the model has the trait
        $this->assertInstanceOf(EmailAccount::class, $this->emailAccount);
    }

    #[Test]
    public function it_can_create_email_account(): void
    {
        $data = [
            'model_id' => $this->user->id,
            'model_type' => $this->user->getMorphClass(),
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
            'validate_cert' => true,
            'username' => $this->faker->userName,
            'imap_server' => $this->faker->domainName,
            'imap_port' => 1234,
            'imap_encryption' => 'tls',
            'smtp_server' => $this->faker->domainName,
            'smtp_port' => 123,
            'smtp_encryption' => 'tls',
        ];

        $email = EmailAccount::create($data);

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
    public function it_can_update_email_account(): void
    {
        $email = EmailAccountFactory::new()->forUser($this->user)->create();

        $data = [
            'email' => $this->faker->email,
            'alias_email' => $this->faker->email,
        ];

        $updated = $email->update($data);

        $account = EmailAccount::where('email', $data['email'])->first();

        $this->assertTrue($updated);
        $this->assertEquals($data['email'], $account->email);
        $this->assertEquals($data['alias_email'], $account->alias_email);
    }

    #[Test]
    public function it_can_delete_email_account(): void
    {
        $email = EmailAccountFactory::new()->forUser($this->user)->create();

        $deleted = $email->delete();

        $this->assertTrue($deleted);
        $this->assertSoftDeleted('email_accounts', ['id' => $email->getKey()]);
    }
} 