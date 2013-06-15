<?php

return array(
    'service_manager' => include 'service.config.php',

    'zfc_user' => array(
        'login_plugins' => array(
            'ZfcUserRemember\Plugin\LoginPlugin'
        )
    )
);