<?php

namespace ZfcUserRemember\Entity;

use ZfcUser\Entity\UserInterface;
use ZfcUserRemember\Entity\UserCookieInterface;

class UserCookie implements UserCookieInterface
{
    /**
     * @var UserInterface
     **/
    protected $user;

    /**
     * @var string
     */
    protected $token;

    /**
     * @param string $token
     * @return UserCookie
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param UserInterface $user
     * @return UserCookie
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}
