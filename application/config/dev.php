<?php
return [
    'settings' => [

        'http_host' => 'ec2-13-125-247-52.ap-northeast-2.compute.amazonaws.com',

        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../views/',
            'cache' => false
        ],

        // Monolog settings
        'logger' => [
            'name' => 'synctree_studio_v2',
            'path' => __DIR__ . '/../../logs/'.date('Ymd').'.log',
            'level' => \Monolog\Logger::DEBUG
        ],

        'home_path' => BASE_DIR.'/',

        // Secure Config settings
        'secure_config' => [
            'path' => '/home/ubuntu/apps/secure/'
        ],

        'api' => [
            'api_url'      => 'http://ec2-13-125-247-52.ap-northeast-2.compute.amazonaws.com',
            'secure_url'   => 'http://ec2-13-125-247-52.ap-northeast-2.compute.amazonaws.com',
        ],

        'amazon' => [
            's3' => [
                'region' => 'ap-northeast-2',
                'version' => 'latest',
                'key' => 'AKIAII2JQVHE5ZE3PUQA',
                'secret' => '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',
                'bucket_name' => 'synctreem-public'
            ],
            'dynamodb' => [
                'region' => 'ap-northeast-1',
                'version' => '2012-08-10',
                'key' => 'AKIAJRPI6CNQTAFOBIIA',
                'secret' => '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'
            ],
        ],

        // Language Pack
        'language' => [
            'availableLang' => ['KO', 'EN'],
            'defaultLang' => 'EN',
        ],

        'redis' => [
            'host' => [
                '127.0.0.1',
                '127.0.0.1',
            ],
            'port' => [
                '6379',
                '6379',
            ],
            'auth' => [
                '5856e3e7e0d46081eaa9e60b9730427cfe600c31dd9034e44259969721dd460b',
                '5856e3e7e0d46081eaa9e60b9730427cfe600c31dd9034e44259969721dd460b'
            ],
            'connection_timeout' => 5,
            'crypt'              => false
        ],

        // RDB settings
        'rdb' => [
            'driver' => 'mysql',
            'host' => '13.114.212.113',
            'port' => 3306,
            'dbname' => 'synctree_m',
            'charset' => 'utf8mb4',
            'username' => 'leopard',
            'password' => '30DE42F6BA1E11E797110A19FC990CB6',
        ],
    ]
];
