<?php

namespace ZfcUserRemember\Plugin;

use Zend\Authentication\Result;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use ZfcUser\Plugin\LoginPluginInterface;
use ZfcUserRemember\Service\RememberService;

class LoginPlugin extends AbstractListenerAggregate implements LoginPluginInterface
{
    /**
     * @var RememberService
     */
    protected $rememberService;

    /**
     * @var bool
     */
    protected $remember = false;

    /**
     * @param RememberService $rememberService
     */
    public function __construct(RememberService $rememberService)
    {
        $this->rememberService = $rememberService;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        // login service
        $this->listeners[] = $events->attach(
            LoginPluginInterface::EVENT_PRE_LOGIN,
            array($this, 'prepareCookie')
        );
        $this->listeners[] = $events->attach(
            LoginPluginInterface::EVENT_POST_LOGIN,
            array($this, 'setCookie')
        );
        $this->listeners[] = $events->attach(
            LoginPluginInterface::EVENT_PRE_LOGOUT,
            array($this, 'clearCookies')
        );
        $this->listeners[] = $events->attach(
            LoginPluginInterface::EVENT_GET_LOGIN_FORM,
            array($this, 'prepareLoginForm')
        );
    }

    /**
     * @param EventInterface $e
     */
    public function prepareCookie(EventInterface $e)
    {
        $this->remember = (bool) $e->getParam('remember');
    }

    /**
     * @param EventInterface $e
     * @return \ZfcUser\Form\LoginForm
     */
    public function prepareLoginForm(EventInterface $e)
    {
        /** @var \ZfcUser\Form\LoginForm $form */
        $form = $e->getTarget();
        $form->add(array(
            'name' => 'remember',
            'type' => 'checkbox',
            'options' => array(
                'label' => 'Remember me on this computer'
            )
        ));

        return $e->getTarget();
    }

    /**
     * @param EventInterface $e
     */
    public function setCookie(EventInterface $e)
    {
        if (!$this->remember) {
            return;
        }

        $result = $e->getParam('result');
        if (!$result instanceof Result) {
            return;
        }

        if ($result->isValid()) {
            $this->rememberService->generateCookie($result->getIdentity());
        }
    }

    /**
     * @param EventInterface $e
     */
    public function clearCookies(EventInterface $e)
    {
        $this->rememberService->logout();
    }
}