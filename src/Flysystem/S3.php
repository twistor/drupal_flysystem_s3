<?php

/**
 * @file
 * Contains \Drupal\flysystem_s3\Flysystem\S3.
 */

namespace Drupal\flysystem_s3\Flysystem;

use Aws\S3\S3Client;
use Aws\Credentials\Credentials;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\flysystem\Plugin\FlysystemPluginInterface;
use Drupal\flysystem\Plugin\FlysystemUrlTrait;
use League\Flysystem\AwsS3v3\AwsS3Adapter;

/**
 * Drupal plugin for the "S3" Flysystem adapter.
 *
 * @Adapter(id = "s3")
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
   * The protocol used to generate the external URL.
   *
   * @var string
   */
  protected $protocol;

  /**
   * The hostname used to generate the external URL.
   *
   * @var string
   */
  protected $cname;

  /**
   * The S3 region
   *
   * @var string
   */
  protected $region;

  /**
   * The S3 client.
   *
   * @var \Aws\AwsClientInterface
   */
  protected $client;

  /**
   * Options to pass into \League\Flysystem\AwsS3v3\AwsS3Adapter.
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
   * Constructs a S3v3 object.
   *
   * @param array $configuration
   *   Plugin configuration array.
   */
  public function __construct(array $configuration) {
    $this->bucket = $configuration['bucket'];
    $this->prefix = isset($configuration['prefix']) ? (string) $configuration['prefix'] : NULL;
    $this->options = !empty($configuration['options']) ? $configuration['options'] : [];
    $this->region = isset($configuration['region']) ? (string) $configuration['region'] : 'us-east-1';
    $this->protocol = isset($configuration['protocol']) ? (string) $configuration['protocol'] : 'http';
    $this->cname = isset($configuration['cname']) ? (string) $configuration['cname'] : $this->bucket . '.s3.amazonaws.com';

    // @todo Put this in the container.
    $credentials = new Credentials($configuration['key'], $configuration['secret']);
    $this->client = new S3Client([
      'version' => 'latest',
      'region' => $this->region,
      'credentials' => $credentials,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getAdapter() {
    if (!isset($this->adapter)) {
      // @todo: The v3 S3 adapter doesn't take the $options param, which makes RRS impossible for now.
      // @see https://github.com/thephpleague/flysystem-aws-s3-v3/issues/31
      $this->adapter = new AwsS3Adapter($this->client, $this->bucket, $this->prefix);
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

    return $this->protocol . '://' . $this->cname . '/' . $this->prefix . '/' . $target;
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
