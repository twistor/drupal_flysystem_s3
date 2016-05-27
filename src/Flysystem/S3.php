<?php

/**
 * @file
 * Contains \Drupal\flysystem_s3\Flysystem\S3.
 */

namespace Drupal\flysystem_s3\Flysystem;

use Aws\AwsClientInterface;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\flysystem\Plugin\FlysystemPluginInterface;
use Drupal\flysystem\Plugin\FlysystemUrlTrait;
use Drupal\flysystem\Plugin\ImageStyleGenerationTrait;
use Drupal\flysystem_s3\Flysystem\Adapter\S3Adapter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drupal plugin for the "S3" Flysystem adapter.
 *
 * @Adapter(id = "s3")
 */
class S3 implements FlysystemPluginInterface, ContainerFactoryPluginInterface {

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
   * @param string $scheme
   *   The current scheme, either 'http' or 'https'.
   */
  public function __construct(AwsClientInterface $client, array $configuration) {
    $this->client = $client;
    $this->bucket = $configuration['bucket'];
    $this->prefix = $configuration['prefix'];
    $this->options = $configuration['options'];

    if ($this->isCnameVirtualHosted($configuration['cname'], $this->bucket) || $this->isNotAws($configuration)) {
      $this->urlPrefix = $configuration['protocol'] . '://' . $configuration['cname'];
    }
    else {
      // us-east-1 doesn't follow the consistent mapping.
      if ($configuration['cname'] === 's3-us-east-1.amazonaws.com') {
        $configuration['cname'] = 's3.amazonaws.com';
      }

      $this->urlPrefix = $configuration['protocol'] . '://' . $configuration['cname'] . '/' . $this->bucket;
    }

    if (strlen($this->prefix)) {
      $this->urlPrefix .= '/' . UrlHelper::encodePath($this->prefix);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $protocol = $container->get('request_stack')->getCurrentRequest()->getScheme();

    $configuration += [
      'prefix' => '',
      'protocol' => $protocol,
      'options' => [],
      'region' => 'us-east-1',
    ];

    $configuration += ['cname' => 's3-' . $configuration['region'] . '.amazonaws.com'];

    $client = new S3Client([
      'version' => 'latest',
      'region' => $configuration['region'],
      'credentials' => new Credentials($configuration['key'], $configuration['secret']),
      'endpoint' => isset($configuration['endpoint']) ? $configuration['endpoint'] : null
    ]);

    unset($configuration['key'], $configuration['secret']);

    return new static($client, $configuration);
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

  /**
   * Detects whether the CNAME uses Virtual Hosted–Style Method.
   *
   * @param string $cname
   *   The CNAME.
   * @param string $bucket
   *   The bucket identifer.
   *
   * @return bool
   *   TRUE if the CNAME uses Virtual Hosted–Style Method. FALSE otherwise.
   *
   * @see http://docs.aws.amazon.com/AmazonS3/latest/dev/VirtualHosting.html
   */
  private function isCnameVirtualHosted($cname, $bucket) {
    return strpos($cname, $bucket) === 0;
  }

  /**
   * Detects if the provided configuration indicates that AWS is not used
   *
   * @param array $configuration
   *   The configuration
   *
   * @return bool
   *   TRUE if the CNAME does not contain amazonaws.com
   */
  private function isNotAws(array $configuration) {
    return isset($configuration['endpoint']) && strpos($configuration['endpoint'], 'amazonaws.com') === false;
  }

}
