Flysystem S3
============

For setup instructions see the Flysystem README.md.

## CONFIGURATION ##

The region needs to be set to the region id, not the region name. Here is a list
of the region names and their corresponding ids:

|Region name               |Region id      |
|:-------------------------|:--------------|
|US East (N. Virginia)     |us-east-1      |
|US West (N. California)   |us-west-1      |
|US West (Oregon)          |us-west-2      |
|EU (Ireland)              |eu-west-1      |
|EU (Frankfurt)            |eu-central-1   |
|Asia Pacific (Tokyo)      |ap-northeast-1 |
|Asia Pacific (Seoul)      |ap-northeast-2 |
|Asia Pacific (Singapore)  |ap-southeast-1 |
|Asia Pacific (Sydney)     |ap-southeast-2 |
|South America (Sao Paulo) |sa-east-1      |

Example configuration:

```php
$schemes = [
  's3' => [
    'driver' => 's3',
    'config' => [
      'key'    => '[your key]',
      'secret' => '[your secret]',
      'region' => '[aws-region-id]',
      'bucket' => '[bucket-name]',

      // Optional configuration settings.

      'options' => [
        'ACL' => 'public-read',
        'StorageClass' => 'REDUCED_REDUNDANCY',
      ],

      'protocol' => 'https',             // Will be autodetected based on the current request.

      'prefix' => 'an/optional/prefix',  // Directory prefix for all uploaded/viewed files.

      'cname' => 'static.example.com',   // A cname that resolves to your bucket. Used for URL generation.
    ],

    'cache' => TRUE, // Creates a metadata cache to speed up lookups.
  ],
];

$settings['flysystem'] = $schemes;
```
