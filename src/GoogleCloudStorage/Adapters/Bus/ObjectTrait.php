<?php

namespace Arquivei\Flysystems\GoogleCloudStorage\Adapters\Bus;

use Google\Cloud\Storage\StorageObject;
use League\Flysystem\Util;

trait ObjectTrait
{
    /**
     * Returns a storage object for the given path.
     *
     * @param string $path
     *
     * @return \Google\Cloud\Storage\StorageObject
     */
    protected function readObject($path)
    {
        $path = $this->applyPathPrefix($path);
        return $this->bucket->object($path);
    }


    /**
     * Returns a dictionary of object metadata from an object.
     *
     * @param StorageObject $object
     *
     * @return array
     */
    protected function normalizeObject(StorageObject $object)
    {
        $name = $this->removePathPrefix($object->name());
        $info = $object->info();
        $isDir = substr($name, -1) === '/';

        if ($isDir) {
            $name = rtrim($name, '/');
        }

        return [
            'type' => $isDir ? 'dir' : 'file',
            'dirname' => Util::dirname($name),
            'path' => $name,
            'timestamp' => strtotime($info['updated']),
            'mimetype' => isset($info['contentType']) ? $info['contentType'] : '',
            'size' => $info['size'],
        ];
    }
}
