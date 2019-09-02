<?php
return [
    'settings' => [

        'http_host' => 'local.studio.synctreengine.com',

        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../views/',
            'cache' => false
        ],

        // Monolog settings
        'logger' => [
            'name' => 'synctree_studio_v2.1',
            'path' => __DIR__ . '/../../logs/'.date('Ymd').'.log',
            'level' => \Monolog\Logger::DEBUG
        ],

        'home_path' => '/home/ubuntu/apps/studio/',

        // Secure Config settings
        'secure_config' => [
            'path' => '/home/ubuntu/apps/publish/studio/secure/'
        ],

        'deploy' => [
            'origin_file_path'       => '/home/ubuntu/apps/studio_usr/',
            'source_group_file_path' => '/home/ubuntu/apps/studio_deploy_tmp/',
            'source_file_path'       => '/home/ubuntu/apps/studio_deploy_tmp/source/',
            'achive_target_path'     => '/home/ubuntu/apps/studio_deploy/',
        ],

        // Language Pack
        'language' => [
            'availableLang' => ['KO', 'EN'],
            'defaultLang' => 'KO',
        ],

        'redis' => [
            'host' => [
                '52.78.186.39'
            ],
            'port' => [
                '6379'
            ],
            'auth' => [
                '5856e3e7e0d46081eaa9e60b9730427cfe600c31dd9034e44259969721dd460b'
            ],
            'connection_timeout' => 5,
            'crypt'              => false
        ],

        // RDB settings
        'rdb' => [
            'driver' => 'mysql',
            'host' => '13.209.208.5',
            'port' => 3306,
            'dbname' => 'bison',
            'charset' => 'utf8mb4',
            'username' => 'app_server',
            'password' => 'lxBr8MmswCP3kQpy',
        ],


    ]
];
