<?php

return array(
    'doctrine' => array(
        'driver' => array(
            'zfc_user_remember' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\XmlDriver',
                'paths' => array(__DIR__ . '/orm')
            ),

            'orm_default' => array(
                'drivers' => array(
                    'ZfcUserRemember\Entity' => 'zfc_user_remember',
                )
            )
        )
    ),

    'service_manager' => include 'service.config.php',

    'zfc_user' => array(
        'extensions' => array(
            'remember'          => array(
                'type' => 'ZfcUserRemember\Extension',
                'options' => array(
                    'duration'     => 1209600,
                    'entity_class' => 'ZfcUserRemember\Entity\UserCookie',
                    'salt'         => 'change_the_default_salt!',
                )
            ),
            'remember_doctrine' => 'ZfcUserRemember\DoctrineExtension',
        )
    ),

    'zfc_user_remember' => array(
        'cookie_class' => 'ZfcUserRemember\Entity\UserCookie',
        'plugins' => array(
            'ZfcUserRemember\Plugin\DoctrineORMPlugin',
        )
    )
);