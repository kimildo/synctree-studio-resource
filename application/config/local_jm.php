<?php
return [
    'settings' => [

        'http_host' => 'local.testntservice.com:8020',

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
            'path' => '/home/ubuntu/apps/studio_secure/'
        ],

        'api' => [
            'api_url'      => 'http://local.testntservice.com:8020',
            'secure_url'   => 'http://local.studio-testntservice.com:8020',
        ],
		
		'deploy' => [
            'origin_file_path'       => '/home/ubuntu/apps/studio_nfs/',
            'source_group_file_path' => '/home/ubuntu/apps/studio_deploy_tmp/',
            'source_file_path'       => '/home/ubuntu/apps/studio_deploy_tmp/source/',
            'achive_target_path'     => '/home/ubuntu/apps/studio_deploy/',
        ],

        'amazon' => [
            'codedeploy' => [
                'region'       => 'ap-northeast-2',
                'version'      => 'latest',
                'key'          => 'AKIAITXTE2YUV7LBCZWQ',
                'secret'       => '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',
                'app_name'     => 'studio-deploy',
                'group_name'   => 'studio-deploy-group',
                'ec2_tag_name' => 'synctree-studio-v2.1-deploy-target',
                'bucket_name'  => 'studio-codedeploy'
            ],
            's3deploy' => [
                'region'      => 'ap-northeast-2',
                'version'     => 'latest',
                'key'         => 'AKIAJ4ZQXS5YLQOQ3MRA',
                'secret'      => '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',
                'bucket_name' => 'studio-codedeploy'
            ],
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
                '52.78.186.39',
                '52.78.186.39',
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
            'host' => '13.209.208.5',
            'port' => 3306,
            'dbname' => 'bison',
            'charset' => 'utf8mb4',
            'username' => 'app_server',
            'password' => 'lxBr8MmswCP3kQpy',
        ],
    ]
];
