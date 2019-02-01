<?php

namespace Arquivei\Flysystems\ArquiveiStorage;

class AbstractStorage
{
    protected $basePath;
    protected $bucket;

    public function setBucket(String $bucket) : AbstractStorage
    {
        $this->bucket = $bucket;
        return $this;
    }

    public function setBasePath(String $basePath) : AbstractStorage
    {
        $this->basePath = $basePath;
        return $this;
    }

    protected function key(String $key) : String
    {
        if (!is_null($this->basePath) && !empty($this->basePath) && strlen($this->basePath) > 0) {
            return sprintf('%s/%s', $this->basePath, $key);
        }
        return $key;
    }
}