<?php

namespace Turahe\MailClient\Tests\Unit;

use Turahe\MailClient\DTOs\EmailAccountConfig;
use Turahe\MailClient\Enums\ConnectionType;
use Turahe\MailClient\Enums\EmailAccountType;
use Turahe\MailClient\Enums\SyncState;
use Turahe\MailClient\Services\EmailAccountManager;
use Turahe\MailClient\Tests\TestCase;

class Php83FeaturesTest extends TestCase
{
    public function test_enum_methods_work_correctly(): void
    {
        // Test ConnectionType enum methods
        $gmail = ConnectionType::Gmail;
        $this->assertEquals('Gmail', $gmail->getDisplayName());
        $this->assertTrue($gmail->supportsOAuth());
        $this->assertEquals(['imap' => 993, 'smtp' => 587], $gmail->getDefaultPorts());

        $imap = ConnectionType::Imap;
        $this->assertEquals('IMAP', $imap->getDisplayName());
        $this->assertFalse($imap->supportsOAuth());

        // Test SyncState enum methods
        $enabled = SyncState::Enabled;
        $this->assertEquals('Enabled', $enabled->getDisplayName());
        $this->assertTrue($enabled->isActive());
        $this->assertFalse($enabled->isInactive());
        $this->assertEquals('text-green-500', $enabled->getColorClass());

        $disabled = SyncState::Disabled;
        $this->assertFalse($disabled->isActive());
        $this->assertTrue($disabled->isInactive());
        $this->assertEquals('text-red-500', $disabled->getColorClass());

        // Test EmailAccountType enum methods
        $personal = EmailAccountType::Personal;
        $this->assertEquals('Personal', $personal->getDisplayName());
        $this->assertTrue($personal->isPersonal());
        $this->assertFalse($personal->isShared());
        $this->assertEquals('fas fa-user', $personal->getIconClass());

        $shared = EmailAccountType::Shared;
        $this->assertEquals('Shared', $shared->getDisplayName());
        $this->assertTrue($shared->isShared());
        $this->assertFalse($shared->isPersonal());
        $this->assertEquals('fas fa-users', $shared->getIconClass());
    }

    public function test_enum_to_array_methods(): void
    {
        $connectionTypes = ConnectionType::toArray();
        $this->assertContains('GMAIL', $connectionTypes);
        $this->assertContains('OUTLOOK', $connectionTypes);
        $this->assertContains('IMAP', $connectionTypes);

        $syncStates = SyncState::toArray();
        $this->assertContains('ENABLED', $syncStates);
        $this->assertContains('DISABLED', $syncStates);
        $this->assertContains('STOPPED', $syncStates);

        $accountTypes = EmailAccountType::toArray();
        $this->assertContains('PERSONAL', $accountTypes);
        $this->assertContains('SHARED', $accountTypes);
    }

    public function test_email_account_config_dto(): void
    {
        $config = new EmailAccountConfig(
            email: 'test@example.com',
            password: 'password123',
            connectionType: ConnectionType::Gmail
        );

        $this->assertEquals('test@example.com', $config->email);
        $this->assertEquals('password123', $config->password);
        $this->assertEquals(ConnectionType::Gmail, $config->connectionType);
        $this->assertEquals('imap.gmail.com', $config->getEffectiveImapServer());
        $this->assertEquals('smtp.gmail.com', $config->getEffectiveSmtpServer());
        $this->assertEquals('test@example.com', $config->getEffectiveUsername());

        // Test readonly properties cannot be modified
        $this->expectException(\Error::class);
        $config->email = 'new@example.com';
    }

    public function test_email_account_config_validation(): void
    {
        // Test invalid email
        $this->expectException(\InvalidArgumentException::class);
        new EmailAccountConfig(
            email: 'invalid-email',
            password: 'password123',
            connectionType: ConnectionType::Gmail
        );
    }

    public function test_email_account_config_from_array(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'connection_type' => 'GMAIL',
            'imap_port' => 993,
            'smtp_port' => 587,
        ];

        $config = EmailAccountConfig::fromArray($data);

        $this->assertEquals('test@example.com', $config->email);
        $this->assertEquals(ConnectionType::Gmail, $config->connectionType);
        $this->assertEquals(993, $config->imapPort);
        $this->assertEquals(587, $config->smtpPort);
    }

    public function test_email_account_config_with_method(): void
    {
        $config = new EmailAccountConfig(
            email: 'test@example.com',
            password: 'password123',
            connectionType: ConnectionType::Gmail
        );

        $updatedConfig = $config->with([
            'imap_port' => 143,
            'smtp_port' => 25,
        ]);

        $this->assertEquals(143, $updatedConfig->imapPort);
        $this->assertEquals(25, $updatedConfig->smtpPort);
        $this->assertEquals('test@example.com', $updatedConfig->email); // Unchanged
    }

    public function test_email_account_manager_service(): void
    {
        $manager = new EmailAccountManager();

        // Test validation
        $errors = $manager->validateAccountConfig([
            'email' => 'invalid-email',
            'connection_type' => 'INVALID_TYPE',
        ]);

        $this->assertNotEmpty($errors);
        $this->assertContains('The email field must be a valid email address.', $errors);
        $this->assertContains('The connection type must be a valid value.', $errors);
    }

    public function test_validation_trait(): void
    {
        $trait = new class {
            use \Turahe\MailClient\Concerns\HasEmailAccountValidation;
        };

        // Test validation rules
        $rules = $trait->getEmailAccountValidationRules();
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
        $this->assertArrayHasKey('connection_type', $rules);

        // Test validation messages
        $messages = $trait->getEmailAccountValidationMessages();
        $this->assertArrayHasKey('email.required', $messages);
        $this->assertArrayHasKey('email.email', $messages);

        // Test connection type validation
        $errors = $trait->validateConnectionTypeConfig(ConnectionType::Gmail, [
            'imap_server' => 'wrong.server.com',
        ]);
        $this->assertNotEmpty($errors);
        $this->assertContains('Gmail IMAP server should be imap.gmail.com.', $errors);
    }

    public function test_match_expressions_in_enums(): void
    {
        // Test match expressions in enum methods
        $gmail = ConnectionType::Gmail;
        $this->assertEquals('Gmail', $gmail->getDisplayName());
        $this->assertTrue($gmail->supportsOAuth());

        $outlook = ConnectionType::Outlook;
        $this->assertEquals('Outlook', $outlook->getDisplayName());
        $this->assertTrue($outlook->supportsOAuth());

        $imap = ConnectionType::Imap;
        $this->assertEquals('IMAP', $imap->getDisplayName());
        $this->assertFalse($imap->supportsOAuth());
    }

    public function test_null_coalescing_assignment(): void
    {
        // This test demonstrates the null coalescing assignment operator (??=)
        // which is used in the EmailAccount model
        $user = null;
        $user ??= auth()->user();
        
        // Since we're in a test environment without auth, this should remain null
        $this->assertNull($user);
    }
} 