<?php

namespace ZfcUserRemember\Service;

use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Result;
use Zend\Http;
use ZfcUser\Entity\UserInterface;
use ZfcUser\Service\AbstractPluginService;
use ZfcUserRemember\Authentication\RememberAdapter;
use ZfcUserRemember\Entity\UserCookie;
use ZfcUserRemember\Options\ModuleOptions;
use ZfcUserRemember\Plugin\RememberPluginInterface;

class RememberService extends AbstractPluginService
{
    const COOKIE_NAME = 'remember';

    /**
     * @var RememberAdapter
     */
    protected $adapter;

    /**
     * @var AuthenticationService
     */
    protected $authService;

    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var Http\Request
     */
    protected $request;

    /**
     * @var Http\Response
     */
    protected $response;

    /**
     * @var array
     */
    protected $allowedPluginInterfaces = array(
        'ZfcUserRemember\Plugin\RememberPluginInterface'
    );

    /**
     * Authenticates a user via cookie if enabled and the cookie exists.
     *
     * @return null|Result
     */
    public function login()
    {
        $adapter     = $this->getAdapter();
        $authService = $this->getAuthenticationService();

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
     * Performs logout (clearing cookies) for the user.
     */
    public function logout()
    {
        if (!$this->getAuthenticationService()->hasIdentity()) {
            return;
        }

        $this->invalidateCookie();
        $this->getEventManager()->trigger(
            RememberPluginInterface::EVENT_LOGOUT,
            $this->getAuthenticationService()->getIdentity()
        );
    }

    /**
     * @return null|\ZfcUserRemember\Entity\UserCookieInterface
     */
    public function getCookie()
    {
        $request = $this->request;
        $cookie  = $request->getCookie();

        if (!isset($cookie->{RememberService::COOKIE_NAME})) {
            return null;
        }

        list($email, $token) = explode(':', $cookie->{RememberService::COOKIE_NAME});

        $results    = $this->getEventManager()->trigger(RememberPluginInterface::EVENT_GET_COOKIE, $token);
        $userCookie = $results->last();

        if (!$userCookie || !$userCookie->getUser()->getEmail() === $email) {
            return null;
        }

        return $userCookie;
    }

    /**
     * Generates a new cookie for the user.
     *
     * @param UserInterface $user
     */
    public function generateCookie(UserInterface $user)
    {
        $token     = $this->generateToken($user);
        $expires   = time() + $this->options->getDuration();
        $setCookie = new Http\Header\SetCookie(static::COOKIE_NAME, $user->getEmail() . ':' . $token, $expires, '/');

        $this->getResponse()->getHeaders()->addHeader($setCookie);

        $cookie = new UserCookie();
        $cookie->setUser($user)
               ->setToken($token);

        $this->getEventManager()->trigger(RememberPluginInterface::EVENT_GENERATE_COOKIE, $cookie);
    }

    /**
     * @param \Zend\Http\Request $request
     * @return RememberService
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return \Zend\Http\PhpEnvironment\Request|\Zend\Http\Request
     */
    public function getRequest()
    {
        if (!$this->request) {
            $this->request = new Http\PhpEnvironment\Request();
        }
        return $this->request;
    }

    /**
     * @param \Zend\Http\Response $response
     * @return RememberService
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return \Zend\Http\Response
     */
    public function getResponse()
    {
        if (!$this->response) {
            $this->response = new Http\PhpEnvironment\Response();
        }
        return $this->response;
    }

    /**
     * @param ModuleOptions $options
     * @return RememberService
     */
    public function setOptions(ModuleOptions $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return ModuleOptions
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->options = new ModuleOptions();
        }
        return $this->options;
    }

    /**
     * @param RememberAdapter $adapter
     * @return RememberService
     */
    public function setAdapter(RememberAdapter $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * @return RememberAdapter
     */
    public function getAdapter()
    {
        if (!$this->adapter) {
            $this->adapter = new RememberAdapter($this);
        }
        return $this->adapter;
    }

    /**
     * @param AuthenticationService $authenticationService
     * @return RememberService
     */
    public function setAuthenticationService(AuthenticationService $authenticationService)
    {
        $this->authService = $authenticationService;
        return $this;
    }

    /**
     * @return AuthenticationService
     */
    public function getAuthenticationService()
    {
        if (!$this->authService) {
            $this->authService = new AuthenticationService();
        }
        return $this->authService;
    }

    /**
     * Invalidate any cookie the user may have.
     */
    protected function invalidateCookie()
    {
        $cookie = $this->getRequest()->getCookie();
        if (!isset($cookie->{static::COOKIE_NAME})) {
            return;
        }

        $setCookie = new Http\Header\SetCookie(static::COOKIE_NAME, null, -1, '/');
        $this->getResponse()->getHeaders()->addHeader($setCookie);

        $userCookie = $this->getCookie();
        if ($userCookie) {
            $this->getEventManager()->trigger(RememberPluginInterface::EVENT_INVALIDATE_COOKIE, $userCookie);
        }
    }

    /**
     * @param UserInterface $user
     * @return string
     */
    protected function generateToken(UserInterface $user)
    {
        return md5($user->getEmail() . microtime(true) . $this->options->getSalt());
    }
}