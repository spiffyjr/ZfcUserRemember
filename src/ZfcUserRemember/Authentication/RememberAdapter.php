<?php

namespace ZfcUserRemember\Authentication;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\Http\Request;
use ZfcUserRemember\Service\RememberService;

class RememberAdapter implements AdapterInterface
{
    const FAILURE_DISABLED = -5;
    const FAILURE_INVALID  = -6;

    /**
     * @var RememberService
     */
    protected $service;

    /**
     * @param RememberService $service
     */
    public function __construct(RememberService $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate()
    {
        $userCookie = $this->service->getCookie();
        if (!$userCookie) {
            return new Result(self::FAILURE_INVALID, null, array('No cookie present'));
        }

        return new Result(Result::SUCCESS, $userCookie->getUser());
    }
}
