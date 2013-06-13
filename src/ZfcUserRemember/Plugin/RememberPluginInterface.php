<?php

namespace ZfcUserRemember\Plugin;

interface RememberPluginInterface
{
    const EVENT_LOGOUT            = 'logout';
    const EVENT_GET_COOKIE        = 'getCookie';
    const EVENT_GENERATE_COOKIE   = 'generateCookie';
    const EVENT_INVALIDATE_COOKIE = 'invalidateCookie';
}