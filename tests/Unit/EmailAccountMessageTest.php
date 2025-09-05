<?php

namespace Turahe\MailClient\Tests\Unit;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\Factories\UserFactory;
use PHPUnit\Framework\Attributes\Test;
use Turahe\MailClient\Models\EmailAccount;
use Turahe\MailClient\Models\EmailAccountMessage;
use Turahe\MailClient\Models\EmailAccountMessageAddress;
use Turahe\MailClient\Models\EmailAccountMessageHeader;
use Turahe\MailClient\Support\EmailAccountMessageBody;
use Turahe\MailClient\Tests\Factories\EmailAccountFactory;
use Turahe\MailClient\Tests\Factories\EmailAccountFolderFactory;
use Turahe\MailClient\Tests\Factories\EmailAccountMessageFactory;
use Turahe\MailClient\Tests\TestCase;

class EmailAccountMessageTest extends TestCase
{
    use WithFaker;

    protected $testUser;

    protected $testAccount;

    protected $testFolder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testUser = UserFactory::new()->createOne();
        $this->testAccount = EmailAccountFactory::new()->forUser($this->testUser)->create();
        $this->testFolder = EmailAccountFolderFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);
    }

    #[Test]
    public function it_can_list_all_email_account_messages(): void
    {
        EmailAccountMessageFactory::new()->count(3)->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        $this->assertInstanceOf(Collection::class, EmailAccountMessage::all());
        $this->assertCount(3, EmailAccountMessage::all());
    }

    #[Test]
    public function it_can_create_an_email_account_message(): void
    {
        $data = [
            'email_account_id' => $this->testAccount->id,
            'remote_id' => $this->faker->uuid,
            'message_id' => $this->faker->uuid,
            'subject' => $this->faker->sentence,
            'html_body' => '<p>'.$this->faker->paragraph.'</p>',
            'text_body' => $this->faker->paragraph,
            'is_read' => false,
            'is_draft' => false,
            'date' => now(),
            'is_sent_via_app' => false,
            'hash' => hash('sha256', $this->faker->text),
        ];

        $message = EmailAccountMessage::create($data);

        $this->assertInstanceOf(EmailAccountMessage::class, $message);
        $this->assertEquals($data['email_account_id'], $message->email_account_id);
        $this->assertEquals($data['remote_id'], $message->remote_id);
        $this->assertEquals($data['subject'], $message->subject);
        $this->assertEquals($data['html_body'], $message->html_body);
        $this->assertEquals($data['text_body'], $message->text_body);
        $this->assertFalse($message->is_read);
        $this->assertFalse($message->is_draft);
        $this->assertFalse($message->is_sent_via_app);
    }

    #[Test]
    public function it_can_update_email_account_message(): void
    {
        $message = EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'is_read' => false,
        ]);

        $updated = $message->update(['is_read' => true]);

        $this->assertTrue($updated);
        $this->assertTrue($message->fresh()->is_read);
    }

    #[Test]
    public function it_can_delete_email_account_message(): void
    {
        $message = EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        $deleted = $message->delete();

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('email_account_messages', ['id' => $message->id]);
    }

    #[Test]
    public function it_belongs_to_email_account(): void
    {
        $message = EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        $this->assertInstanceOf(EmailAccount::class, $message->account);
        $this->assertEquals($this->testAccount->id, $message->account->id);
    }

    #[Test]
    public function it_belongs_to_many_folders(): void
    {
        $message = EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        $message->folders()->attach($this->testFolder->id);

        $this->assertInstanceOf(Collection::class, $message->folders);
        $this->assertCount(1, $message->folders);
        $this->assertEquals($this->testFolder->id, $message->folders->first()->id);
    }

    #[Test]
    public function it_has_many_addresses(): void
    {
        $message = EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        $address = EmailAccountMessageAddress::create([
            'message_id' => $message->id,
            'address' => $this->faker->email,
            'name' => $this->faker->name,
            'address_type' => 'from',
        ]);

        $this->assertInstanceOf(Collection::class, $message->addresses);
        $this->assertCount(1, $message->addresses);
        $this->assertEquals($address->address, $message->addresses->first()->address);
        $this->assertEquals($address->address_type, $message->addresses->first()->address_type);
    }

    #[Test]
    public function it_has_many_headers(): void
    {
        $message = EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        $header = EmailAccountMessageHeader::create([
            'message_id' => $message->id,
            'name' => 'Content-Type',
            'value' => 'text/html',
            'header_type' => 'standard',
        ]);

        $this->assertInstanceOf(Collection::class, $message->headers);
        $this->assertCount(1, $message->headers);
        $this->assertEquals($header->id, $message->headers->first()->id);
    }

    #[Test]
    public function it_can_determine_if_message_is_reply(): void
    {
        $message = EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        // Message without reply headers
        $this->assertFalse($message->isReply());

        // Add in-reply-to header
        EmailAccountMessageHeader::create([
            'message_id' => $message->id,
            'name' => 'in-reply-to',
            'value' => '<previous-message-id@example.com>',
            'header_type' => 'standard',
        ]);

        $message->refresh();
        $this->assertTrue($message->isReply());
    }

    #[Test]
    public function it_has_body_method(): void
    {
        $message = EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'html_body' => '<p>Test body</p>',
            'text_body' => 'Test body',
        ]);

        $body = $message->body();

        $this->assertInstanceOf(EmailAccountMessageBody::class, $body);
    }

    #[Test]
    public function it_has_preview_text_attribute(): void
    {
        $message = EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'text_body' => 'This is a test message body for preview.',
        ]);

        $previewText = $message->preview_text;

        $this->assertIsString($previewText);
    }

    #[Test]
    public function it_can_scope_unread_messages(): void
    {
        EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'is_read' => false,
        ]);

        EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'is_read' => true,
        ]);

        $unreadMessages = EmailAccountMessage::unread()->get();

        $this->assertCount(1, $unreadMessages);
        $this->assertFalse($unreadMessages->first()->is_read);
    }

    #[Test]
    public function it_can_scope_read_messages(): void
    {
        EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'is_read' => false,
        ]);

        EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'is_read' => true,
        ]);

        $readMessages = EmailAccountMessage::read()->get();

        $this->assertCount(1, $readMessages);
        $this->assertTrue($readMessages->first()->is_read);
    }

    #[Test]
    public function it_can_scope_messages_of_folder(): void
    {
        $message1 = EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);
        $message1->folders()->attach($this->testFolder->id);

        $message2 = EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);

        $folderMessages = EmailAccountMessage::ofFolder($this->testFolder->id)->get();

        $this->assertCount(1, $folderMessages);
        $this->assertEquals($message1->id, $folderMessages->first()->id);
    }

    #[Test]
    public function it_can_scope_where_remote_ids_in(): void
    {
        $remoteId1 = $this->faker->uuid;
        $remoteId2 = $this->faker->uuid;
        $remoteId3 = $this->faker->uuid;

        EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'remote_id' => $remoteId1,
        ]);

        EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'remote_id' => $remoteId2,
        ]);

        EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
            'remote_id' => $remoteId3,
        ]);

        $messages = EmailAccountMessage::whereRemoteIdsIn([$remoteId1, $remoteId2])->get();

        $this->assertCount(2, $messages);
        $this->assertTrue($messages->pluck('remote_id')->contains($remoteId1));
        $this->assertTrue($messages->pluck('remote_id')->contains($remoteId2));
        $this->assertFalse($messages->pluck('remote_id')->contains($remoteId3));
    }

    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $expectedFillable = [
            'email_account_id', 'remote_id', 'message_id',
            'subject', 'html_body', 'text_body', 'is_read',
            'is_draft', 'date', 'is_sent_via_app', 'hash',
        ];

        $message = new EmailAccountMessage;
        $this->assertEquals($expectedFillable, $message->getFillable());
    }

    #[Test]
    public function it_casts_attributes_correctly(): void
    {
        $message = new EmailAccountMessage;
        $casts = $message->getCasts();

        $this->assertEquals('datetime', $casts['date']);
        $this->assertEquals('boolean', $casts['is_draft']);
        $this->assertEquals('boolean', $casts['is_read']);
        $this->assertEquals('boolean', $casts['is_sent_via_app']);
        $this->assertEquals('string', $casts['email_account_id']);
    }

    #[Test]
    public function it_has_correct_timeline_sort_column(): void
    {
        $message = new EmailAccountMessage;
        $this->assertEquals('date', $message->getTimelineSortColumn());
    }

    #[Test]
    public function it_has_correct_timeline_relation(): void
    {
        $message = new EmailAccountMessage;
        $this->assertEquals('emails', $message->getTimelineRelation());
    }

    #[Test]
    public function it_has_correct_timeline_component(): void
    {
        $message = new EmailAccountMessage;
        $this->assertEquals('record-tab-timeline-email', $message->getTimelineComponent());
    }

    #[Test]
    public function it_has_constants_defined(): void
    {
        $this->assertEquals('messages', EmailAccountMessage::TAGS_TYPE);
        $this->assertEquals('attachments', EmailAccountMessage::ATTACHMENTS_MEDIA_TAG);
        $this->assertEquals('embedded-attachments', EmailAccountMessage::EMBEDDED_ATTACHMENTS_MEDIA_TAG);
    }
}
