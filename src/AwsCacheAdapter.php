<?php

namespace Drupal\flysystem_s3;

use Aws\CacheInterface;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * A Drupal cache adapter for use with the AWS PHP SDK.
 */
class AwsCacheAdapter implements CacheInterface {

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cache;

  /**
   * The cache prefix.
   *
   * @var string
   */
  private $prefix;

  /**
   * Constructs an AwsCacheAdapter object.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The Drupal cache backend.
   * @param string $prefix
   *   (Optional) The prefix to use for cache items. Defaults to an empty
   *   string.
   */
  public function __construct(CacheBackendInterface $cache, $prefix = '') {
    $this->cache = $cache;
    $this->prefix = $prefix;
  }

  /**
   * {@inheritdoc}
   */
  public function get($key) {
    if ($item = $this->cache->get($this->prefix . $key)) {
      return $item->data;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function set($key, $value, $ttl = 0) {
    $ttl = (int) $ttl;
    $ttl = $ttl === 0 ? CacheBackendInterface::CACHE_PERMANENT : time() + $ttl;

    $this->cache->set($this->prefix . $key, $value, $ttl);
  }

  /**
   * {@inheritdoc}
   */
  public function remove($key) {
    $this->cache->delete($this->prefix . $key);
  }

}
