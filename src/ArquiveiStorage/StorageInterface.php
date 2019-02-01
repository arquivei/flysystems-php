<?php

namespace Arquivei\Flysystems\ArquiveiStorage;

interface StorageInterface
{
    public function getObjectAsync(array $keys): array;
    public function putObject(string $data, string $key, string $acl = "private"): string;
}
