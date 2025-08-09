<?php

namespace Turahe\MailClient\Tests\Unit;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\Factories\UserFactory;
use PHPUnit\Framework\Attributes\Test;
use Turahe\MailClient\Models\EmailAccount;
use Turahe\MailClient\Models\ScheduledEmail;
use Turahe\MailClient\Tests\Factories\EmailAccountFactory;
use Turahe\MailClient\Tests\Factories\ScheduledEmailFactory;
use Turahe\MailClient\Tests\TestCase;

class ScheduledEmailTest extends TestCase
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
    public function it_can_list_all_scheduled_emails(): void
    {
        ScheduledEmailFactory::new()->count(3)->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        $this->assertInstanceOf(Collection::class, ScheduledEmail::all());
        $this->assertCount(3, ScheduledEmail::all());
    }

    #[Test]
    public function it_can_create_a_scheduled_email(): void
    {
        $scheduledAt = now()->addHour();
        $data = [
            'email_account_id' => $this->testAccount->id,
            'subject' => $this->faker->sentence,
            'html_body' => $this->faker->paragraph,
            'to' => [$this->faker->email],
            'cc' => [$this->faker->email],
            'bcc' => [],
            'type' => 'email',
            'scheduled_at' => $scheduledAt,
            'status' => 'pending',
            'retries' => 0,
        ];

        $scheduledEmail = ScheduledEmail::create($data);

        $this->assertInstanceOf(ScheduledEmail::class, $scheduledEmail);
        $this->assertEquals($data['email_account_id'], $scheduledEmail->email_account_id);
        $this->assertEquals($data['subject'], $scheduledEmail->subject);
        $this->assertEquals($data['html_body'], $scheduledEmail->html_body);
        $this->assertEquals($data['to'], $scheduledEmail->to);
        $this->assertEquals($data['cc'], $scheduledEmail->cc);
        $this->assertEquals($data['bcc'], $scheduledEmail->bcc);
        $this->assertEquals($scheduledAt->timestamp, $scheduledEmail->scheduled_at->timestamp);
        $this->assertEquals('pending', $scheduledEmail->status);
        $this->assertEquals(0, $scheduledEmail->retries);
    }

    #[Test]
    public function it_can_update_scheduled_email(): void
    {
        $scheduledEmail = ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'pending',
        ]);

        $newSubject = 'Updated Subject';
        $updated = $scheduledEmail->update(['subject' => $newSubject]);

        $this->assertTrue($updated);
        $this->assertEquals($newSubject, $scheduledEmail->fresh()->subject);
    }

    #[Test]
    public function it_can_delete_scheduled_email(): void
    {
        $scheduledEmail = ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        $deleted = $scheduledEmail->delete();

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('scheduled_emails', ['id' => $scheduledEmail->id]);
    }

    #[Test]
    public function it_belongs_to_email_account(): void
    {
        $scheduledEmail = ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        $this->assertInstanceOf(EmailAccount::class, $scheduledEmail->account);
        $this->assertEquals($this->testAccount->id, $scheduledEmail->account->id);
    }

    #[Test]
    public function it_can_mark_as_sending(): void
    {
        $scheduledEmail = ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'pending',
        ]);

        $scheduledEmail->markAsSending();

        $this->assertEquals('sending', $scheduledEmail->fresh()->status);
    }

    #[Test]
    public function it_can_mark_as_failed(): void
    {
        $scheduledEmail = ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'pending',
        ]);

        $reason = 'SMTP connection failed';
        $scheduledEmail->markAsFailed($reason);

        $freshEmail = $scheduledEmail->fresh();
        $this->assertEquals('failed', $freshEmail->status);
        $this->assertEquals($reason, $freshEmail->fail_reason);
    }

    #[Test]
    public function it_can_mark_as_failed_with_additional_attributes(): void
    {
        $scheduledEmail = ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'pending',
            'retries' => 1,
        ]);

        $reason = 'Rate limit exceeded';
        $retryAfter = now()->addMinutes(30);
        $scheduledEmail->markAsFailed($reason, [
            'retries' => 2,
            'retry_after' => $retryAfter,
            'failed_at' => now(),
        ]);

        $freshEmail = $scheduledEmail->fresh();
        $this->assertEquals('failed', $freshEmail->status);
        $this->assertEquals($reason, $freshEmail->fail_reason);
        $this->assertEquals(2, $freshEmail->retries);
        $this->assertEquals($retryAfter->timestamp, $freshEmail->retry_after->timestamp);
        $this->assertNotNull($freshEmail->failed_at);
    }

    #[Test]
    public function it_can_mark_as_sent(): void
    {
        $scheduledEmail = ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'sending',
            'fail_reason' => 'Previous failure',
            'retry_after' => now()->addHour(),
            'failed_at' => now()->subHour(),
        ]);

        $scheduledEmail->markAsSent();

        $freshEmail = $scheduledEmail->fresh();
        $this->assertEquals('sent', $freshEmail->status);
        $this->assertNull($freshEmail->fail_reason);
        $this->assertNull($freshEmail->retry_after);
        $this->assertNull($freshEmail->failed_at);
        $this->assertNotNull($freshEmail->sent_at);
    }

    #[Test]
    public function it_can_check_if_email_is_sent(): void
    {
        $sentEmail = ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'sent',
        ]);

        $pendingEmail = ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'pending',
        ]);

        $this->assertTrue($sentEmail->isSent());
        $this->assertFalse($pendingEmail->isSent());
    }

    #[Test]
    public function it_can_check_if_email_is_sending(): void
    {
        $sendingEmail = ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'sending',
        ]);

        $pendingEmail = ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'pending',
        ]);

        $this->assertTrue($sendingEmail->isSending());
        $this->assertFalse($pendingEmail->isSending());
    }

    #[Test]
    public function it_can_check_if_email_is_pending(): void
    {
        $pendingEmail = ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'pending',
        ]);

        $sentEmail = ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'sent',
        ]);

        $this->assertTrue($pendingEmail->isPending());
        $this->assertFalse($sentEmail->isPending());
    }

    #[Test]
    public function it_can_check_if_email_is_failed(): void
    {
        $failedEmail = ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'failed',
        ]);

        $pendingEmail = ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'pending',
        ]);

        $this->assertTrue($failedEmail->isFailed());
        $this->assertFalse($pendingEmail->isFailed());
    }

    #[Test]
    public function it_can_scope_pending_emails(): void
    {
        ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'pending',
        ]);

        ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'sent',
        ]);

        ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'failed',
        ]);

        $pendingEmails = ScheduledEmail::pending()->get();

        $this->assertCount(1, $pendingEmails);
        $this->assertEquals('pending', $pendingEmails->first()->status);
    }

    #[Test]
    public function it_can_scope_failed_emails(): void
    {
        ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'pending',
        ]);

        ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'failed',
        ]);

        ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'failed',
        ]);

        $failedEmails = ScheduledEmail::failed()->get();

        $this->assertCount(2, $failedEmails);
        $failedEmails->each(function ($email) {
            $this->assertEquals('failed', $email->status);
        });
    }

    #[Test]
    public function it_can_scope_due_for_send_emails(): void
    {
        // Email scheduled in the past (due)
        ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'pending',
            'scheduled_at' => now()->subHour(),
        ]);

        // Email scheduled in the future (not due)
        ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'pending',
            'scheduled_at' => now()->addHour(),
        ]);

        // Email scheduled now (due)
        ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'pending',
            'scheduled_at' => now(),
        ]);

        $dueEmails = ScheduledEmail::dueForSend()->get();

        $this->assertCount(2, $dueEmails);
        $dueEmails->each(function ($email) {
            $this->assertTrue($email->scheduled_at->lte(now()));
            $this->assertEquals('pending', $email->status);
        });
    }

    #[Test]
    public function it_can_scope_retryable_emails(): void
    {
        // Failed email that can be retried
        ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'failed',
            'retries' => 1,
            'retry_after' => now()->subMinutes(10),
        ]);

        // Failed email that has exceeded max retries
        ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'failed',
            'retries' => ScheduledEmail::$maxRetries,
            'retry_after' => now()->subMinutes(10),
        ]);

        // Failed email that is not ready for retry yet
        ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'status' => 'failed',
            'retries' => 1,
            'retry_after' => now()->addMinutes(10),
        ]);

        $retryableEmails = ScheduledEmail::retryable(now())->get();

        $this->assertCount(1, $retryableEmails);
        $this->assertEquals('failed', $retryableEmails->first()->status);
        $this->assertTrue($retryableEmails->first()->retries < ScheduledEmail::$maxRetries);
        $this->assertTrue($retryableEmails->first()->retry_after->lte(now()));
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $scheduledEmail = new ScheduledEmail();
        $casts = $scheduledEmail->getCasts();

        $this->assertEquals('datetime', $casts['scheduled_at']);
        $this->assertEquals('datetime', $casts['sent_at']);
        $this->assertEquals('datetime', $casts['failed_at']);
        $this->assertEquals('array', $casts['to']);
        $this->assertEquals('array', $casts['cc']);
        $this->assertEquals('array', $casts['bcc']);
        $this->assertEquals('int', $casts['retries']);
        $this->assertEquals('datetime', $casts['retry_after']);
        $this->assertEquals('string', $casts['email_account_id']);
        $this->assertEquals('string', $casts['related_message_id']);
        $this->assertEquals('array', $casts['associations']);
    }

    #[Test]
    public function it_has_max_retries_constant(): void
    {
        $this->assertEquals(3, ScheduledEmail::$maxRetries);
    }

    #[Test]
    public function it_uses_correct_table(): void
    {
        $scheduledEmail = new ScheduledEmail();
        $this->assertEquals('scheduled_emails', $scheduledEmail->getTable());
    }

    #[Test]
    public function it_allows_all_attributes_to_be_mass_assigned(): void
    {
        $scheduledEmail = new ScheduledEmail();
        $this->assertEquals([], $scheduledEmail->getGuarded());
    }

    #[Test]
    public function it_can_handle_complex_email_addresses(): void
    {
        $complexEmails = [
            'simple@example.com',
            'user+tag@example.com',
            'first.last@subdomain.example.com',
            'unicode.тест@example.com',
        ];

        $scheduledEmail = ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'to' => $complexEmails,
            'cc' => [$complexEmails[0], $complexEmails[1]],
            'bcc' => [$complexEmails[2]],
        ]);

        $this->assertEquals($complexEmails, $scheduledEmail->to);
        $this->assertEquals([$complexEmails[0], $complexEmails[1]], $scheduledEmail->cc);
        $this->assertEquals([$complexEmails[2]], $scheduledEmail->bcc);
    }
}
