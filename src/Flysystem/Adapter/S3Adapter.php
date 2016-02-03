<?php

/**
 * @file
 * Contains \Drupal\flysystem_s3\Flysystem\Adapter\S3Adapter.
 */

namespace Drupal\flysystem_s3\Flysystem\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;

/**
 * Overrides methods so it works with Drupal.
 */
class S3Adapter extends AwsS3Adapter {

  /**
   * {@inheritdoc}
   */
  public function has($path) {
    $location = $this->applyPathPrefix($path);

    if ($this->s3Client->doesObjectExist($this->bucket, $location)) {
      return TRUE;
    }

    // Check for directory existance.
    return $this->s3Client->doesObjectExist($this->bucket, $location . '/');
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata($path) {
    $metadata = parent::getMetadata($path);

    if ($metadata === FALSE) {
      return [
        'type' => 'dir',
        'path' => $path,
        'timestamp' => REQUEST_TIME,
        'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
      ];
    }

    return $metadata;
  }

}
