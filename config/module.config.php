<?php

return array(
    'doctrine' => array(
        'orm_default' => array(
            'drivers' => array(
                'ZfcUserRemember\Entity' => 'zfc_user_doctrine_orm',
            )
        )
    ),

    'zfc_user' => array(
        'login_plugins' => array(
            'ZfcUserRemember\Plugin\RememberPlugin'
        )
    )
);