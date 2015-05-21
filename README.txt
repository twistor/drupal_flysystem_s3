Flysystem S3
============

For setup instructions see the Flysystem README.txt.

## CONFIGURATION ##

Example configuration:

$schemes = [
  's3example' => [
    'type' => 's3v2',
    'config' => [
      'key'    => '[your key]',
      'secret' => '[your secret]',
      'region' => '[aws-region]',
      'bucket-name' => '[bucket-name]',

      // Optional.
      'prefix' => 'an/optional/prefix',
      'base_url' => 'http://some.other.endpoint',
    ],
  ],
];

$settings['flysystem'] = $schemes;
