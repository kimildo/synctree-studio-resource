<?php

namespace libraries\log;

use libraries\util\CommonUtil;
use \Monolog\Logger;
use \Monolog\Processor\UidProcessor;
use \Monolog\Handler\StreamHandler;
use Elasticsearch\ClientBuilder;


class ElasticSearch
{

    static private $_client;

    public static function testElk(array $data = [])
    {
        if (empty($data)) {
            $data = [
                'index' => 'my_index',
                'type' => 'my_type',
                'id' => 'my_id',
                'body' => ['testField' => 'abc']
            ];
        }

        $client = self::_setClient();
        $response = $client->index($data);
        print_r($response);

    }

    private static function _setClient()
    {
        $hosts = [
            // This is effectively equal to: "https://username:password!#$?*abc@foo.com:9200/"
            //[
            //    'host' => 'foo.com',
            //    'port' => '9200',
            //    'scheme' => 'https',
            //    'user' => 'username',
            //    'pass' => 'password!#$?*abc'
            //],
            // This is equal to "http://localhost:9200/"
            [
                'host' => 'localhost',    // Only host is required
            ]
        ];

        self::$_client = ClientBuilder::create()       // Instantiate a new ClientBuilder
                ->setHosts($hosts)              // Set the hosts
                ->build();                      // Build the client object

    }



}