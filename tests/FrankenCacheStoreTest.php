<?php

declare(strict_types=1);

namespace b1tc0re\LaravelFrankenCache\Test;

use b1tc0re\LaravelFrankenCache\FrankenCacheStore;
use b1tc0re\LaravelFrankenCache\Test\Support\TieredCacheFake;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

require_once __DIR__.'/Support/TieredCacheFake.php';
require_once __DIR__.'/Support/TieredCacheFunctions.php';

final class FrankenCacheStoreTest extends PHPUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        TieredCacheFake::reset();
    }

    public function test_get_returns_null_for_a_missing_key_and_prefixes_the_key(): void
    {
        $store = new FrankenCacheStore('test:');

        $this->assertNull($store->get('missing'));
        $this->assertSame([
            'method'    => 'franken_cache_tiered_get',
            'arguments' => ['test:missing'],
        ], TieredCacheFake::lastCall('franken_cache_tiered_get'));
    }

    public function test_get_unserializes_complex_values_and_keeps_numeric_strings_plain(): void
    {
        TieredCacheFake::$values['test:payload'] = serialize(['name' => 'value']);
        TieredCacheFake::$values['test:number']  = '42';
        $store                                   = new FrankenCacheStore('test:');

        $this->assertSame(['name' => 'value'], $store->get('payload'));
        $this->assertSame('42', $store->get('number'));
    }

    public function test_many_returns_values_and_null_for_missing_keys(): void
    {
        TieredCacheFake::$values['test:first']  = serialize('one');
        TieredCacheFake::$values['test:number'] = '42';
        $store                                  = new FrankenCacheStore('test:');

        $this->assertSame([
            'first'   => 'one',
            'missing' => null,
            'number'  => '42',
        ], $store->many(['first', 'missing', 'number']));
        $this->assertSame([
            'method'    => 'franken_cache_tiered_many',
            'arguments' => [['test:first', 'test:missing', 'test:number']],
        ], TieredCacheFake::lastCall('franken_cache_tiered_many'));
    }

    public function test_many_does_not_call_native_api_for_an_empty_key_list(): void
    {
        $store = new FrankenCacheStore('test:');

        $this->assertSame([], $store->many([]));
        $this->assertSame([], TieredCacheFake::$calls);
    }

    public function test_put_serializes_complex_values_and_stores_numeric_values_plain(): void
    {
        $store = new FrankenCacheStore('test:');

        $this->assertTrue($store->put('payload', ['name' => 'value'], 60));
        $this->assertSame(serialize(['name' => 'value']), TieredCacheFake::$values['test:payload']);
        $this->assertSame([
            'method'    => 'franken_cache_tiered_set',
            'arguments' => ['test:payload', serialize(['name' => 'value']), 60],
        ], TieredCacheFake::lastCall('franken_cache_tiered_set'));

        $this->assertTrue($store->put('number', 42, 30));
        $this->assertSame('42', TieredCacheFake::$values['test:number']);
    }

    public function test_put_many_serializes_values_and_returns_false_for_empty_input(): void
    {
        $store = new FrankenCacheStore('test:');

        $this->assertTrue($store->putMany(['name' => 'value', 'number' => 42], 90));
        $this->assertSame([
            'method'    => 'franken_cache_tiered_put_many',
            'arguments' => [[
                'test:name'   => serialize('value'),
                'test:number' => '42',
            ], 90],
        ], TieredCacheFake::lastCall('franken_cache_tiered_put_many'));

        TieredCacheFake::reset();

        $this->assertFalse($store->putMany([], 90));
        $this->assertSame([], TieredCacheFake::$calls);
    }

    public function test_forever_forget_flush_touch_and_add_use_prefixed_keys(): void
    {
        $store = new FrankenCacheStore('test:');

        $this->assertTrue($store->forever('forever', 'value'));
        $this->assertSame(serialize('value'), TieredCacheFake::$values['test:forever']);
        $this->assertSame([
            'method'    => 'franken_cache_tiered_forever',
            'arguments' => ['test:forever', serialize('value')],
        ], TieredCacheFake::lastCall('franken_cache_tiered_forever'));

        $this->assertTrue($store->add('new', 'value', 120));
        $this->assertFalse($store->add('new', 'other', 120));
        $this->assertSame([
            'method'    => 'franken_cache_tiered_add',
            'arguments' => ['test:new', serialize('other'), 120],
        ], TieredCacheFake::lastCall('franken_cache_tiered_add'));

        $this->assertTrue($store->touch('new', 300));
        $this->assertSame([
            'method'    => 'franken_cache_tiered_touch',
            'arguments' => ['test:new', 300],
        ], TieredCacheFake::lastCall('franken_cache_tiered_touch'));

        $this->assertTrue($store->forget('new'));
        $this->assertFalse($store->forget('new'));
        $this->assertTrue($store->flush());
        $this->assertSame([], TieredCacheFake::$values);
    }

    public function test_increment_and_decrement_forward_prefixed_key_and_value(): void
    {
        TieredCacheFake::$values['test:counter'] = '10';
        $store                                   = new FrankenCacheStore('test:');

        $this->assertSame(13, $store->increment('counter', 3));
        $this->assertSame([
            'method'    => 'franken_cache_tiered_increment',
            'arguments' => ['test:counter', 3],
        ], TieredCacheFake::lastCall('franken_cache_tiered_increment'));

        $this->assertSame(11, $store->decrement('counter', 2));
        $this->assertSame([
            'method'    => 'franken_cache_tiered_decrement',
            'arguments' => ['test:counter', 2],
        ], TieredCacheFake::lastCall('franken_cache_tiered_decrement'));
    }

    public function test_prefix_can_be_changed(): void
    {
        $store = new FrankenCacheStore('initial:');

        $this->assertSame('initial:', $store->getPrefix());

        $store->setPrefix('updated:');

        $this->assertSame('updated:', $store->getPrefix());
    }
}
