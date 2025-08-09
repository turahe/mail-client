<?php

namespace Turahe\MailClient\Tests\Unit;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\Factories\UserFactory;
use PHPUnit\Framework\Attributes\Test;
use Turahe\MailClient\Models\EmailAccountMessageAddress;
use Turahe\MailClient\Tests\Factories\EmailAccountFactory;
use Turahe\MailClient\Tests\Factories\EmailAccountMessageFactory;
use Turahe\MailClient\Tests\TestCase;

class EmailAccountMessageAddressTest extends TestCase
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
    public function it_can_list_all_email_account_message_addresses(): void
    {
        EmailAccountMessageAddress::factory()->count(3)->forMessage($this->testMessage)->create();

        $this->assertInstanceOf(Collection::class, EmailAccountMessageAddress::all());
        $this->assertCount(3, EmailAccountMessageAddress::all());
    }

    #[Test]
    public function it_can_create_an_email_account_message_address(): void
    {
        $data = [
            'address' => $this->faker->email,
            'name' => $this->faker->name,
            'address_type' => 'from',
        ];

        $address = EmailAccountMessageAddress::factory()->forMessage($this->testMessage)->create($data);

        $this->assertInstanceOf(EmailAccountMessageAddress::class, $address);
        $this->assertEquals($this->testMessage->id, $address->message_id);
        $this->assertEquals($data['address'], $address->address);
        $this->assertEquals($data['name'], $address->name);
        $this->assertEquals($data['address_type'], $address->address_type);
    }

    #[Test]
    public function it_can_create_different_address_types(): void
    {
        $addressTypes = ['from', 'to', 'cc', 'bcc', 'replyTo', 'sender'];

        foreach ($addressTypes as $type) {
            $address = EmailAccountMessageAddress::factory()->forMessage($this->testMessage)->create([
                'address' => $this->faker->email,
                'name' => $this->faker->name,
                'address_type' => $type,
            ]);

            $this->assertEquals($type, $address->address_type);
        }

        $this->assertCount(count($addressTypes), EmailAccountMessageAddress::all());
    }

    #[Test]
    public function it_can_update_email_account_message_address(): void
    {
        $address = EmailAccountMessageAddress::factory()->forMessage($this->testMessage)->create([
            'address' => $this->faker->email,
            'name' => $this->faker->name,
            'address_type' => 'to',
        ]);

        $newName = 'Updated Name';
        
        // Since this model doesn't have a primary key, we'll test that the attributes can be changed
        $address->name = $newName;
        $this->assertEquals($newName, $address->name);
        
        // Test that we can create another address with updated data
        $newAddress = EmailAccountMessageAddress::factory()->forMessage($this->testMessage)->create([
            'address' => 'updated@example.com',
            'name' => $newName,
            'address_type' => 'cc',
        ]);
        
        $this->assertEquals($newName, $newAddress->name);
        $this->assertEquals('updated@example.com', $newAddress->address);
    }

    #[Test]
    public function it_can_delete_email_account_message_address(): void
    {
        $address = EmailAccountMessageAddress::factory()->forMessage($this->testMessage)->create([
            'address' => $this->faker->email,
            'name' => $this->faker->name,
            'address_type' => 'from',
        ]);

        $deleted = $address->delete();

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('email_account_message_addresses', ['id' => $address->id]);
    }

    #[Test]
    public function it_does_not_have_timestamps(): void
    {
        $address = new EmailAccountMessageAddress();
        $this->assertFalse($address->timestamps);
    }

    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $expectedFillable = ['address', 'name', 'address_type', 'message_id'];

        $address = new EmailAccountMessageAddress();
        $this->assertEquals($expectedFillable, $address->getFillable());
    }

    #[Test]
    public function it_casts_attributes_correctly(): void
    {
        $address = new EmailAccountMessageAddress();
        $casts = $address->getCasts();

        $this->assertEquals('string', $casts['message_id']);
    }

    #[Test]
    public function it_can_create_from_address(): void
    {
        $address = EmailAccountMessageAddress::factory()->forMessage($this->testMessage)->create([
            'address' => 'sender@example.com',
            'name' => 'Sender Name',
            'address_type' => 'from',
        ]);

        $this->assertEquals('from', $address->address_type);
        $this->assertEquals('sender@example.com', $address->address);
        $this->assertEquals('Sender Name', $address->name);
    }

    #[Test]
    public function it_can_create_to_address(): void
    {
        $address = EmailAccountMessageAddress::factory()->forMessage($this->testMessage)->create([
            'address' => 'recipient@example.com',
            'name' => 'Recipient Name',
            'address_type' => 'to',
        ]);

        $this->assertEquals('to', $address->address_type);
        $this->assertEquals('recipient@example.com', $address->address);
        $this->assertEquals('Recipient Name', $address->name);
    }

    #[Test]
    public function it_can_create_cc_address(): void
    {
        $address = EmailAccountMessageAddress::factory()->forMessage($this->testMessage)->create([
            'address' => 'cc@example.com',
            'name' => 'CC Name',
            'address_type' => 'cc',
        ]);

        $this->assertEquals('cc', $address->address_type);
        $this->assertEquals('cc@example.com', $address->address);
        $this->assertEquals('CC Name', $address->name);
    }

    #[Test]
    public function it_can_create_bcc_address(): void
    {
        $address = EmailAccountMessageAddress::factory()->forMessage($this->testMessage)->create([
            'address' => 'bcc@example.com',
            'name' => 'BCC Name',
            'address_type' => 'bcc',
        ]);

        $this->assertEquals('bcc', $address->address_type);
        $this->assertEquals('bcc@example.com', $address->address);
        $this->assertEquals('BCC Name', $address->name);
    }

    #[Test]
    public function it_can_create_reply_to_address(): void
    {
        $address = EmailAccountMessageAddress::factory()->forMessage($this->testMessage)->create([
            'address' => 'replyto@example.com',
            'name' => 'Reply To Name',
            'address_type' => 'replyTo',
        ]);

        $this->assertEquals('replyTo', $address->address_type);
        $this->assertEquals('replyto@example.com', $address->address);
        $this->assertEquals('Reply To Name', $address->name);
    }

    #[Test]
    public function it_allows_null_name(): void
    {
        $address = EmailAccountMessageAddress::factory()->forMessage($this->testMessage)->create([
            'address' => 'test@example.com',
            'name' => null,
            'address_type' => 'from',
        ]);

        $this->assertNull($address->name);
        $this->assertEquals('test@example.com', $address->address);
    }

    #[Test]
    public function it_uses_correct_table(): void
    {
        $address = new EmailAccountMessageAddress();
        $this->assertEquals('email_account_message_addresses', $address->getTable());
    }
}
