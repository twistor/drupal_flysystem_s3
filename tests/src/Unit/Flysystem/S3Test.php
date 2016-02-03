<?php

/**
 * @file
 * Contains \NoDrupal\Tests\flysystem_s3\Unit\Flysystem\S3Test.
 */

namespace NoDrupal\Tests\flysystem_s3\Unit\Flysystem;

use Drupal\flysystem_s3\Flysystem\S3;

/**
 * @coversDefaultClass \Drupal\flysystem_s3\Flysystem\S3
 * @group flysystem_s3
 */
class S3Test extends \PHPUnit_Framework_TestCase {

  /**
   * @covers \Drupal\flysystem_s3\Flysystem\S3
   */
  public function test() {
    $configuration = [
      'key'    => 'fee',
      'secret' => 'fo',
      'region' => 'eu-west-1',
      'bucket' => 'example-bucket',

      'prefix' => 'test prefix',

      'options' => ['ACL' => 'public-read'],
    ];

    $plugin = new S3($configuration);

    $this->assertInstanceOf('League\Flysystem\AdapterInterface', $plugin->getAdapter());

    $this->assertSame('http://s3-eu-west-1.amazonaws.com/example-bucket/test%20prefix/foo%201.html', $plugin->getExternalUrl('s3://foo 1.html'));

    unset($configuration['prefix']);

    $plugin = new S3($configuration);
    $this->assertSame('http://s3-eu-west-1.amazonaws.com/example-bucket/foo%201.html', $plugin->getExternalUrl('s3://foo 1.html'));
  }

}
