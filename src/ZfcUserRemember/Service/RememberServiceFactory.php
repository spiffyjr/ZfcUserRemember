<?php

namespace ZfcUserRemember\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use ZfcUser\Service\AbstractServiceFactory;

class RememberServiceFactory extends AbstractServiceFactory
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return RememberService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var \ZfcUserRemember\Options\ModuleOptions $options */
        $options = $serviceLocator->get('ZfcUserRemember\Options\ModuleOptions');

        /** @var \ZfcUser\Options\ModuleOptions $userOptions */
        $userOptions = $serviceLocator->get('ZfcUser\Options\ModuleOptions');

        $service = new RememberService();
        $service->setOptions($options);
        $service->setRequest($serviceLocator->get('Request'));
        $service->setResponse($serviceLocator->get('Response'));
        $service->setAuthenticationService($serviceLocator->get('Zend\Authentication\AuthenticationService'));

        foreach ($options->getPlugins() as $plugin) {
            $service->registerPlugin($this->get($serviceLocator, $plugin));
        }

        $service->setAuthenticationService($this->get($serviceLocator, $userOptions->getAuthenticationService()));

        return $service;
    }
}