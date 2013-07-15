<?php

namespace ZfcUserRemember\Authentication;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\Http\Request;
use ZfcUserRemember\Extension as RememberExtension;

class RememberAdapter implements AdapterInterface
{
    const FAILURE_DISABLED = -5;
    const FAILURE_INVALID  = -6;

    /**
     * @var RememberExtension
     */
    protected $extension;

    /**
     * @param RememberExtension $extension
     */
    public function __construct(RememberExtension $extension)
    {
        $this->extension = $extension;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate()
    {
        $userCookie = $this->extension->getCookie();
        if (!$userCookie) {
            return new Result(self::FAILURE_INVALID, null, array('No cookie present'));
        }

        return new Result(Result::SUCCESS, $userCookie->getUser());
    }
}
