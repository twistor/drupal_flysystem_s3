<?php

namespace NoDrupal\Tests\flysystem_s3\Unit;

use Drupal\flysystem_s3\AwsCacheAdapter;
use Drupal\Core\Cache\MemoryBackend;

/**
 * @coversDefaultClass \Drupal\flysystem_s3\AwsCacheAdapter
 * @covers \Drupal\flysystem_s3\AwsCacheAdapter
 * @group flysystem_s3
 */
class AwsCacheAdapterTest extends \PHPUnit_Framework_TestCase {

  public function testBasicGetSetDelete() {
    $backend = new MemoryBackend('foo');
    $cache = new AwsCacheAdapter($backend, 'bar_prefix:');

    $cache->set('key', 'value');
    $this->assertSame('value', $cache->get('key'));

    $backend_item = $backend->get('bar_prefix:key');
    $this->assertSame('value', $backend_item->data);
    $this->assertSame(-1, $backend_item->expire);

    $cache->remove('key');
    $this->assertNull($cache->get('key'));

    $this->assertFalse($backend->get('bar_prefix:key'));
  }

  public function testTtlIsSet() {
    $backend = new MemoryBackend('foo');
    $cache = new AwsCacheAdapter($backend);

    $cache->set('key', 'value', 10);

    // This should work unles the system running the test is extremely slow.
    $expires = time() + 10;

    $this->assertSame('value', $cache->get('key'));

    $backend_item = $backend->get('key');
    $this->assertSame($expires, $backend_item->expire);
  }

}
