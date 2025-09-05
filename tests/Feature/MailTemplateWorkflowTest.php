<?php

namespace Turahe\MailClient\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\Factories\UserFactory;
use PHPUnit\Framework\Attributes\Test;
use Turahe\MailClient\Models\PredefinedMailTemplate;
use Turahe\MailClient\Tests\TestCase;

class MailTemplateWorkflowTest extends TestCase
{
    use WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = UserFactory::new()->createOne();
    }

    #[Test]
    public function it_can_manage_mail_template_lifecycle(): void
    {
        // Create a new template
        $template = PredefinedMailTemplate::create([
            'name' => 'Welcome Email Template',
            'subject' => 'Welcome to {{company_name}}!',
            'body' => '
                <html>
                    <body>
                        <h1>Welcome {{user_name}}!</h1>
                        <p>Thank you for joining {{company_name}}. We are excited to have you on board.</p>
                        <p>Here are your next steps:</p>
                        <ul>
                            <li>Complete your profile</li>
                            <li>Explore our features</li>
                            <li>Join our community</li>
                        </ul>
                        <p>Best regards,<br>The {{company_name}} Team</p>
                    </body>
                </html>
            ',
            'is_shared' => false,
        ]);

        $this->assertInstanceOf(PredefinedMailTemplate::class, $template);
        $this->assertEquals('Welcome Email Template', $template->name);
        $this->assertFalse($template->is_shared);
        $this->assertStringContainsString('{{user_name}}', $template->body);
        $this->assertStringContainsString('{{company_name}}', $template->subject);

        // Update template content
        $template->update([
            'body' => str_replace('{{company_name}}', 'Acme Corp', $template->body),
            'subject' => str_replace('{{company_name}}', 'Acme Corp', $template->subject),
        ]);

        $this->assertStringContainsString('Acme Corp', $template->fresh()->body);
        $this->assertStringContainsString('Acme Corp', $template->fresh()->subject);

        // Make template shared
        $template->update(['is_shared' => true]);
        $this->assertTrue($template->fresh()->is_shared);

        // Test template filtering and search
        $sharedTemplates = PredefinedMailTemplate::shared()->get();
        $this->assertContains($template->id, $sharedTemplates->pluck('id'));

        $visibleTemplates = PredefinedMailTemplate::visibleToUser()->get();
        $this->assertContains($template->id, $visibleTemplates->pluck('id'));

        // Clone template for customization
        $clonedTemplate = PredefinedMailTemplate::create([
            'name' => $template->name.' - Custom',
            'subject' => $template->subject,
            'body' => str_replace('Acme Corp', 'Custom Company', $template->body),
            'is_shared' => false,
        ]);

        $this->assertStringContainsString('Custom Company', $clonedTemplate->body);
        $this->assertFalse($clonedTemplate->is_shared);
        $this->assertNotEquals($template->id, $clonedTemplate->id);
    }

    #[Test]
    public function it_can_handle_template_collection_and_filtering(): void
    {
        // Create multiple templates with different characteristics
        $welcomeTemplate = PredefinedMailTemplate::create([
            'name' => 'Welcome Email',
            'subject' => 'Welcome aboard!',
            'body' => '<h1>Welcome!</h1><p>We are excited to have you.</p>',
            'is_shared' => true,
        ]);

        $newsletterTemplate = PredefinedMailTemplate::create([
            'name' => 'Monthly Newsletter',
            'subject' => 'Newsletter - {{month}} {{year}}',
            'body' => '<h2>Newsletter</h2><p>Check out what is new this month.</p>',
            'is_shared' => true,
        ]);

        $privateTemplate = PredefinedMailTemplate::create([
            'name' => 'Private Draft',
            'subject' => 'Draft Email',
            'body' => '<p>This is a private draft.</p>',
            'is_shared' => false,
        ]);

        $followUpTemplate = PredefinedMailTemplate::create([
            'name' => 'Follow Up Template',
            'subject' => 'Following up on our conversation',
            'body' => '<p>I wanted to follow up on our recent conversation.</p>',
            'is_shared' => false,
        ]);

        // Test shared templates scope
        $sharedTemplates = PredefinedMailTemplate::shared()->get();
        $this->assertCount(2, $sharedTemplates);
        $this->assertContains($welcomeTemplate->id, $sharedTemplates->pluck('id'));
        $this->assertContains($newsletterTemplate->id, $sharedTemplates->pluck('id'));
        $this->assertNotContains($privateTemplate->id, $sharedTemplates->pluck('id'));

        // Test visible templates (should only return shared)
        $visibleTemplates = PredefinedMailTemplate::visibleToUser()->get();
        $this->assertCount(2, $visibleTemplates);
        $this->assertContains($welcomeTemplate->id, $visibleTemplates->pluck('id'));
        $this->assertContains($newsletterTemplate->id, $visibleTemplates->pluck('id'));

        // Test name filtering
        $welcomeTemplates = PredefinedMailTemplate::where('name', 'LIKE', '%Welcome%')->get();
        $this->assertCount(1, $welcomeTemplates);
        $this->assertEquals($welcomeTemplate->id, $welcomeTemplates->first()->id);

        $followUpTemplates = PredefinedMailTemplate::where('name', 'LIKE', '%Follow%')->get();
        $this->assertCount(1, $followUpTemplates);
        $this->assertEquals($followUpTemplate->id, $followUpTemplates->first()->id);

        // Test subject filtering
        $conversationTemplates = PredefinedMailTemplate::where('subject', 'LIKE', '%conversation%')->get();
        $this->assertCount(1, $conversationTemplates);
        $this->assertEquals($followUpTemplate->id, $conversationTemplates->first()->id);

        // Test body content search
        $draftTemplates = PredefinedMailTemplate::where('body', 'LIKE', '%draft%')->get();
        $this->assertCount(1, $draftTemplates);
        $this->assertEquals($privateTemplate->id, $draftTemplates->first()->id);
    }

    #[Test]
    public function it_can_handle_template_content_variations(): void
    {
        // Test HTML template
        $htmlTemplate = PredefinedMailTemplate::create([
            'name' => 'Rich HTML Template',
            'subject' => 'Professional Email',
            'body' => '
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .header { background-color: #f8f9fa; padding: 20px; }
                        .content { padding: 20px; }
                        .footer { background-color: #e9ecef; padding: 10px; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Professional Communication</h1>
                    </div>
                    <div class="content">
                        <p>Dear {{recipient_name}},</p>
                        <p>This is a professionally formatted email with proper HTML structure.</p>
                        <table border="1" style="border-collapse: collapse; width: 100%;">
                            <tr>
                                <th>Feature</th>
                                <th>Description</th>
                            </tr>
                            <tr>
                                <td>HTML Support</td>
                                <td>Full HTML formatting capabilities</td>
                            </tr>
                            <tr>
                                <td>Template Variables</td>
                                <td>Dynamic content substitution</td>
                            </tr>
                        </table>
                    </div>
                    <div class="footer">
                        <p>Best regards,<br>{{sender_name}}</p>
                    </div>
                </body>
                </html>
            ',
            'is_shared' => true,
        ]);

        // Test plain text template
        $plainTextTemplate = PredefinedMailTemplate::create([
            'name' => 'Simple Text Template',
            'subject' => 'Simple Notification',
            'body' => '
Dear {{recipient_name}},

This is a simple plain text email template.

Key points:
- Easy to read
- No HTML formatting
- Fast to load
- Compatible with all email clients

Thank you for your attention.

Best regards,
{{sender_name}}
{{company_name}}
            ',
            'is_shared' => true,
        ]);

        // Test template with minimal content
        $minimalTemplate = PredefinedMailTemplate::create([
            'name' => 'Minimal Template',
            'subject' => 'Quick Note',
            'body' => '<p>{{message}}</p>',
            'is_shared' => false,
        ]);

        // Verify content characteristics
        $this->assertStringContainsString('<!DOCTYPE html>', $htmlTemplate->body);
        $this->assertStringContainsString('<style>', $htmlTemplate->body);
        $this->assertStringContainsString('<table', $htmlTemplate->body);

        $this->assertStringNotContainsString('<html>', $plainTextTemplate->body);
        $this->assertStringNotContainsString('<p>', $plainTextTemplate->body);
        $this->assertStringContainsString('Dear {{recipient_name}}', $plainTextTemplate->body);

        $this->assertTrue(strlen($minimalTemplate->body) < 50);
        $this->assertStringContainsString('{{message}}', $minimalTemplate->body);

        // Test template duplication for different purposes
        $duplicatedTemplate = PredefinedMailTemplate::create([
            'name' => $htmlTemplate->name.' - Copy',
            'subject' => $htmlTemplate->subject.' (Copy)',
            'body' => $htmlTemplate->body,
            'is_shared' => false,
        ]);

        $this->assertNotEquals($htmlTemplate->id, $duplicatedTemplate->id);
        $this->assertEquals($htmlTemplate->body, $duplicatedTemplate->body);
        $this->assertStringContainsString('Copy', $duplicatedTemplate->name);
        $this->assertFalse($duplicatedTemplate->is_shared);

        // Test batch operations
        $allTemplates = PredefinedMailTemplate::all();
        $this->assertCount(4, $allTemplates);

        // Update all shared templates to include a footer
        $sharedTemplates = PredefinedMailTemplate::shared()->get();
        foreach ($sharedTemplates as $template) {
            $template->update([
                'body' => $template->body."\n\n--- \nThis is an automated message.",
            ]);
        }

        $updatedShared = PredefinedMailTemplate::shared()->get();
        foreach ($updatedShared as $template) {
            $this->assertStringContainsString('automated message', $template->body);
        }
    }
}
