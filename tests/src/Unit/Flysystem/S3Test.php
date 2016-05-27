<?php

/**
 * @file
 * Contains \NoDrupal\Tests\flysystem_s3\Unit\Flysystem\S3Test.
 */

namespace NoDrupal\Tests\flysystem_s3\Unit\Flysystem;

use Aws\AwsClientInterface;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\flysystem_s3\Flysystem\S3;
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
      'region' => 'eu-west-1',
      'bucket' => 'example-bucket',

      'prefix' => 'test prefix',

      'options' => ['ACL' => 'public-read'],
      'protocol' => 'http',
      'cname' => 'example.com',
    ];

    $client = new S3Client([
      'version' => 'latest',
      'region' => 'beep',
      'credentials' => new Credentials('fsdf', 'sfsdf'),
    ]);

    $plugin = new S3($client, $configuration);

    $this->assertInstanceOf('League\Flysystem\AdapterInterface', $plugin->getAdapter());

    $this->assertSame('http://example.com/example-bucket/test%20prefix/foo%201.html', $plugin->getExternalUrl('s3://foo 1.html'));

    $configuration['prefix'] = '';

    $plugin = new S3($client, $configuration);
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

  public function testCreateUsingNonAwsConfiguration() {
    $container = new ContainerBuilder();
    $container->set('request_stack', new RequestStack());
    $container->get('request_stack')->push(Request::create('https://example.com/'));

    $configuration = [
      'key'      => 'fee',
      'secret'   => 'fo',
      'region'   => 'eu-west-1',
      'bucket'   => 'example-bucket',
      'cname'    => 'something.somewhere.tld',
      'endpoint' => 'https://api.somewhere.tld'
    ];

    $plugin = S3::create($container, $configuration, '', '');
    $this->assertSame('https://something.somewhere.tld/foo%201.html', $plugin->getExternalUrl('s3://foo 1.html'));
    $this->assertSame($plugin->getAdapter()->getClient()->getEndpoint(). '', 'https://api.somewhere.tld');
  }

  public function testEnsure() {
    $configuration = [
      'region' => 'eu-west-1',
      'bucket' => 'example-bucket',

      'prefix' => 'test prefix',

      'options' => ['ACL' => 'public-read'],
      'protocol' => 'http',
      'cname' => 'example.com',
    ];

    $client = $this->prophesize(S3Client::class);
    $client->doesBucketExist(Argument::type('string'))->willReturn(TRUE);
    $plugin = new S3($client->reveal(), $configuration);

    $this->assertSame([], $plugin->ensure());

    $client->doesBucketExist(Argument::type('string'))->willReturn(FALSE);
    $plugin = new S3($client->reveal(), $configuration);

    $result = $plugin->ensure();
    $this->assertSame(1, count($result));
    $this->assertSame(RfcLogLevel::ERROR, $result[0]['severity']);
  }

}
