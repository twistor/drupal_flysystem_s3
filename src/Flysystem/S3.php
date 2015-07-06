<?php

/**
 * @file
 * Contains \Drupal\flysystem_s3\Flysystem\S3.
 */

namespace Drupal\flysystem_s3\Flysystem;

use Aws\S3\S3Client;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\flysystem\Plugin\FlysystemPluginInterface;
use Drupal\flysystem\Plugin\FlysystemUrlTrait;
use League\Flysystem\AwsS3v2\AwsS3Adapter;

/**
 * Drupal plugin for the "S3" Flysystem adapter.
 *
 * @Adapter(id = "s3v2")
 */
class S3 implements FlysystemPluginInterface {

    use FlysystemUrlTrait { getExternalUrl as getDownloadlUrl; }

  /**
   * The S3 Flysystem adapter.
   *
   * @var \League\Flysystem\AdapterInterface
   */
  protected $adapter;

  /**
   * The S3 bucket.
   *
   * @var string
   */
  protected $bucket;

  /**
   * The S3 client.
   *
   * @var \Aws\Common\Client\AwsClientInterface
   */
  protected $client;

  /**
   * Options to pass into \League\Flysystem\AwsS3v2\AwsS3Adapter.
   *
   * @var array
   */
  protected $options;

  /**
   * The path prefix inside the bucket.
   *
   * @var string
   */
  protected $prefix;

  /**
   * Constructs a S3v2 object.
   *
   * @param array $configuration
   *   Plugin configuration array.
   */
  public function __construct(array $configuration) {
    $this->bucket = $configuration['bucket'];
    $this->prefix = isset($configuration['prefix']) ? (string) $configuration['prefix'] : NULL;
    $this->options = !empty($configuration['options']) ? $configuration['options'] : [];

    unset(
      $configuration['bucket'],
      $configuration['prefix'],
      $configuration['options']
    );

    // @todo Put this in the container.
    $this->client = S3Client::factory($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function getAdapter() {
    if (!isset($this->adapter)) {
      $this->adapter = new AwsS3Adapter($this->client, $this->bucket, $this->prefix, $this->options);
    }

    return $this->adapter;
  }

  /**
   * {@inheritdoc}
   */
  public function getExternalUrl($uri) {
    $target = $this->getTarget($uri);

    // Support image style generation.
    if (strpos($target, 'styles/') === 0 && !$this->getAdapter()->has($target)) {
      return $this->getDownloadlUrl($uri);
    }

    return $this->client->getExternalUrl($this->bucket, $target);
  }

  /**
   * {@inheritdoc}
   */
  public function ensure($force = FALSE) {
    // @TODO: If the bucket exists, can we write to it? Find a way to test that.
    if (!$this->client->doesBucketExist($this->bucket)) {
      return [[
        'severity' => RfcLogLevel::ERROR,
        'message' => 'Bucket %bucket does not exist.',
        'context' => [
          '%bucket' => $this->bucket,
        ],
      ]];
    }

    return [];
  }
}
