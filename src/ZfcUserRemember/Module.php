<?php

namespace ZfcUserRemember;

use Zend\EventManager\EventInterface;
use Zend\Http\Request as HttpRequest;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

class Module implements
    AutoloaderProviderInterface,
    BootstrapListenerInterface,
    ConfigProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function onBootstrap(EventInterface $e)
    {
        /** @var \Zend\Mvc\Application $app */
        $app = $e->getTarget();
        $sm  = $app->getServiceManager();

        $request = $sm->get('Request');
        if ($request instanceof HttpRequest) {
            $rememberService = $sm->get('ZfcUserRemember\Service\RememberService');
            $rememberService->login();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * {@inheritDoc}
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }
}