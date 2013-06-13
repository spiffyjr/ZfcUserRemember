<?php

namespace ZfcUserRemember\Entity;

use ZfcUser\Entity\UserInterface;

interface UserCookieInterface
{
    /**
     * @param int $id
     * @return UserCookie
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $token
     * @return UserCookie
     */
    public function setToken($token);

    /**
     * @return int
     */
    public function getToken();

    /**
     * @param UserInterface $user
     * @return UserCookie
     */
    public function setUser(UserInterface $user);

    /**
     * @return UserInterface
     */
    public function getUser();
}