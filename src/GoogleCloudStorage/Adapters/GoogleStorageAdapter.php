<?php

namespace Arquivei\Flysystems\GoogleCloudStorage\Adapters;

use Arquivei\Flysystems\GoogleCloudStorage\Adapters\Bus\{
    ObjectTrait, DirectoryTrait, SteamTrait, VisibilityTrait
};
use Google\Cloud\Storage\StorageClient as GcsClient;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\Util;

class GoogleStorageAdapter extends AbstractAdapter
{

    use ObjectTrait, DirectoryTrait, SteamTrait, VisibilityTrait;

    const STORAGE_API_URI = 'https://storage.googleapis.com';

    /**
     * @var gcsClient
     */
    protected $gcsClient;
    /**
     * @var string
     */
    protected $bucket;
    /**
     * @var array
     */
    protected $options = [];

    /**
     * Constructor.
     *
     * @param gcsClient $client
     * @param string   $bucket
     * @param string   $prefix
     * @param array    $options
     */
    public function __construct(GcsClient $client, $bucket, $prefix = '', array $options = [])
    {
        $this->gcsClient = $client;
        $this->bucket = $bucket;
        $this->setPathPrefix($prefix);
        $this->options = $options;
    }

    /**
     * Get the gcsClient bucket.
     *
     * @return string
     */
    public function getBucket(): string
    {
        return $this->bucket;
    }

    /**
     * Set the gcsClient bucket.
     *
     * @param string $bucket
     *
     * @return GoogleStorageAdapter
     */
    public function setBucket(string $bucket): GoogleStorageAdapter
    {
        $this->bucket = $bucket;
        return $this;
    }

    /**
     * Get the gcsClient instance.
     *
     * @return GcsClient
     */
    public function getClient(): GcsClient
    {
        return $this->gcsClient;
    }

    /**
    * Write a new file.
    *
    * @param string $path
    * @param string $contents
    * @param Config $config Config object
    *
    * @return false|array false on failure file meta data on success
    */
    public function write($path, $contents, Config $config)
    {
        return $this->upload($path, $contents, $config);
    }

    /**
     * Update a file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return false|array false on failure file meta data on success
     */
    public function update($path, $contents, Config $config)
    {
        return $this->upload($path, $contents, $config);
    }

    /**
     * Rename a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function rename($path, $newpath)
    {
        if ( ! $this->copy($path, $newpath)) {
            return false;
        }
        return $this->delete($path);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        $this->readObject($path)->delete();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        return $this->readObject($path)->exists();
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        $object = $this->readObject($path);
        $contents = $object->downloadAsString();
        $data = $this->normalizeObject($object);
        $data['contents'] = $contents;
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newpath)
    {
        $newpath = $this->applyPathPrefix($newpath);
        // we want the new file to have the same visibility as the original file
        $visibility = $this->getRawVisibility($path);
        $options = [
            'name' => $newpath,
            'predefinedAcl' => $this->getPredefinedAclForVisibility($visibility),
        ];
        $this->readObject($path)->copy($this->bucket, $options);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        $object = $this->readObject($path);
        return $this->normalizeObject($object);
    }
    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }
    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        return $this->getMetadata($path);
    }
    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false)
    {
        $directory = $this->applyPathPrefix($directory);
        $objects = $this->bucket->objects(['prefix' => $directory]);
        $normalised = [];
        foreach ($objects as $object) {
            $normalised[] = $this->normalizeObject($object);
        }
        return Util::emulateDirectories($normalised);
    }

    /**
     * Returns an array of options from the config.
     *
     * @param Config $config
     *
     * @return array
     */
    protected function getOptionsFromConfig(Config $config)
    {
        $options = [];
        if ($visibility = $config->get('visibility')) {
            $options['predefinedAcl'] = $this->getPredefinedAclForVisibility($visibility);
        } else {
            // if a file is created without an acl, it isn't accessible via the console
            // we therefore default to private
            $options['predefinedAcl'] = $this->getPredefinedAclForVisibility(AdapterInterface::VISIBILITY_PRIVATE);
        }
        if ($metadata = $config->get('metadata')) {
            $options['metadata'] = $metadata;
        }
        return $options;
    }
}
