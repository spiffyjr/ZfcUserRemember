<?php

namespace ZfcUserRemember;

use Zend\ServiceManager\ServiceLocatorInterface;
use ZfcUserDoctrineORM\Service\AbstractServiceFactory;

class DoctrineExtensionFactory extends AbstractServiceFactory
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Extension
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new DoctrineExtension($this->getObjectManager($serviceLocator));
    }
}