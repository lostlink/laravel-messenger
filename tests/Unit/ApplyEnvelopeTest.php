<?php

namespace Lostlink\Messenger\Tests\Unit;

use Lostlink\Messenger\Actions\ApplyEnvelope;
use Lostlink\Messenger\Message;
use Lostlink\Messenger\Tests\TestCase;

class ApplyEnvelopeTest extends TestCase
{
    private ApplyEnvelope $action;

    public function setUp(): void
    {
        parent::setUp();
        $this->action = new ApplyEnvelope();
    }

    public function test_injects_uuid_when_enabled(): void
    {
        $message = new Message('body', 'log');
        $config = ['envelope' => ['uuid' => true, 'timestamp' => false]];

        $result = ($this->action)($message, $config);

        $this->assertArrayHasKey('_id', $result->attributes);
        $this->assertNotEmpty($result->attributes['_id']);
    }

    public function test_does_not_override_existing_uuid(): void
    {
        $original = 'existing-uuid';
        $message = new Message('body', 'log', ['_id' => $original]);
        $config = ['envelope' => ['uuid' => true, 'timestamp' => false]];

        $result = ($this->action)($message, $config);

        $this->assertSame($original, $result->attributes['_id']);
    }

    public function test_injects_timestamp_when_enabled(): void
    {
        $message = new Message('body', 'log');
        $config = ['envelope' => ['uuid' => false, 'timestamp' => true]];

        $result = ($this->action)($message, $config);

        $this->assertArrayHasKey('_sent_at', $result->attributes);
        $this->assertNotEmpty($result->attributes['_sent_at']);
    }

    public function test_does_not_override_existing_timestamp(): void
    {
        $original = '2020-01-01T00:00:00+00:00';
        $message = new Message('body', 'log', ['_sent_at' => $original]);
        $config = ['envelope' => ['uuid' => false, 'timestamp' => true]];

        $result = ($this->action)($message, $config);

        $this->assertSame($original, $result->attributes['_sent_at']);
    }

    public function test_no_mutation_when_disabled(): void
    {
        $message = new Message('body', 'log', ['custom' => 'value']);
        $config = ['envelope' => ['uuid' => false, 'timestamp' => false]];

        $result = ($this->action)($message, $config);

        $this->assertArrayNotHasKey('_id', $result->attributes);
        $this->assertArrayNotHasKey('_sent_at', $result->attributes);
        $this->assertSame(['custom' => 'value'], $result->attributes);
    }

    public function test_returns_new_message_instance(): void
    {
        $message = new Message('body', 'log');
        $config = ['envelope' => ['uuid' => false, 'timestamp' => false]];

        $result = ($this->action)($message, $config);

        $this->assertNotSame($message, $result);
    }
}
