<?php

namespace Lostlink\Messenger\Tests\Feature;

use Illuminate\Support\Collection;
use Lostlink\Messenger\Tests\TestCase;

class MacrosTest extends TestCase
{
    public function test_from_json_macro_is_registered(): void
    {
        $this->assertTrue(Collection::hasMacro('fromJson'));
    }

    public function test_recursive_macro_is_registered(): void
    {
        $this->assertTrue(Collection::hasMacro('recursive'));
    }

    public function test_from_json_returns_collection_from_json_array(): void
    {
        $result = Collection::fromJson('[{"a":1},{"b":2}]');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $this->assertSame(['a' => 1], $result->first());
        $this->assertSame(['b' => 2], $result->last());
    }

    public function test_from_json_returns_collection_from_json_object(): void
    {
        $result = Collection::fromJson('{"key":"value","num":42}');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame('value', $result->get('key'));
        $this->assertSame(42, $result->get('num'));
    }

    public function test_recursive_converts_nested_arrays_to_collections(): void
    {
        $result = collect([[1, 2], [3, 4]])->recursive();

        $this->assertInstanceOf(Collection::class, $result);

        // Each nested array should have been converted to a Collection
        $first = $result->first();
        $this->assertInstanceOf(Collection::class, $first);
        $this->assertSame([1, 2], $first->all());

        $last = $result->last();
        $this->assertInstanceOf(Collection::class, $last);
        $this->assertSame([3, 4], $last->all());
    }

    public function test_recursive_leaves_scalar_values_untouched(): void
    {
        $result = collect([1, 'hello', true, null])->recursive();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame(1, $result->get(0));
        $this->assertSame('hello', $result->get(1));
        $this->assertTrue($result->get(2));
        $this->assertNull($result->get(3));
    }
}
