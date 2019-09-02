<?php
    return [
        'settings' => [

            'http_host' => 'tidesquare.studio.synctreengine.com',

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

            'home_path' => BASE_DIR . '/',

            // Secure Config settings
            'secure_config' => [
                'path' => '/home/ubuntu/apps/publish/secure/'
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
                    'key'          => 'AKIA56G4MQZHZVUBQ2XX',
                    'secret'       => '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',
                    'app_name'     => 'studio-deploy',
                    'group_name'   => 'studio-deploy-group',
                    'ec2_tag_name' => 'synctree-studio-tidesquare-deploy-target',
                    'bucket_name'  => 'codedeploy-studio'
                ],
                's3deploy' => [
                    'region'      => 'ap-northeast-2',
                    'version'     => 'latest',
                    'key'         => 'AKIA56G4MQZH75EZFN4W',
                    'secret'      => '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',
                    'bucket_name' => 'codedeploy-studio'
                ],
                's3Log' => [
                    'region'      => 'ap-northeast-2',
                    'version'     => 'latest',
                    'key'         => 'AKIA56G4MQZHQXQ4QNLG',
                    'secret'      => '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',
                    'bucket_name' => 'logs-studio'
                ],
                'dynamodb' => [
                    'region'  => 'ap-northeast-1',
                    'version' => '2012-08-10',
                    'key'     => 'AKIAJRPI6CNQTAFOBIIA',
                    'secret'  => '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'
                ],
            ],

            // Language Pack
            'language' => [
                'availableLang' => ['KO', 'EN'],
                'defaultLang' => 'EN',
            ],

            'redis' => [
                'host' => [
                    'elc-studio-prod.gayhrs.ng.0001.apn2.cache.amazonaws.com'
                ],
                'port' => [
                    '6379',
                ],
                'auth' => [
                    '',
                ],
                'connection_timeout' => 5,
                'crypt'              => false
            ],

            // RDB settings
            'rdb' => [
                'driver' => 'mysql',
                'host' => '172.31.27.219',
                'port' => 3306,
                'dbname' => 'bison_staging',
                'charset' => 'utf8mb4',
                'username' => 'app_server',
                'password' => 'lxBr8MmswCP3kQpy',
            ],
        ]
    ];
