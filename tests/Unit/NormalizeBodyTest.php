<?php

namespace Lostlink\Messenger\Tests\Unit;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Lostlink\Messenger\Actions\NormalizeBody;
use Lostlink\Messenger\Tests\TestCase;

class NormalizeBodyTest extends TestCase
{
    private NormalizeBody $action;

    public function setUp(): void
    {
        parent::setUp();
        $this->action = new NormalizeBody();
    }

    public function test_string_passes_through(): void
    {
        $result = ($this->action)('hello world');

        $this->assertSame('hello world', $result);
        $this->assertStringEndsNotWith("\n", $result);
    }

    public function test_array_is_json_encoded(): void
    {
        $result = ($this->action)(['foo' => 'bar']);

        $this->assertSame('{"foo":"bar"}', $result);
    }

    public function test_arrayable_uses_to_array(): void
    {
        $called = false;

        $arrayable = new class ($called) implements Arrayable {
            public function __construct(private bool &$called) {}

            public function toArray(): array
            {
                $this->called = true;

                return ['key' => 'value'];
            }
        };

        $result = ($this->action)($arrayable);

        $this->assertTrue($called, 'toArray() was not called');
        $this->assertSame('{"key":"value"}', $result);
    }

    public function test_jsonable_uses_to_json(): void
    {
        $called = false;

        $jsonable = new class ($called) implements Jsonable {
            public function __construct(private bool &$called) {}

            public function toJson($options = 0): string
            {
                $this->called = true;

                return '{"jsonable":true}';
            }
        };

        $result = ($this->action)($jsonable);

        $this->assertTrue($called, 'toJson() was not called');
        $this->assertSame('{"jsonable":true}', $result);
    }

    public function test_json_serializable_uses_json_serialize(): void
    {
        $serializable = new class implements \JsonSerializable {
            public function jsonSerialize(): mixed
            {
                return ['serialized' => 1];
            }
        };

        $result = ($this->action)($serializable);

        $this->assertSame('{"serialized":1}', $result);
    }

    public function test_no_trailing_newline_on_any_type(): void
    {
        $result = ($this->action)(['a' => 'b']);

        $this->assertStringEndsNotWith("\n", $result);
    }

    public function test_invalid_utf8_throws_json_exception(): void
    {
        $this->expectException(\JsonException::class);

        // Invalid UTF-8 byte sequence inside an array forces json_encode to fail
        ($this->action)(["\xFF\xFE"]);
    }
}
