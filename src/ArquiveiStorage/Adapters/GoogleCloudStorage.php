<?php

namespace Arquivei\Flysystems\ArquiveiStorage\Adapters;

use Arquivei\Flysystems\ArquiveiStorage\AbstractStorage;
use Arquivei\Flysystems\ArquiveiStorage\ArquiveiStorageException;
use Arquivei\Flysystems\ArquiveiStorage\StorageInterface;
use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\Storage\StorageClient;
use GuzzleHttp\Promise;

class GoogleCloudStorage extends AbstractStorage implements StorageInterface
{
    private $client;

    public function __construct(StorageClient $client)
    {
        $this->client = $client;
    }

    public function getObjectAsync(array $keys): array
    {
        try {
            $bucket = $this->client->bucket($this->bucket);

            $promises = [];
            foreach ($keys as $key => $object) {
                $storageObject = $bucket->object($this->key($object));
                $promises[$key] = $storageObject->downloadAsStreamAsync()
                    ->then(function (\Psr\Http\Message\StreamInterface $data) {
                        return $data;
                    });
            }
            $objects = Promise\unwrap($promises);
            return array_map(function ($object) {
                return $object->getContents();
            }, $objects);
        } catch (\Throwable $throwable) {
            if ($throwable instanceof GoogleException) {
                throw new ArquiveiStorageException($throwable, $throwable->getCode(), $throwable->getMessage());
            }
            throw $throwable;
        }
    }

    public function getObjectsMetadataByPrefix(string $prefix): array
    {
        try {
            $bucket = $this->client->bucket($this->bucket);

            $storageObjects = $bucket->objects(['prefix' => $prefix]);

            $objectsMetadata = [];
            foreach ($storageObjects as $storageObject) {
                $objectsMetadata[] = $storageObject->info();
            }

            return $objectsMetadata;
        } catch (\Throwable $throwable) {
            if ($throwable instanceof GoogleException) {
                throw new ArquiveiStorageException($throwable, $throwable->getCode(), $throwable->getMessage());
            }
            throw $throwable;
        }
    }

    public function exists(string $key)
    {
        try {
            $bucket = $this->client->bucket($this->bucket);
            $storageObject = $bucket->object($this->key($key));
            return $storageObject->exists();

        } catch (\Throwable $throwable) {
            if ($throwable instanceof GoogleException) {
                throw new ArquiveiStorageException($throwable, $throwable->getCode(), $throwable->getMessage());
            }
            throw $throwable;
        }
    }

    public function putObject(string $data, string $key, ?string $acl = null): string
    {
        $options = [
            'name' => $key
        ];

        if (!is_null($acl)) {
            $options['predefinedAcl'] = $acl;
        }

        try {
            $bucket = $this->client->bucket($this->bucket);
            $storageObject = $bucket->upload(
                $data,
                $options
            );

            return $storageObject->info()['name'];

        } catch (\Throwable $throwable) {
            if ($throwable instanceof GoogleException) {
                throw new ArquiveiStorageException($throwable, $throwable->getCode(), $throwable->getMessage());
            }

            throw $throwable;
        }
    }

    public function putObjectWithSignedUrlReturn(
        string $data,
        string $key,
        \DateTime $expireDate,
        ?string $acl = null
    ): array {
        try {
            $options = [
                'name' => $key
            ];

            if (!is_null($acl)) {
                $options['predefinedAcl'] = $acl;
            }

            $bucket = $this->client->bucket($this->bucket);
            $storageObject = $bucket->upload(
                $data,
                $options
            );

            return [
                "object_info" => $storageObject->info()['name'],
                "signed_url" => $storageObject->signedUrl($expireDate)
            ];

        } catch (\Throwable $throwable) {
            if ($throwable instanceof GoogleException) {
                throw new ArquiveiStorageException($throwable, $throwable->getCode(), $throwable->getMessage());
            }

            throw $throwable;
        }
    }
}
