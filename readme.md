# arquivei/flysystems-php

Google Cloud Storage and Amazon S3 adapters for php.

This project is based-on League\Flysystem [http://flysystem.thephpleague.com/docs/].

## Installation

```bash
composer require arquivei/flysystems-php
```

## How to use

#### Using with Google Cloud Storage

```php

require_once ('vendor/autoload.php');

$gcsStorage = new \Arquivei\Flysystems\ArquiveiStorage\Adapters\GoogleCloudStorage(
    new \Google\Cloud\Storage\StorageClient([
        'projectId' => 'my-project',
        'keyFilePath' => './auth.json'
    ])
);

$gcsStorage->setBucket('my-bucket');

$gcsStorage->putObject('data', '2019/key/');

```

#### Using with Amazon Aws S3

```php
$awsStorage = new \Arquivei\Flysystems\ArquiveiStorage\Adapters\AmazonAwsStorage(
    new \Aws\S3\S3Client([
        'key' => 'my-key',
        'secret' => 'my-secret',
        'region' => 'my-region',
        'version' => 'my-version'
    ])
);

$awsStorage->setBucket('my-bucket');

$awsStorage->putObject('data', '2019/key/');

```

#### Using with Laravel

The first step you need to do is register the service provider in app.php

```php
'providers' => [
    Arquivei\Flysystems\GoogleCloudStorage\GoogleCloudStorageServiceProvider::class,
]
```

Then, create the config in filesystem.php


```php
'gcs' => [
    'driver' => 'gcs',
    'project_id' => env('GOOGLE_CLOUD_PROJECT_ID', 'your-project-id'),
    'key_file' => env('GOOGLE_CLOUD_KEY_FILE', null),
    'bucket' => env('GOOGLE_CLOUD_STORAGE_BUCKET', 'your-bucket'),
    'path_prefix' => env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX', null),
    'storage_api_uri' => env('GOOGLE_CtestingLOUD_STORAGE_API_URI', null), 
    'visibility' =>  env('GOOGLE_CLOUD_STORAGE_API_URI', 'private'), 
],
```


