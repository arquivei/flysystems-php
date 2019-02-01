<?php

namespace Arquivei\Flysystems\GoogleCloudStorage\Adapters\Bus;

use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\Storage\Acl;
use League\Flysystem\AdapterInterface;

trait VisibilityTrait
{
    /**
     * @param string $path
     *
     * @return string
     */
    protected function getRawVisibility($path)
    {
        try {
            $acl = $this->readObject($path)->acl()->get(['entity' => 'allUsers']);
            return $acl['role'] === Acl::ROLE_READER ?
                AdapterInterface::VISIBILITY_PUBLIC :
                AdapterInterface::VISIBILITY_PRIVATE;
        } catch (NotFoundException $e) {
            // object may not have an acl entry, so handle that gracefully
            return AdapterInterface::VISIBILITY_PRIVATE;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility($path)
    {
        return [
            'visibility' => $this->getRawVisibility($path),
        ];

    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility($path, $visibility)
    {
        $object = $this->readObject($path);

        if ($visibility === AdapterInterface::VISIBILITY_PRIVATE) {
            $object->acl()->delete('allUsers');
        } elseif ($visibility === AdapterInterface::VISIBILITY_PUBLIC) {
            $object->acl()->add('allUsers', Acl::ROLE_READER);
        }

        $normalized = $this->normalizeObject($object);
        $normalized['visibility'] = $visibility;
        return $normalized;

    }

    /**
     * @param string $visibility
     *
     * @return string
     */
    protected function getPredefinedAclForVisibility($visibility)
    {
        return $visibility === AdapterInterface::VISIBILITY_PUBLIC ? 'publicRead' : 'projectPrivate';
    }
}
