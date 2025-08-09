<?php

namespace Turahe\MailClient\Tests\Unit;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\Factories\UserFactory;
use PHPUnit\Framework\Attributes\Test;
use Turahe\MailClient\Models\EmailAccountMessageHeader;
use Turahe\MailClient\Tests\Factories\EmailAccountFactory;
use Turahe\MailClient\Tests\Factories\EmailAccountMessageFactory;
use Turahe\MailClient\Tests\TestCase;

class EmailAccountMessageHeaderTest extends TestCase
{
    use WithFaker;

    protected $testUser;
    protected $testAccount;
    protected $testMessage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testUser = UserFactory::new()->createOne();
        $this->testAccount = EmailAccountFactory::new()->forUser($this->testUser)->create();
        $this->testMessage = EmailAccountMessageFactory::new()->create([
            'email_account_id' => $this->testAccount->id,
        ]);
    }

    #[Test]
    public function it_can_list_all_email_account_message_headers(): void
    {
        EmailAccountMessageHeader::factory()->count(5)->forMessage($this->testMessage)->create();

        $this->assertInstanceOf(Collection::class, EmailAccountMessageHeader::all());
        $this->assertCount(5, EmailAccountMessageHeader::all());
    }

    #[Test]
    public function it_can_create_an_email_account_message_header(): void
    {
        $data = [
            'message_id' => $this->testMessage->id,
            'name' => 'Content-Type',
            'value' => 'text/html; charset=utf-8',
            'header_type' => 'standard',
        ];

        $header = EmailAccountMessageHeader::create($data);

        $this->assertInstanceOf(EmailAccountMessageHeader::class, $header);
        $this->assertEquals($data['message_id'], $header->message_id);
        $this->assertEquals($data['name'], $header->name);
        $this->assertEquals($data['value'], $header->value);
        $this->assertEquals($data['header_type'], $header->header_type);
    }

    #[Test]
    public function it_can_create_different_header_types(): void
    {
        $headers = [
            ['name' => 'Content-Type', 'value' => 'text/html', 'header_type' => 'standard'],
            ['name' => 'X-Custom-Header', 'value' => 'custom-value', 'header_type' => 'custom'],
            ['name' => 'Message-ID', 'value' => '<123@example.com>', 'header_type' => 'id'],
            ['name' => 'Date', 'value' => 'Mon, 1 Jan 2024 12:00:00 +0000', 'header_type' => 'date'],
        ];

        foreach ($headers as $headerData) {
            $header = EmailAccountMessageHeader::create([
                'message_id' => $this->testMessage->id,
                'name' => $headerData['name'],
                'value' => $headerData['value'],
                'header_type' => $headerData['header_type'],
            ]);

            $this->assertEquals($headerData['name'], $header->name);
            $this->assertEquals($headerData['value'], $header->value);
            $this->assertEquals($headerData['header_type'], $header->header_type);
        }

        $this->assertCount(count($headers), EmailAccountMessageHeader::all());
    }

    #[Test]
    public function it_can_update_email_account_message_header(): void
    {
        $header = EmailAccountMessageHeader::create([
            'message_id' => $this->testMessage->id,
            'name' => 'Subject',
            'value' => 'Original Subject',
            'header_type' => 'standard',
        ]);

        $newValue = 'Updated Subject';
        $updated = $header->update(['value' => $newValue]);

        $this->assertTrue($updated);
        $this->assertEquals($newValue, $header->fresh()->value);
    }

    #[Test]
    public function it_can_delete_email_account_message_header(): void
    {
        $header = EmailAccountMessageHeader::create([
            'message_id' => $this->testMessage->id,
            'name' => 'X-Test-Header',
            'value' => 'test-value',
            'header_type' => 'custom',
        ]);

        $deleted = $header->delete();

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('email_account_message_headers', ['id' => $header->id]);
    }

    #[Test]
    public function it_does_not_have_timestamps(): void
    {
        $header = new EmailAccountMessageHeader();
        $this->assertFalse($header->timestamps);
    }

    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $expectedFillable = ['name', 'value', 'header_type', 'message_id'];

        $header = new EmailAccountMessageHeader();
        $this->assertEquals($expectedFillable, $header->getFillable());
    }

    #[Test]
    public function it_casts_attributes_correctly(): void
    {
        $header = new EmailAccountMessageHeader();
        $casts = $header->getCasts();

        $this->assertEquals('string', $casts['message_id']);
    }

    #[Test]
    public function it_can_create_content_type_header(): void
    {
        $header = EmailAccountMessageHeader::create([
            'message_id' => $this->testMessage->id,
            'name' => 'Content-Type',
            'value' => 'text/html; charset=utf-8',
            'header_type' => 'standard',
        ]);

        $this->assertEquals('Content-Type', $header->name);
        $this->assertEquals('text/html; charset=utf-8', $header->value);
    }

    #[Test]
    public function it_can_create_message_id_header(): void
    {
        $messageId = '<' . $this->faker->uuid . '@example.com>';
        $header = EmailAccountMessageHeader::create([
            'message_id' => $this->testMessage->id,
            'name' => 'Message-ID',
            'value' => $messageId,
            'header_type' => 'id',
        ]);

        $this->assertEquals('Message-ID', $header->name);
        $this->assertEquals($messageId, $header->value);
    }

    #[Test]
    public function it_can_create_in_reply_to_header(): void
    {
        $replyToId = '<' . $this->faker->uuid . '@example.com>';
        $header = EmailAccountMessageHeader::create([
            'message_id' => $this->testMessage->id,
            'name' => 'In-Reply-To',
            'value' => $replyToId,
            'header_type' => 'reply',
        ]);

        $this->assertEquals('In-Reply-To', $header->name);
        $this->assertEquals($replyToId, $header->value);
    }

    #[Test]
    public function it_can_create_references_header(): void
    {
        $references = '<ref1@example.com> <ref2@example.com>';
        $header = EmailAccountMessageHeader::create([
            'message_id' => $this->testMessage->id,
            'name' => 'References',
            'value' => $references,
            'header_type' => 'references',
        ]);

        $this->assertEquals('References', $header->name);
        $this->assertEquals($references, $header->value);
    }

    #[Test]
    public function it_can_create_date_header(): void
    {
        $date = 'Mon, 1 Jan 2024 12:00:00 +0000';
        $header = EmailAccountMessageHeader::create([
            'message_id' => $this->testMessage->id,
            'name' => 'Date',
            'value' => $date,
            'header_type' => 'date',
        ]);

        $this->assertEquals('Date', $header->name);
        $this->assertEquals($date, $header->value);
    }

    #[Test]
    public function it_can_create_custom_header(): void
    {
        $header = EmailAccountMessageHeader::create([
            'message_id' => $this->testMessage->id,
            'name' => 'X-Custom-Application',
            'value' => 'Mail Client v1.0',
            'header_type' => 'custom',
        ]);

        $this->assertEquals('X-Custom-Application', $header->name);
        $this->assertEquals('Mail Client v1.0', $header->value);
        $this->assertEquals('custom', $header->header_type);
    }

    #[Test]
    public function it_uses_correct_table(): void
    {
        $header = new EmailAccountMessageHeader();
        $this->assertEquals('email_account_message_headers', $header->getTable());
    }

    #[Test]
    public function it_can_find_headers_by_name(): void
    {
        EmailAccountMessageHeader::create([
            'message_id' => $this->testMessage->id,
            'name' => 'Content-Type',
            'value' => 'text/html',
            'header_type' => 'standard',
        ]);

        EmailAccountMessageHeader::create([
            'message_id' => $this->testMessage->id,
            'name' => 'X-Custom',
            'value' => 'custom-value',
            'header_type' => 'custom',
        ]);

        $contentTypeHeaders = EmailAccountMessageHeader::where('name', 'Content-Type')->get();
        $customHeaders = EmailAccountMessageHeader::where('name', 'X-Custom')->get();

        $this->assertCount(1, $contentTypeHeaders);
        $this->assertCount(1, $customHeaders);
        $this->assertEquals('text/html', $contentTypeHeaders->first()->value);
        $this->assertEquals('custom-value', $customHeaders->first()->value);
    }

    #[Test]
    public function it_allows_long_header_values(): void
    {
        $longValue = str_repeat('This is a very long header value. ', 50);
        $header = EmailAccountMessageHeader::create([
            'message_id' => $this->testMessage->id,
            'name' => 'X-Long-Header',
            'value' => $longValue,
            'header_type' => 'custom',
        ]);

        $this->assertEquals($longValue, $header->value);
    }
}
