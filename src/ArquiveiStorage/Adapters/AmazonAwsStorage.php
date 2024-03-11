<?php

namespace Arquivei\Flysystems\ArquiveiStorage\Adapters;

use Arquivei\Flysystems\ArquiveiStorage\AbstractStorage;
use Arquivei\Flysystems\ArquiveiStorage\ArquiveiStorageException;
use Arquivei\Flysystems\ArquiveiStorage\StorageInterface;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use GuzzleHttp\Promise;

class AmazonAwsStorage extends AbstractStorage implements StorageInterface
{

    private $client;

    public function __construct(S3Client $client)
    {
        $this->client = $client;
    }

    public function getObjectAsync(array $keys): array
    {
        try {
            $promises = [];
            foreach ($keys as $key) {
                $promises[] = $this->client->getObjectAsync([
                    'Bucket' => $this->bucket,
                    'Key' => $this->key($key),
                ]);
            }
            $objects = Promise\Utils::unwrap($promises);
            return array_map(function ($object) {
                return $object['Body']->getContents();
            }, $objects);
        } catch (\Throwable $throwable) {
            if ($throwable instanceof S3Exception) {
                throw new ArquiveiStorageException($throwable, $throwable->getCode(), $throwable->getMessage());
            }
            throw $throwable;
        }
    }

    public function putObject(string $data, string $key, string $acl = "private"): string
    {
        try {
            return $this->client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $this->key($key),
                'Body' => $data,
                'ACL' => $acl
            ]);
        } catch (\Throwable $throwable) {
            if ($throwable instanceof S3Exception) {
                throw new ArquiveiStorageException($throwable, $throwable->getCode(), $throwable->getMessage());
            }
            throw $throwable;
        }
    }
}
