<?php

/**
 * @file
 * Contains \NoDrupal\Tests\flysystem_s3\Unit\Flysystem\S3Test.
 */

namespace NoDrupal\Tests\flysystem_s3\Unit\Flysystem;

use Aws\AwsClientInterface;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\flysystem_s3\Flysystem\S3;
use League\Flysystem\Config;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

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
      'bucket' => 'example-bucket',
      'prefix' => 'test prefix',
      'cname' => 'example.com',
    ];

    $client = new S3Client([
      'version' => 'latest',
      'region' => 'beep',
      'credentials' => new Credentials('fsdf', 'sfsdf'),
    ]);

    $plugin = new S3($client, new Config($configuration));

    $this->assertInstanceOf('League\Flysystem\AdapterInterface', $plugin->getAdapter());

    $this->assertSame('http://example.com/example-bucket/test%20prefix/foo%201.html', $plugin->getExternalUrl('s3://foo 1.html'));

    $configuration['prefix'] = '';

    $plugin = new S3($client, new Config($configuration));
    $this->assertSame('http://example.com/example-bucket/foo%201.html', $plugin->getExternalUrl('s3://foo 1.html'));
  }

  public function testCreate() {
    $container = new ContainerBuilder();
    $container->set('request_stack', new RequestStack());
    $container->get('request_stack')->push(Request::create('https://example.com/'));

    $configuration = [
      'key'    => 'fee',
      'secret' => 'fo',
      'region' => 'eu-west-1',
      'bucket' => 'example-bucket',
    ];

    $plugin = S3::create($container, $configuration, '', '');
    $this->assertSame('https://s3-eu-west-1.amazonaws.com/example-bucket/foo%201.html', $plugin->getExternalUrl('s3://foo 1.html'));
  }

  public function testEnsure() {
    $client = $this->prophesize(S3ClientInterface::class);
    $client->doesBucketExist(Argument::type('string'))->willReturn(TRUE);
    $plugin = new S3($client->reveal(), new Config(['bucket' => 'example-bucket']));

    $this->assertSame([], $plugin->ensure());

    $client->doesBucketExist(Argument::type('string'))->willReturn(FALSE);
    $plugin = new S3($client->reveal(), new Config(['bucket' => 'example-bucket']));

    $result = $plugin->ensure();
    $this->assertSame(1, count($result));
    $this->assertSame(RfcLogLevel::ERROR, $result[0]['severity']);
  }

}
