<?php

return array(
    'factories' => array(
        'ZfcUserRemember\Options\ModuleOptions'   => 'ZfcUserRemember\Options\ModuleOptionsFactory',
        'ZfcUserRemember\Plugin\LoginPlugin'      => 'ZfcUserRemember\Plugin\LoginPluginFactory',
        'ZfcUserRemember\Service\RememberService' => 'ZfcUserRemember\Service\RememberServiceFactory'
    )
);