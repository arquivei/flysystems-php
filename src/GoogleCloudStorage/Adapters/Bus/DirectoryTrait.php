<?php

namespace Arquivei\Flysystems\GoogleCloudStorage\Adapters\Bus;

use League\Flysystem\Config;

trait DirectoryTrait
{
    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
        return $this->delete($this->normalizeDirName($dirname));
    }
    /**
     * {@inheritdoc}
     */
    public function createDir($dirname, Config $config)
    {
        return $this->upload($this->normalizeDirName($dirname), '', $config);
    }

    /**
     * Returns a normalised directory name from the given path.
     *
     * @param string $dirname
     *
     * @return string
     */
    protected function normalizeDirName($dirname)
    {
        return rtrim($dirname, '/') . '/';
    }
}
