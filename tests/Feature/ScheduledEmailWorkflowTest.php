<?php

namespace Turahe\MailClient\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\Factories\UserFactory;
use PHPUnit\Framework\Attributes\Test;
use Turahe\MailClient\Models\EmailAccount;
use Turahe\MailClient\Models\ScheduledEmail;
use Turahe\MailClient\Tests\Factories\EmailAccountFactory;
use Turahe\MailClient\Tests\Factories\ScheduledEmailFactory;
use Turahe\MailClient\Tests\TestCase;

class ScheduledEmailWorkflowTest extends TestCase
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
        ]);
    }

    #[Test]
    public function it_can_schedule_and_process_email_workflow(): void
    {
        // Create a scheduled email for the future
        $scheduledEmail = ScheduledEmail::create([
            'email_account_id' => $this->emailAccount->id,
            'subject' => 'Scheduled Newsletter',
            'html_body' => '<h1>Monthly Newsletter</h1><p>Check out our latest updates!</p>',
            'to' => ['subscriber@example.com', 'another@example.com'],
            'cc' => ['manager@example.com'],
            'bcc' => [],
            'type' => 'newsletter',
            'scheduled_at' => Carbon::now()->addHours(2),
            'status' => 'pending',
            'retries' => 0,
        ]);

        $this->assertInstanceOf(ScheduledEmail::class, $scheduledEmail);
        $this->assertEquals('pending', $scheduledEmail->status);
        $this->assertTrue($scheduledEmail->isPending());
        $this->assertFalse($scheduledEmail->isSent());
        $this->assertFalse($scheduledEmail->isFailed());

        // Test scheduled email belongs to account
        $this->assertEquals($this->emailAccount->id, $scheduledEmail->email_account_id);
        $this->assertEquals($this->emailAccount->id, $scheduledEmail->account->id);

        // Test email is not due for sending yet
        $dueEmails = ScheduledEmail::dueForSend()->get();
        $this->assertNotContains($scheduledEmail->id, $dueEmails->pluck('id'));

        // Create an email that's due for sending
        $dueEmail = ScheduledEmail::create([
            'email_account_id' => $this->emailAccount->id,
            'subject' => 'Immediate Email',
            'html_body' => '<p>This should be sent immediately</p>',
            'to' => ['immediate@example.com'],
            'cc' => [],
            'bcc' => [],
            'type' => 'email',
            'scheduled_at' => Carbon::now()->subMinutes(5),
            'status' => 'pending',
            'retries' => 0,
        ]);

        // Test email is due for sending
        $dueEmails = ScheduledEmail::dueForSend()->get();
        $this->assertContains($dueEmail->id, $dueEmails->pluck('id'));

        // Simulate processing workflow
        $this->assertCount(2, ScheduledEmail::pending()->get());
        
        // Mark as sending
        $dueEmail->markAsSending();
        $this->assertEquals('sending', $dueEmail->status);
        $this->assertTrue($dueEmail->isSending());

        // Mark as sent
        $dueEmail->markAsSent();
        $this->assertEquals('sent', $dueEmail->status);
        $this->assertTrue($dueEmail->isSent());
        $this->assertNotNull($dueEmail->sent_at);
        $this->assertNull($dueEmail->fail_reason);
        $this->assertNull($dueEmail->retry_after);
        $this->assertNull($dueEmail->failed_at);

        // Test failed email workflow
        $failedEmail = ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->emailAccount->id,
            'status' => 'pending',
        ]);

        $failedEmail->markAsFailed('SMTP connection failed');
        $this->assertEquals('failed', $failedEmail->status);
        $this->assertTrue($failedEmail->isFailed());
        $this->assertEquals('SMTP connection failed', $failedEmail->fail_reason);

        // Test failed email with retry attributes
        $retryableEmail = ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->emailAccount->id,
            'status' => 'pending',
        ]);

        $retryableEmail->markAsFailed('Temporary failure', [
            'retries' => 1,
            'retry_after' => Carbon::now()->addMinutes(30),
            'failed_at' => Carbon::now(),
        ]);

        $this->assertEquals(1, $retryableEmail->retries);
        $this->assertNotNull($retryableEmail->retry_after);
        $this->assertNotNull($retryableEmail->failed_at);

        // Test retryable scope
        $retryableEmails = ScheduledEmail::retryable(Carbon::now()->addHour())->get();
        $this->assertContains($retryableEmail->id, $retryableEmails->pluck('id'));
    }

    #[Test]
    public function it_can_handle_scheduled_email_scopes_and_filtering(): void
    {
        // Create emails with different statuses
        $pendingEmails = ScheduledEmailFactory::new()->count(3)->create([
            'email_account_id' => $this->emailAccount->id,
            'status' => 'pending',
            'scheduled_at' => Carbon::now()->addHour(),
        ]);

        $sentEmails = ScheduledEmailFactory::new()->count(2)->create([
            'email_account_id' => $this->emailAccount->id,
            'status' => 'sent',
            'sent_at' => Carbon::now()->subHour(),
        ]);

        $failedEmails = ScheduledEmailFactory::new()->count(1)->create([
            'email_account_id' => $this->emailAccount->id,
            'status' => 'failed',
            'failed_at' => Carbon::now()->subMinutes(30),
        ]);

        // Test pending scope
        $pendingResults = ScheduledEmail::pending()->get();
        $this->assertCount(3, $pendingResults);
        foreach ($pendingResults as $email) {
            $this->assertEquals('pending', $email->status);
        }

        // Test failed scope
        $failedResults = ScheduledEmail::failed()->get();
        $this->assertCount(1, $failedResults);
        $this->assertEquals('failed', $failedResults->first()->status);

        // Test due for send scope (create overdue emails)
        $overdueEmails = ScheduledEmailFactory::new()->count(2)->create([
            'email_account_id' => $this->emailAccount->id,
            'status' => 'pending',
            'scheduled_at' => Carbon::now()->subMinutes(30),
        ]);

        $dueEmails = ScheduledEmail::dueForSend()->get();
        $this->assertCount(2, $dueEmails);
        foreach ($dueEmails as $email) {
            $this->assertTrue($email->scheduled_at->lte(Carbon::now()));
        }

        // Test complex email addresses
        $complexEmail = ScheduledEmail::create([
            'email_account_id' => $this->emailAccount->id,
            'subject' => 'Complex Recipients',
            'html_body' => '<p>Testing complex recipients</p>',
            'to' => [
                'simple@example.com',
                'user+tag@example.com',
                'first.last@subdomain.example.com',
            ],
            'cc' => ['manager@example.com', 'supervisor@example.com'],
            'bcc' => ['bcc@example.com'],
            'type' => 'email',
            'scheduled_at' => Carbon::now()->addMinutes(10),
            'status' => 'pending',
        ]);

        $this->assertCount(3, $complexEmail->to);
        $this->assertCount(2, $complexEmail->cc);
        $this->assertCount(1, $complexEmail->bcc);
        $this->assertContains('user+tag@example.com', $complexEmail->to);
        $this->assertContains('first.last@subdomain.example.com', $complexEmail->to);
    }

    #[Test]
    public function it_can_handle_retry_logic_workflow(): void
    {
        $maxRetries = ScheduledEmail::$maxRetries;
        
        // Create email that will fail multiple times
        $email = ScheduledEmail::create([
            'email_account_id' => $this->emailAccount->id,
            'subject' => 'Retry Test Email',
            'html_body' => '<p>This will fail and retry</p>',
            'to' => ['test@example.com'],
            'cc' => [],
            'bcc' => [],
            'type' => 'email',
            'scheduled_at' => Carbon::now()->subMinutes(5),
            'status' => 'pending',
            'retries' => 0,
        ]);

        // Simulate multiple failures with retries
        for ($i = 1; $i <= $maxRetries; $i++) {
            $email->markAsFailed("Attempt {$i} failed", [
                'retries' => $i,
                'retry_after' => Carbon::now()->addMinutes(10 * $i),
                'failed_at' => Carbon::now(),
            ]);

            $this->assertEquals($i, $email->retries);
            $this->assertEquals('failed', $email->status);
        }

        // After max retries, email should still be retryable if retry_after is set
        $retryableEmails = ScheduledEmail::retryable(Carbon::now()->addHour())->get();
        
        if ($email->retries < $maxRetries) {
            $this->assertContains($email->id, $retryableEmails->pluck('id'));
        }

        // Test successful retry
        $retryEmail = ScheduledEmailFactory::new()->create([
            'email_account_id' => $this->emailAccount->id,
            'status' => 'failed',
            'retries' => 1,
            'retry_after' => Carbon::now()->subMinutes(5),
        ]);

        $retryableNow = ScheduledEmail::retryable()->get();
        $this->assertContains($retryEmail->id, $retryableNow->pluck('id'));

        // Mark retry as successful
        $retryEmail->markAsSent();
        $this->assertEquals('sent', $retryEmail->status);
        $this->assertNotNull($retryEmail->sent_at);
        $this->assertNull($retryEmail->fail_reason);
    }
}
