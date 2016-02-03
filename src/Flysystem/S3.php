<?php

/**
 * @file
 * Contains \Drupal\flysystem_s3\Flysystem\S3.
 */

namespace Drupal\flysystem_s3\Flysystem;

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\flysystem\Plugin\FlysystemPluginInterface;
use Drupal\flysystem\Plugin\FlysystemUrlTrait;
use Drupal\flysystem\Plugin\ImageStyleGenerationTrait;
use Drupal\flysystem_s3\Flysystem\Adapter\S3Adapter;

/**
 * Drupal plugin for the "S3" Flysystem adapter.
 *
 * @Adapter(id = "s3")
 */
class S3 implements FlysystemPluginInterface {

  use ImageStyleGenerationTrait;
  use FlysystemUrlTrait { getExternalUrl as getDownloadlUrl; }

  /**
   * The S3 bucket.
   *
   * @var string
   */
  protected $bucket;

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
   * The URL prefix.
   *
   * @var string
   */
  protected $urlPrefix;

  /**
   * Constructs a S3v3 object.
   *
   * @param array $configuration
   *   Plugin configuration array.
   */
  public function __construct(array $configuration) {
    $this->bucket = $configuration['bucket'];
    $this->prefix = isset($configuration['prefix']) ? $configuration['prefix'] : '';
    $this->options = !empty($configuration['options']) ? $configuration['options'] : [];

    $region = isset($configuration['region']) ? $configuration['region'] : 'us-east-1';
    $protocol = isset($configuration['protocol']) ? $configuration['protocol'] : 'http';
    $cname = isset($configuration['cname']) ? $configuration['cname'] : 's3-' . $region . '.amazonaws.com';

    $this->urlPrefix = $protocol . '://' . $cname . '/' . $this->bucket;

    if (strlen($this->prefix)) {
      $this->urlPrefix .= '/' . UrlHelper::encodePath($this->prefix);
    }

    $credentials = new Credentials($configuration['key'], $configuration['secret']);

    $this->client = new S3Client([
      'version' => 'latest',
      'region' => $region,
      'credentials' => $credentials,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getAdapter() {
    return new S3Adapter($this->client, $this->bucket, $this->prefix, $this->options);
  }

  /**
   * {@inheritdoc}
   */
  public function getExternalUrl($uri) {
    $target = $this->getTarget($uri);

    if (strpos($target, 'styles/') === 0 && !file_exists($uri)) {
      $this->generateImageStyle($target);
    }

    return $this->urlPrefix . '/' . UrlHelper::encodePath($target);
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
