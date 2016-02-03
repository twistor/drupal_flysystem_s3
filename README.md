Flysystem S3
============

For setup instructions see the Flysystem README.md.

## CONFIGURATION ##

Example configuration:

```php
$schemes = [
  's3' => [
    'driver' => 's3',
    'config' => [
      'key'    => '[your key]',
      'secret' => '[your secret]',
      'region' => '[aws-region]',
      'bucket' => '[bucket-name]',

      // Optional configuration settings.

      'options' => [
        'ACL' => 'public-read',
        'StorageClass' => 'REDUCED_REDUNDANCY',
      ],

      'protocol' => 'https',

      'prefix' => 'an/optional/prefix',  // Directory prefix for all uploaded/viewed files.

      'cname' => 'static.example.com',   // A cname that resolves to your bucket. Used for URL generation.
    ],

    'cache' => TRUE, // Creates a metadata cache to speed up lookups.
  ],
];

$settings['flysystem'] = $schemes;
```
