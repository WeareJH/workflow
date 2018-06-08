<?php

return [
    'backend' => [
        'frontName' => 'admin',
    ],
    'db' => [
        'connection' => [
            'indexer' => [
                'host' => 'db',
                'dbname' => 'docker',
                'username' => 'docker',
                'password' => 'docker',
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'active' => '1',
                'persistent' => NULL,
            ],
            'default' => [
                'host' => 'db',
                'dbname' => 'docker',
                'username' => 'docker',
                'password' => 'docker',
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'active' => '1',
            ],
        ],
        'table_prefix' => '',
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default',
        ],
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'production',
    'session' => [
        'save' => 'redis',
        'redis' => [
            'host' => 'redis',
            'port' => '6379',
            'password' => '',
            'timeout' => '2.5',
            'persistent_identifier' => '',
            'database' => '0',
            'compression_threshold' => '2048',
            'compression_library' => 'gzip',
            'log_level' => '1',
            'max_concurrency' => '6',
            'break_after_frontend' => '5',
            'break_after_adminhtml' => '30',
            'first_lifetime' => '600',
            'bot_first_lifetime' => '60',
            'bot_lifetime' => '7200',
            'disable_locking' => '0',
            'min_lifetime' => '60',
            'max_lifetime' => '2592000',
        ],
    ],
    'cache_types' => [
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'target_rule' => 1,
        'full_page' => 1,
        'amasty_shopby' => 1,
        'translate' => 1,
        'config_webservice' => 1,
    ],
    'install' => [
        'date' => 'Mon, 05 Mar 2018 11:35:35 +0000',
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'backend' => 'Cm_Cache_Backend_Redis',
                'backend_options' => [
                    'server' => 'redis',
                    'port' => '6379',
                    'database' => '1',
                ],
            ],
        ],
    ],
#    'queue' => [
#        'amqp' => [
#            'host'        => 'rabbitmq',
#            'port'        => '5672',
#            'user'        => getenv('RABBITMQ_DEFAULT_USER'),
#            'password'    => getenv('RABBITMQ_DEFAULT_PASS'),
#            'virtualhost' => '/',
#            'ssl'         => '',
#        ],
#    ],
];
