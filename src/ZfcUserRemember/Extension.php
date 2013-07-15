<?php

namespace ZfcUserRemember;

use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Result;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Header\SetCookie;
use Zend\Http\Request as HttpRequest;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;
use ZfcUser\Entity\UserInterface;
use ZfcUser\Extension\AbstractExtension;
use ZfcUser\Extension\Authentication;
use ZfcUserRemember\Authentication\RememberAdapter;

class Extension extends AbstractExtension
{
    const COOKIE_NAME             = 'remember';
    const EVENT_GENERATE_COOKIE   = 'remember.generateCookie';
    const EVENT_GET_COOKIE        = 'remember.getCookie';
    const EVENT_INVALIDATE_COOKIE = 'remember.invalidateCookie';

    /**
     * @var AuthenticationService
     */
    protected $authenticationService;

    /**
     * @var \ZfcUserRemember\Entity\UserCookieInterface;
     */
    protected $cookiePrototype;

    /**
     * @var array
     */
    protected $options = array(
        'duration'     => 1209600,
        'entity_class' => 'Application\Entity\User',
        'salt'         => 'change_the_default_salt!',
    );

    /**
     * Set on pre.login to tell post.login to store the cookie or not.
     *
     * @var bool
     */
    protected $rememberEnabled = false;

    /**
     * @param AuthenticationService $authenticationService
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(
        AuthenticationService $authenticationService,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $this->authenticationService = $authenticationService;
        $this->request               = $request;
        $this->response              = $response;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'remember';
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(Authentication::EVENT_LOGIN_PRE, array($this, 'onLoginPre'));
        $this->listeners[] = $events->attach(Authentication::EVENT_LOGIN_POST, array($this, 'onLoginPost'));
        $this->listeners[] = $events->attach(
            Authentication::EVENT_LOGIN_PREPARE_FORM,
            array($this, 'onPrepareLoginForm')
        );
        $this->listeners[] = $events->attach(Authentication::EVENT_LOGOUT_POST, array($this, 'onLogoutPost'));
    }

    /**
     * @return null|\ZfcUserRemember\Entity\UserCookieInterface
     */
    public function getCookie()
    {
        if (!$this->request instanceof HttpRequest) {
            return null;
        }
        $cookie = $this->request->getCookie();

        if (!isset($cookie->{static::COOKIE_NAME})) {
            return null;
        }

        list($identity, $token) = explode(':', $cookie->{static::COOKIE_NAME});

        $manager    = $this->getManager();
        $results    = $manager->getEventManager()->trigger(static::EVENT_GET_COOKIE, $token);
        $userCookie = $results->last();

        if (!$userCookie || !$userCookie->getUser()->getEmail() === $identity) {
            return null;
        }

        return $userCookie;
    }

    /**
     * Authenticates a user via cookie if enabled and the cookie exists.
     *
     * @return null|Result
     */
    public function login()
    {
        $adapter     = new RememberAdapter($this);
        $authService = $this->authenticationService;

        if ($authService->hasIdentity()) {
            return null;
        }

        $result = $authService->authenticate($adapter);

        $this->invalidateCookie();

        if ($result->isValid()) {
            $this->generateCookie($result->getIdentity());
        }

        return $result;
    }

    /**
     * @param EventInterface $e
     */
    public function onPrepareLoginForm(EventInterface $e)
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
    }

    /**
     * @param EventInterface $e
     */
    public function onLoginPre(EventInterface $e)
    {
        $this->rememberEnabled = (bool) $e->getParam('remember');
    }

    /**
     * @param EventInterface $e
     */
    public function onLoginPost(EventInterface $e)
    {
        if (!$this->rememberEnabled) {
            return;
        }

        $result = $e->getParam('result');
        if (!$result instanceof Result) {
            return;
        }

        if ($result->isValid()) {
            $this->generateCookie($result->getIdentity());
        }
    }

    /**
     * @param EventInterface $e
     */
    public function onLogoutPost(EventInterface $e)
    {
        $this->invalidateCookie();
    }

    /**
     * @return \ZfcUserRemember\Entity\UserCookieInterface
     */
    public function getCookiePrototype()
    {
        if (!$this->cookiePrototype) {
            $userClass = $this->options['entity_class'];
            if (!class_exists($userClass)) {
                // todo: throw exception
                echo 'userclass ' . $userClass . ' could not be found';
                exit;
            }
            $this->cookiePrototype = new $userClass();
        }
        return $this->cookiePrototype;
    }

    /**
     * Generates a new cookie for the user.
     *
     * @param UserInterface $user
     */
    protected function generateCookie(UserInterface $user)
    {
        $token     = $this->generateToken($user);
        $expires   = time() + $this->options['duration'];
        $setCookie = new SetCookie(static::COOKIE_NAME, $user->getEmail() . ':' . $token, $expires, '/');
        $manager   = $this->getManager();

        $this->response->getHeaders()->addHeader($setCookie);

        $cookie = $this->getCookiePrototype();
        $cookie->setUser($user)
               ->setToken($token);

        $manager->getEventManager()->trigger(static::EVENT_GENERATE_COOKIE, $cookie);
    }

    /**
     * @param UserInterface $user
     * @return string
     */
    protected function generateToken(UserInterface $user)
    {
        return md5($user->getEmail() . microtime(true) . $this->options['salt']);
    }

    /**
     * Invalidate any cookie the user may have.
     */
    protected function invalidateCookie()
    {
        $cookie = $this->request->getCookie();
        if (!isset($cookie->{static::COOKIE_NAME})) {
            return;
        }

        $setCookie = new SetCookie(static::COOKIE_NAME, null, -1, '/');
        $this->response->getHeaders()->addHeader($setCookie);

        $userCookie = $this->getCookie();
        if ($userCookie) {
            $this->getManager()->getEventManager()->trigger(static::EVENT_INVALIDATE_COOKIE, $userCookie);
        }
    }
}