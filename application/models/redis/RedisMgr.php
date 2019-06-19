<?php

namespace models\redis;

use libraries\crypt\AES;
use libraries\util\RedisUtil;
use libraries\util\CommonUtil;
use libraries\constant\CommonConst;
use libraries\log\LogMessage;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class RedisMgr
{
    protected $redisHost;
    protected $redisPort;
    protected $redisAuth;
    protected $redisConnectionTime;
    protected $connectionPool;

    /**
     * RedisMgr constructor.
     *
     * @param null $config
     *
     * @throws \Exception
     */
    public function __construct($config = null)
    {
        if (empty($config)) {
            $config = include APP_DIR . 'config/' . APP_ENV . '.php';
            $this->redisHost = $config['settings']['redis']['host'];
            $this->redisPort = $config['settings']['redis']['port'];
            $this->redisConnectionTime = $config['settings']['redis']['connection_timeout'];
            //$this->redisAuth = $config['settings']['redis']['auth'];
        } else {
            $this->redisHost = $config['host'];
            $this->redisPort = $config['port'];
            $this->redisConnectionTime = $config['connection_timeout'];
            //$this->redisAuth = $config['auth'];
        }

        foreach ($this->redisHost as $key => $val) {
            $this->redisAuth[$key] = CommonUtil::getSecureConfig(CommonConst::SECURE_REDIS_PASSWORD) ?? null;
        }

        /*
         * init redis connection pool
         */
        $this->connectionPool = [];
    }

    private function makeConnection($shard, $db)
    {
        $index = $shard % count($this->redisHost);

        if (true === array_key_exists($index, $this->connectionPool)) {
            $connection = $this->connectionPool[$index];
        } else {
            $connection = $this->tryConnect($index);
        }

        $this->selectDb($connection, $db);

        return $connection;
    }

    private function tryConnect($index)
    {
        try {
            $redisHost = $this->redisHost[$index];
            $redisPort = $this->redisPort[$index];
            $redisTimeout = $this->redisConnectionTime;

            if ( ! empty($this->redisAuth)) {
                $redisAuth = $this->redisAuth[$index];
            }

            /*
             * redis connect
             */
            $redis = new \Redis();
            $redis->connect($redisHost, $redisPort, $redisTimeout);

            /*
             * with authentication
             */
            if ( ! empty($redisAuth)) {
                $redis->auth($redisAuth);
            }

            if (true === array_key_exists($index, $this->connectionPool)) {
                $this->connectionPool[$index]->close();
                $this->connectionPool[$index] = null;
            }

            $this->connectionPool[$index] = $redis;
        } catch (\Exception $ex) {
            throw $ex;
        }

        return $redis;
    }

    private function selectDb($redis, $db)
    {
        $redis->select($db);
    }

    public function getData($db, $key)
    {
        try {
            $key = trim($key);

            $shard = RedisUtil::getShard($key);
            $connection = $this->makeConnection($shard, $db);

            $value = $connection->get($key);

            if (false !== $value) {
                if (true === RedisUtil::getIsCrypt()) {
                    $secureRedisKey = CommonUtil::getSecureConfig(CommonConst::SECURE_REDIS_KEY);
                    $value = AES::decrypt($value, $secureRedisKey);
                }
            }

            return $value;
        } catch (\Exception $ex) {
            $logs = 'key[' . $key . ']_res[' . $ex->getMessage() . ']';
            LogMessage::error($logs);

            throw $ex;
        }
    }

    public function setData($db, $key, $value, $option = null)
    {
        try {
            $key = trim($key);

            $shard = RedisUtil::getShard($key);
            $connection = $this->makeConnection($shard, $db);

            if (true === RedisUtil::getIsCrypt()) {
                $secureRedisKey = CommonUtil::getSecureConfig(CommonConst::SECURE_REDIS_KEY);
                $value = AES::encrypt($value, $secureRedisKey);
            }

            $isSet = $connection->set($key, $value, $option);
            if (true !== $isSet) {
                throw new \Exception('failed to set redis data');
            }

            return $isSet;
        } catch (\Exception $ex) {
            $logs = 'key[' . $key . ']_res[' . $ex->getMessage() . ']';
            LogMessage::error($logs);

            throw $ex;
        }
    }

    public function setDataWithExpire($db, $key, $expireTime, $value)
    {
        try {
            $key = trim($key);

            $shard = RedisUtil::getShard($key);
            $connection = $this->makeConnection($shard, $db);

            if (true === RedisUtil::getIsCrypt()) {
                $secureRedisKey = CommonUtil::getSecureConfig(CommonConst::SECURE_REDIS_KEY);
                $value = AES::encrypt($value, $secureRedisKey);
            }

            $isSet = $connection->setEx($key, $expireTime, $value);
            if (true !== $isSet) {
                throw new \Exception('failed to set redis data');
            }

            return $isSet;
        } catch (\Exception $ex) {
            $logs = 'key[' . $key . ']_res[' . $ex->getMessage() . ']';
            LogMessage::error($logs);

            throw $ex;
        }
    }

    public function setDataNotModifyExpire($db, $key, $value)
    {
        try {

            $key = trim($key);

            $shard = RedisUtil::getShard($key);
            $connection = $this->makeConnection($shard, $db);

            $script = "local ttl = redis.call('ttl', ARGV[1]) if ttl > 0 then return redis.call('SETEX', ARGV[1], ttl, ARGV[2]) end";

            if (true === RedisUtil::getIsCrypt()) {
                $secureRedisKey = CommonUtil::getSecureConfig(CommonConst::SECURE_REDIS_KEY);
                $value = AES::encrypt($value, $secureRedisKey);
            }

            $isSet = $connection->evaluate($script, [$key, $value]);

            if (true !== $isSet) {
                throw new \Exception('failed to set redis data');
            }

            return $isSet;

        } catch (\Exception $ex) {
            $logs = 'key[' . $key . ']_res[' . $ex->getMessage() . ']';
            LogMessage::error($logs);

            throw $ex;
        }
    }

    public function exist($db, $key)
    {
        try {
            $key = trim($key);

            $shard = RedisUtil::getShard($key);
            $connection = $this->makeConnection($shard, $db);

            return $connection->exists($key);
        } catch (\Exception $ex) {
            $logs = 'key[' . $key . ']_res[' . $ex->getMessage() . ']';
            LogMessage::error($logs);

            throw $ex;
        }
    }

    public function del($db, $key)
    {
        try {
            $key = trim($key);

            $shard = RedisUtil::getShard($key);
            $connection = $this->makeConnection($shard, $db);

            $res = $connection->del($key);
            if (empty($res)) {
                return false;
            }

            return true;
        } catch (\Exception $ex) {
            $logs = 'key[' . $key . ']_res[' . $ex->getMessage() . ']';
            LogMessage::error($logs);

            throw $ex;
        }
    }

    public function expire($db, $key, $expireTime)
    {
        try {
            $key = trim($key);

            $shard = RedisUtil::getShard($key);
            $connection = $this->makeConnection($shard, $db);

            return $connection->expire($key, $expireTime);
        } catch (\Exception $ex) {
            $logs = 'key[' . $key . ']_res[' . $ex->getMessage() . ']';
            LogMessage::error($logs);

            throw $ex;
        }
    }

    public function incr($db, $key)
    {
        try {
            $key = trim($key);

            $shard = RedisUtil::getShard($key);
            $connection = $this->makeConnection($shard, $db);

            return $connection->incr($key);
        } catch (\Exception $ex) {
            $logs = 'key[' . $key . ']_res[' . $ex->getMessage() . ']';
            LogMessage::error($logs);

            throw $ex;
        }
    }

    public function incrWithExpire($db, $key, $expire)
    {
        try {
            $key = trim($key);

            $shard = RedisUtil::getShard($key);
            $connection = $this->makeConnection($shard, $db);

            $script = "local r = redis.call('INCR', ARGV[1]) redis.call('EXPIRE', ARGV[1], ARGV[2]) return r";

            return $connection->evaluate($script, [$key, $expire]);
        } catch (\Exception $ex) {
            $logs = 'key[' . $key . ']_res[' . $ex->getMessage() . ']';
            LogMessage::error($logs);

            throw $ex;
        }
    }

    public function getTtl($db, $key)
    {
        try {
            $key = trim($key);

            $shard = RedisUtil::getShard($key);
            $connection = $this->makeConnection($shard, $db);

            return $connection->ttl($key);
        } catch (\Exception $ex) {
            $logs = 'key[' . $key . ']_res[' . $ex->getMessage() . ']';
            LogMessage::error($logs);

            throw $ex;
        }
    }

    public function setNx($db, $key, $value)
    {
        try {
            $key = trim($key);

            $shard = RedisUtil::getShard($key);
            $connection = $this->makeConnection($shard, $db);

            return $connection->setnx($key, $value);
        } catch (\Exception $ex) {
            $logs = 'key[' . $key . ']_res[' . $ex->getMessage() . ']';
            LogMessage::error($logs);

            throw $ex;
        }
    }
}
