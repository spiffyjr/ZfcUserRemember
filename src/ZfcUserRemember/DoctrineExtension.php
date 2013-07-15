<?php

namespace ZfcUserRemember;

use Doctrine\Common\Persistence\ObjectManager;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use ZfcUser\Entity\UserInterface;
use ZfcUser\Extension\AbstractExtension;
use ZfcUser\Extension\Authentication;
use ZfcUserRemember\Entity\UserCookieInterface;

class DoctrineExtension extends AbstractExtension
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

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
        $this->objectManager->persist($event->getTarget());
        $this->objectManager->flush();
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
        $this->objectManager->remove($event->getTarget());
        $this->objectManager->flush();
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

        $cookies = $this->getObjectRepository()->findAll();
        foreach ($cookies as $cookie) {
            $this->objectManager->remove($cookie);
        }
        $this->objectManager->flush();
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getObjectRepository()
    {
        $remember = $this->getManager()->get('remember');
        return $this->objectManager->getRepository($remember->getOption('entity_class'));
    }
}