<?php

namespace ZfcUserRemember\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
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