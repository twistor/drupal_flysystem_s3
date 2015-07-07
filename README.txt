Flysystem S3
============

For setup instructions see the Flysystem README.txt.

## CONFIGURATION ##

Example configuration:

$schemes = [
  's3example' => [
    'type' => 's3',
    'config' => [
      'key'    => '[your key]',
      'secret' => '[your secret]',
      'region' => '[aws-region]',
      'bucket' => '[bucket-name]',

      // Optional.
      'prefix' => 'an/optional/prefix',  // Directory prefix for all uploaded/viewed files.
      'cname' => 'static.example.com',   // A cname that resolves to your bucket. Used for URL generation.
    ],
  ],
];

$settings['flysystem'] = $schemes;
