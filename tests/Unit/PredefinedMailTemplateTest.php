<?php

namespace Turahe\MailClient\Tests\Unit;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\Factories\UserFactory;
use PHPUnit\Framework\Attributes\Test;
use Turahe\MailClient\Models\PredefinedMailTemplate;
use Turahe\MailClient\Tests\TestCase;

class PredefinedMailTemplateTest extends TestCase
{
    use WithFaker;

    protected $testUser;
    protected $anotherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testUser = UserFactory::new()->createOne();
        $this->anotherUser = UserFactory::new()->createOne();
    }

    #[Test]
    public function it_can_list_all_predefined_mail_templates(): void
    {
        PredefinedMailTemplate::factory()->count(3)->create([
            'user_id' => $this->testUser->id,
        ]);

        $this->assertInstanceOf(Collection::class, PredefinedMailTemplate::all());
        $this->assertCount(3, PredefinedMailTemplate::all());
    }

    #[Test]
    public function it_can_create_a_predefined_mail_template(): void
    {
        $data = [
            'name' => 'Welcome Email',
            'subject' => 'Welcome to our platform!',
            'body' => '<p>Welcome to our platform. We are excited to have you!</p>',
            'is_shared' => false,
            'user_id' => $this->testUser->id,
        ];

        $template = PredefinedMailTemplate::create($data);

        $this->assertInstanceOf(PredefinedMailTemplate::class, $template);
        $this->assertEquals($data['name'], $template->name);
        $this->assertEquals($data['subject'], $template->subject);
        $this->assertEquals($data['body'], $template->body);
        $this->assertFalse($template->is_shared);
        $this->assertEquals($data['user_id'], $template->user_id);
    }

    #[Test]
    public function it_can_create_a_shared_template(): void
    {
        $data = [
            'name' => 'Shared Template',
            'subject' => 'This is a shared template',
            'body' => '<p>This template is shared across all users.</p>',
            'is_shared' => true,
            'user_id' => $this->testUser->id,
        ];

        $template = PredefinedMailTemplate::create($data);

        $this->assertTrue($template->is_shared);
        $this->assertEquals($data['user_id'], $template->user_id);
    }

    #[Test]
    public function it_can_update_predefined_mail_template(): void
    {
        $template = PredefinedMailTemplate::create([
            'name' => 'Original Name',
            'subject' => 'Original Subject',
            'body' => '<p>Original Body</p>',
            'is_shared' => false,
            'user_id' => $this->testUser->id,
        ]);

        $newData = [
            'name' => 'Updated Name',
            'subject' => 'Updated Subject',
            'body' => '<p>Updated Body</p>',
        ];

        $updated = $template->update($newData);

        $this->assertTrue($updated);
        $freshTemplate = $template->fresh();
        $this->assertEquals($newData['name'], $freshTemplate->name);
        $this->assertEquals($newData['subject'], $freshTemplate->subject);
        $this->assertEquals($newData['body'], $freshTemplate->body);
    }

    #[Test]
    public function it_can_delete_predefined_mail_template(): void
    {
        $template = PredefinedMailTemplate::create([
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body' => '<p>Test Body</p>',
            'is_shared' => false,
            'user_id' => $this->testUser->id,
        ]);

        $deleted = $template->delete();

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('predefined_mail_templates', ['id' => $template->id]);
    }

    #[Test]
    public function it_belongs_to_user(): void
    {
        $template = PredefinedMailTemplate::create([
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body' => '<p>Test Body</p>',
            'is_shared' => false,
            'user_id' => $this->testUser->id,
        ]);

        $this->assertEquals($this->testUser->id, $template->user->id);
        $this->assertEquals($this->testUser->name, $template->user->name);
    }

    #[Test]
    public function it_can_scope_visible_to_user(): void
    {
        // Create personal template for testUser
        $personalTemplate = PredefinedMailTemplate::create([
            'name' => 'Personal Template',
            'subject' => 'Personal Subject',
            'body' => '<p>Personal Body</p>',
            'is_shared' => false,
            'user_id' => $this->testUser->id,
        ]);

        // Create personal template for anotherUser
        $anotherPersonalTemplate = PredefinedMailTemplate::create([
            'name' => 'Another Personal Template',
            'subject' => 'Another Personal Subject',
            'body' => '<p>Another Personal Body</p>',
            'is_shared' => false,
            'user_id' => $this->anotherUser->id,
        ]);

        // Create shared template
        $sharedTemplate = PredefinedMailTemplate::create([
            'name' => 'Shared Template',
            'subject' => 'Shared Subject',
            'body' => '<p>Shared Body</p>',
            'is_shared' => true,
            'user_id' => $this->testUser->id,
        ]);

        // Test visibility for testUser
        $visibleToTestUser = PredefinedMailTemplate::visibleToUser($this->testUser->id)->get();
        $this->assertCount(2, $visibleToTestUser); // personal + shared
        
        $templateIds = $visibleToTestUser->pluck('id')->toArray();
        $this->assertContains($personalTemplate->id, $templateIds);
        $this->assertContains($sharedTemplate->id, $templateIds);
        $this->assertNotContains($anotherPersonalTemplate->id, $templateIds);

        // Test visibility for anotherUser
        $visibleToAnotherUser = PredefinedMailTemplate::visibleToUser($this->anotherUser->id)->get();
        $this->assertCount(2, $visibleToAnotherUser); // personal + shared
        
        $anotherTemplateIds = $visibleToAnotherUser->pluck('id')->toArray();
        $this->assertContains($anotherPersonalTemplate->id, $anotherTemplateIds);
        $this->assertContains($sharedTemplate->id, $anotherTemplateIds);
        $this->assertNotContains($personalTemplate->id, $anotherTemplateIds);
    }

    #[Test]
    public function it_can_scope_shared_templates(): void
    {
        // Create personal template
        $personalTemplate = PredefinedMailTemplate::create([
            'name' => 'Personal Template',
            'subject' => 'Personal Subject',
            'body' => '<p>Personal Body</p>',
            'is_shared' => false,
            'user_id' => $this->testUser->id,
        ]);

        // Create shared templates
        $sharedTemplate1 = PredefinedMailTemplate::create([
            'name' => 'Shared Template 1',
            'subject' => 'Shared Subject 1',
            'body' => '<p>Shared Body 1</p>',
            'is_shared' => true,
            'user_id' => $this->testUser->id,
        ]);

        $sharedTemplate2 = PredefinedMailTemplate::create([
            'name' => 'Shared Template 2',
            'subject' => 'Shared Subject 2',
            'body' => '<p>Shared Body 2</p>',
            'is_shared' => true,
            'user_id' => $this->anotherUser->id,
        ]);

        $sharedTemplates = PredefinedMailTemplate::shared()->get();
        
        $this->assertCount(2, $sharedTemplates);
        $sharedIds = $sharedTemplates->pluck('id')->toArray();
        $this->assertContains($sharedTemplate1->id, $sharedIds);
        $this->assertContains($sharedTemplate2->id, $sharedIds);
        $this->assertNotContains($personalTemplate->id, $sharedIds);
    }

    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $expectedFillable = ['name', 'subject', 'body', 'is_shared', 'user_id'];

        $template = new PredefinedMailTemplate();
        $this->assertEquals($expectedFillable, $template->getFillable());
    }

    #[Test]
    public function it_casts_attributes_correctly(): void
    {
        $template = new PredefinedMailTemplate();
        $casts = $template->getCasts();

        $this->assertEquals('boolean', $casts['is_shared']);
        $this->assertEquals('int', $casts['user_id']);
    }

    #[Test]
    public function it_can_create_template_with_html_body(): void
    {
        $htmlBody = '
            <html>
                <body>
                    <h1>Welcome!</h1>
                    <p>This is a <strong>rich HTML</strong> email template.</p>
                    <ul>
                        <li>Feature 1</li>
                        <li>Feature 2</li>
                    </ul>
                </body>
            </html>
        ';

        $template = PredefinedMailTemplate::create([
            'name' => 'HTML Template',
            'subject' => 'Rich HTML Email',
            'body' => $htmlBody,
            'is_shared' => false,
            'user_id' => $this->testUser->id,
        ]);

        $this->assertEquals($htmlBody, $template->body);
    }

    #[Test]
    public function it_can_create_template_with_plain_text_body(): void
    {
        $plainTextBody = "Welcome!\n\nThis is a plain text email template.\n\nBest regards,\nThe Team";

        $template = PredefinedMailTemplate::create([
            'name' => 'Plain Text Template',
            'subject' => 'Plain Text Email',
            'body' => $plainTextBody,
            'is_shared' => false,
            'user_id' => $this->testUser->id,
        ]);

        $this->assertEquals($plainTextBody, $template->body);
    }

    #[Test]
    public function it_uses_correct_table(): void
    {
        $template = new PredefinedMailTemplate();
        $this->assertEquals('predefined_mail_templates', $template->getTable());
    }

    #[Test]
    public function it_has_timestamps(): void
    {
        $template = new PredefinedMailTemplate();
        $this->assertTrue($template->timestamps);
    }

    #[Test]
    public function it_can_filter_by_name(): void
    {
        $template1 = PredefinedMailTemplate::create([
            'name' => 'Welcome Template',
            'subject' => 'Welcome',
            'body' => '<p>Welcome</p>',
            'is_shared' => false,
            'user_id' => $this->testUser->id,
        ]);

        $template2 = PredefinedMailTemplate::create([
            'name' => 'Goodbye Template',
            'subject' => 'Goodbye',
            'body' => '<p>Goodbye</p>',
            'is_shared' => false,
            'user_id' => $this->testUser->id,
        ]);

        $welcomeTemplates = PredefinedMailTemplate::where('name', 'Welcome Template')->get();
        $goodbyeTemplates = PredefinedMailTemplate::where('name', 'Goodbye Template')->get();

        $this->assertCount(1, $welcomeTemplates);
        $this->assertCount(1, $goodbyeTemplates);
        $this->assertEquals($template1->id, $welcomeTemplates->first()->id);
        $this->assertEquals($template2->id, $goodbyeTemplates->first()->id);
    }
}
