<?php


namespace Arquivei\Flysystems\GoogleCloudStorage;

use Arquivei\Flysystems\ArquiveiStorage\Adapters\LogMonologAdapter;
use Arquivei\Flysystems\GoogleCloudStorage\Adapters\GoogleStorageAdapter;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Filesystem;

class GoogleCloudStorageProvider extends ServiceProvider
{
    private $logger;
    /**
     * Create a Filesystem instance with the given adapter.
     *
     * @param  \League\Flysystem\AdapterInterface  $adapter
     * @param  array  $config
     * @return \League\Flysystem\FilesystemInterface
     */
    protected function createFilesystem(AdapterInterface $adapter, array $config)
    {
        $cache = Arr::pull($config, 'cache');

        $config = Arr::only($config, ['visibility', 'disable_asserts', 'url']);

        if ($cache) {
            $adapter = new CachedAdapter($adapter, $this->createCacheStore($cache));
        }

        return new Filesystem($adapter, count($config) > 0 ? $config : null);
    }

    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        $factory = $this->app->make('filesystem'); /* @var FilesystemManager $factory */
        $this->logger = new LogMonologAdapter();
        $factory->extend(
            'gcs',
            function ($app, $config) {
                $storageClient = new StorageClient(
                    [
                        'projectId' => $config['project_id'],
                        'keyFilePath' => $config['key_file'],
                        'restRetryFunction' => function ($exception) {
                            $this->logger->error(
                                '[Arquivei/flysystems-php::GoogleCloudStorage] ' .
                                'Error executing a function on GCS. The function will be retried',
                                [
                                    'message' => $exception->getMessage(),
                                    'exception' => get_class($exception),
                                ]
                            );
                            if ($exception->getCode() == 404) {
                                return false;
                            }
                            return true;
                        },
                    ]
                );
                $bucket = $storageClient->bucket($config['bucket']);
                $pathPrefix = $config['path_prefix'];

                $adapter = new GoogleStorageAdapter($storageClient, $bucket, $pathPrefix, $config);
                return $this->createFilesystem($adapter, $config);
            }
        );
    }

    /**
     * Register bindings in the container.
     */
    public function register()
    {
        //
    }
}