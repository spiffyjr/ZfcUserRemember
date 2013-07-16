<?php

namespace ZfcUserRemember;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use ZfcUser\Entity\UserInterface;
use ZfcUser\Extension\AbstractExtension;
use ZfcUser\Extension\Authentication;
use ZfcUserRemember\Entity\UserCookieInterface;

class DoctrineExtension extends AbstractExtension
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'remember_doctrine';
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        // disable extension if doctrine extension is not present
        if (!$this->getManager()->has('doctrine')) {
            return;
        }

        $this->listeners[] = $events->attach(Authentication::EVENT_LOGOUT_POST, array($this, 'onLogout'));
        $this->listeners[] = $events->attach(Extension::EVENT_GET_COOKIE, array($this, 'onGetCookie'));
        $this->listeners[] = $events->attach(Extension::EVENT_GENERATE_COOKIE, array($this, 'onGenerateCookie'));
        $this->listeners[] = $events->attach(Extension::EVENT_INVALIDATE_COOKIE, array($this, 'onInvalidateCookie'));
    }

    /**
     * @param EventInterface $event
     */
    public function onGenerateCookie(EventInterface $event)
    {
        $cookie = $event->getTarget();
        if (!$cookie instanceof UserCookieInterface) {
            return;
        }
        $om = $this->getObjectManager();
        $om->persist($event->getTarget());
        $om->flush();
    }

    /**
     * @param EventInterface $event
     */
    public function onInvalidateCookie(EventInterface $event)
    {
        $cookie = $event->getTarget();
        if (!$cookie instanceof UserCookieInterface) {
            // todo: throw exception
            echo 'unexpected value 2';
            exit;
        }

        $om = $this->getObjectManager();
        $om->remove($event->getTarget());
        $om->flush();
    }

    /**
     * @param EventInterface $event
     * @return null
     */
    public function onGetCookie(EventInterface $event)
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
    public function onLogout(EventInterface $event)
    {
        $user = $event->getTarget();
        if (!$user instanceof UserInterface) {
            return;
        }

        $om      = $this->getObjectManager();
        $cookies = $this->getObjectRepository()->findAll();
        foreach ($cookies as $cookie) {
            $om->remove($cookie);
        }
        $om->flush();
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getObjectRepository()
    {
        $remember = $this->getManager()->get('remember');
        return $this->getObjectManager()->getRepository($remember->getOption('entity_class'));
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getObjectManager()
    {
        /** @var \ZfcUserDoctrine\Extension $doctrine */
        $doctrine = $this->getManager()->get('doctrine');
        return $doctrine->getObjectManager();
    }
}