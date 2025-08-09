<?php

namespace Turahe\MailClient\Tests\Unit;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Turahe\MailClient\Models\MessageLinksClick;
use Turahe\MailClient\Tests\TestCase;

class MessageLinksClickTest extends TestCase
{
    use WithFaker;

    #[Test]
    public function it_can_list_all_message_link_clicks(): void
    {
        MessageLinksClick::factory()->count(5)->create();

        $this->assertInstanceOf(Collection::class, MessageLinksClick::all());
        $this->assertCount(5, MessageLinksClick::all());
    }

    #[Test]
    public function it_can_create_a_message_link_click(): void
    {
        $data = [
            'url' => $this->faker->url,
            'message_id' => 1,
        ];

        $click = MessageLinksClick::create($data);

        $this->assertInstanceOf(MessageLinksClick::class, $click);
        $this->assertEquals($data['url'], $click->url);
        $this->assertEquals($data['message_id'], $click->message_id);
    }

    #[Test]
    public function it_can_update_message_link_click(): void
    {
        $click = MessageLinksClick::create([
            'url' => 'https://example.com/old',
            'message_id' => 1,
        ]);

        $newUrl = 'https://example.com/new';
        $updated = $click->update(['url' => $newUrl]);

        $this->assertTrue($updated);
        $this->assertEquals($newUrl, $click->fresh()->url);
    }

    #[Test]
    public function it_can_delete_message_link_click(): void
    {
        $click = MessageLinksClick::create([
            'url' => $this->faker->url,
            'message_id' => 1,
        ]);

        $deleted = $click->delete();

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('message_links_clicks', ['id' => $click->id]);
    }

    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $expectedFillable = ['url'];

        $click = new MessageLinksClick();
        $this->assertEquals($expectedFillable, $click->getFillable());
    }

    #[Test]
    public function it_uses_correct_table(): void
    {
        $click = new MessageLinksClick();
        $this->assertEquals('message_links_clicks', $click->getTable());
    }

    #[Test]
    public function it_has_timestamps(): void
    {
        $click = new MessageLinksClick();
        $this->assertTrue($click->timestamps);
    }

    #[Test]
    public function it_can_store_various_url_formats(): void
    {
        $urls = [
            'https://example.com',
            'http://subdomain.example.com/path',
            'https://example.com/path?param=value&other=123',
            'https://example.com/path#anchor',
            'https://example.com:8080/path',
        ];

        foreach ($urls as $url) {
            $click = MessageLinksClick::create([
                'url' => $url,
                'message_id' => 1,
            ]);

            $this->assertEquals($url, $click->url);
        }

        $this->assertCount(count($urls), MessageLinksClick::all());
    }

    #[Test]
    public function it_can_find_clicks_by_url(): void
    {
        $url = 'https://example.com/specific-url';
        
        MessageLinksClick::create([
            'url' => $url,
            'message_id' => 1,
        ]);

        MessageLinksClick::create([
            'url' => 'https://different.com',
            'message_id' => 1,
        ]);

        $specificClicks = MessageLinksClick::where('url', $url)->get();
        $this->assertCount(1, $specificClicks);
        $this->assertEquals($url, $specificClicks->first()->url);
    }

    #[Test]
    public function it_can_track_multiple_clicks_for_same_url(): void
    {
        $url = 'https://example.com/popular-link';

        // Create multiple clicks for the same URL
        MessageLinksClick::create(['url' => $url, 'message_id' => 1]);
        MessageLinksClick::create(['url' => $url, 'message_id' => 2]);
        MessageLinksClick::create(['url' => $url, 'message_id' => 3]);

        $clicksForUrl = MessageLinksClick::where('url', $url)->get();
        $this->assertCount(3, $clicksForUrl);
    }

    #[Test]
    public function it_can_group_clicks_by_message(): void
    {
        $messageId1 = 1;
        $messageId2 = 2;

        MessageLinksClick::create(['url' => 'https://example.com/link1', 'message_id' => $messageId1]);
        MessageLinksClick::create(['url' => 'https://example.com/link2', 'message_id' => $messageId1]);
        MessageLinksClick::create(['url' => 'https://example.com/link3', 'message_id' => $messageId2]);

        $clicksForMessage1 = MessageLinksClick::where('message_id', $messageId1)->get();
        $clicksForMessage2 = MessageLinksClick::where('message_id', $messageId2)->get();

        $this->assertCount(2, $clicksForMessage1);
        $this->assertCount(1, $clicksForMessage2);
    }

    #[Test]
    public function it_can_handle_long_urls(): void
    {
        $longUrl = 'https://example.com/very/long/path/with/many/segments/and/parameters?' . 
                   'param1=value1&param2=value2&param3=value3&param4=value4&param5=value5' .
                   '&param6=value6&param7=value7&param8=value8&param9=value9&param10=value10';

        $click = MessageLinksClick::create([
            'url' => $longUrl,
            'message_id' => 1,
        ]);

        $this->assertEquals($longUrl, $click->url);
    }
}
