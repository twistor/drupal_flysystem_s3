Flysystem S3
============

For setup instructions see the Flysystem README.txt.

## CONFIGURATION ##

See http://docs.aws.amazon.com/aws-sdk-php/v2/guide/configuration.html#client-configuration-options
for a full list of configuration options.

Example configuration:

$schemes = [
  's3example' => [
    'type' => 's3v2',
    'config' => [
      'key'    => '[your key]',
      'secret' => '[your secret]',
      'region' => '[aws-region]',
      'bucket' => '[bucket-name]',

      // Optional.
      'prefix' => 'an/optional/prefix',
      'base_url' => 'http://some.other.endpoint',
      'options' => [
        'StorageClass' => 'REDUCED_REDUNDANCY', // http://aws.amazon.com/s3/details/#RRS
      ],
    ],
  ],
];

$settings['flysystem'] = $schemes;
