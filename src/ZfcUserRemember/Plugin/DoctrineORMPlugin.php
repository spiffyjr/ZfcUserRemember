<?php

namespace ZfcUserRemember\Plugin;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use ZfcUser\Entity\UserInterface;
use ZfcUserDoctrineORM\Plugin\AbstractPlugin;
use ZfcUserRemember\Entity\UserCookieInterface;
use ZfcUserRemember\Plugin\RememberPluginInterface;

class DoctrineORMPlugin extends AbstractPlugin implements RememberPluginInterface
{
    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(
            RememberPluginInterface::EVENT_LOGOUT,
            array($this, RememberPluginInterface::EVENT_LOGOUT)
        );

        $this->listeners[] = $events->attach(
            RememberPluginInterface::EVENT_GET_COOKIE,
            array($this, RememberPluginInterface::EVENT_GET_COOKIE)

        );
        $this->listeners[] = $events->attach(
            RememberPluginInterface::EVENT_GENERATE_COOKIE,
            array($this, RememberPluginInterface::EVENT_GENERATE_COOKIE)
        );

        $this->listeners[] = $events->attach(
            RememberPluginInterface::EVENT_INVALIDATE_COOKIE,
            array($this, RememberPluginInterface::EVENT_INVALIDATE_COOKIE)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function generateCookie(EventInterface $event)
    {
        $cookie = $event->getTarget();
        if (!$cookie instanceof UserCookieInterface) {
            return;
        }
        $this->objectManager->persist($event->getTarget());
        $this->objectManager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function invalidateCookie(EventInterface $event)
    {
        $cookie = $event->getTarget();
        if (!$cookie instanceof UserCookieInterface) {
            // todo: throw exception
            echo 'unexpected value 2';
            exit;
        }
        $this->objectManager->remove($event->getTarget());
        $this->objectManager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getCookie(EventInterface $event)
    {
        $token = $event->getTarget();
        if (!$token) {
            return null;
        }

        return $this->getObjectRepository()->findOneBy(array('token' => $token));
    }

    /**
     * {@inheritDoc}
     */
    public function logout(EventInterface $event)
    {
        $user = $event->getTarget();
        if (!$user instanceof UserInterface) {
            return;
        }

        $cookies = $this->getObjectRepository()->findAll();
        foreach ($cookies as $cookie) {
            $this->objectManager->remove($cookie);
        }
        $this->objectManager->flush();
    }
}