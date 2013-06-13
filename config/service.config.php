<?php

return array(
    'factories' => array(
        'ZfcUserRemember\Options\ModuleOptions'   => 'ZfcUserRemember\Options\ModuleOptionsFactory',
        'ZfcUserRemember\Plugin\RememberPlugin'   => 'ZfcUserRemember\Plugin\RememberPluginFactory',
        'ZfcUserRemember\Service\RememberService' => 'ZfcUserRemember\Service\RememberServiceFactory'
    )
);