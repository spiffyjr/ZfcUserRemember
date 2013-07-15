<?php

namespace ZfcUserRemember;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    /**
     * @var string
     */
    protected $cookieClass;

    /**
     * @var array
     */
    protected $plugins = array();

    /**
     * The time, in seconds, to set the remember me cookie for.
     *
     * @var int
     */
    protected $duration = 1209600;

    /**
     * A salt to be used with the token generation.
     *
     * @var string
     */
    protected $salt = 'default_salt_is_bad';

    /**
     * @param string $cookieClass
     * @return ModuleOptions
     */
    public function setCookieClass($cookieClass)
    {
        $this->cookieClass = $cookieClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getCookieClass()
    {
        return $this->cookieClass;
    }

    /**
     * @param int $duration
     * @return ModuleOptions
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param string $salt
     * @return ModuleOptions
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
        return $this;
    }

    /**
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @param array $plugins
     * @return ModuleOptions
     */
    public function setPlugins($plugins)
    {
        $this->plugins = $plugins;
        return $this;
    }

    /**
     * @return array
     */
    public function getPlugins()
    {
        return $this->plugins;
    }
}