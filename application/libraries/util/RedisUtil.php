<?php
namespace libraries\util;

use libraries\constant\CommonConst;
use libraries\log\LogMessage;
use models\redis\RedisMgr;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class RedisUtil
{
    /**
     * get redis shard info with redis key
     *
     * @param $key
     *
     * @return int
     */
    public static function getShard($key)
    {
        return ord(substr($key, -1));
    }

    /**
     * get is crypt of redis env
     * @return mixed
     */
    public static function getIsCrypt()
    {
        $config = include APP_DIR . 'config/' . APP_ENV . '.php';
        return $config['settings']['redis']['crypt'];
    }

    /**
     * get redis data
     *
     * @param RedisMgr $redis
     * @param          $key
     * @param int      $db
     *
     * @return bool|mixed|string
     * @throws \Exception
     */
    public static function getData(RedisMgr $redis, $key, $db = CommonConst::REDIS_SESSION)
    {
        $resData = $redis->getData($db, $key);
        //$debugInfo = debug_backtrace();
        //$logs = 'key[' . $key . ']_res[' . $resData . ']';
        //LogMessage::info($logs, $debugInfo[1]['class'], $debugInfo[0]['function'], $debugInfo[0]['line']);

        if (false === $resData) {
            return false;
        }

        /*
         * if json type, return with json_decode
         */
        if (null !== ($_res = json_decode($resData, true))) {
            //CommonUtil::showArrDump($_res);
            return $_res;
        }

        return $resData;
    }
	
	public static function getTTL(RedisMgr $redis, $key, $db = CommonConst::REDIS_SESSION)
	{
		$resData = $redis->getTtl($db, $key);
		if (false === $resData) {
            return false;
        }
		return $resData;
	}

    /**
     * get redis data with del
     *
     * @param RedisMgr $redis
     * @param          $key
     * @param int      $db
     *
     * @return bool|mixed|string
     * @throws \Exception
     */
    public static function getDataWithDel(RedisMgr $redis, $key, $db = CommonConst::REDIS_SESSION)
    {
        $debugInfo = debug_backtrace();

        $resData = $redis->getData($db, $key);

        $logs = 'key[' . $key . ']_res[' . $resData . ']';
        LogMessage::info($logs, $debugInfo[1]['class'], $debugInfo[0]['function'], $debugInfo[0]['line']);

        if (false === $resData) {
            return false;
        }

        /*
         * del to redis key
         */
        $is_del = $redis->del($db, $key);
        if (false === $is_del) {
            $logs = 'key[' . $key . ']_res[failed to del redis data]';
            LogMessage::error($logs, $debugInfo[1]['class'], $debugInfo[0]['function'], $debugInfo[0]['line']);
        }

        /*
         * if json type, return with json_decode
         */
        if (null !== ($_res=json_decode($resData, true))) {
            return $_res;
        }

        return $resData;
    }

    /**
     * delete redis data
     *
     * @param RedisMgr $redis
     * @param          $key
     * @param int      $db
     *
     * @return bool
     * @throws \Exception
     */
    public static function delData(RedisMgr $redis, $key, $db = CommonConst::REDIS_SESSION)
    {
        $debugInfo = debug_backtrace();

        /*
         * del to redis key
         */
        $is_del = $redis->del($db, $key);

        if (false === $is_del) {
            $logs = 'key[' . $key . ']_res[failed to del redis data]';
            LogMessage::info($logs, $debugInfo[1]['class'], $debugInfo[0]['function'], $debugInfo[0]['line']);
            return false;
        }

        return true;
    }

    /**
     * set redis data no expire time
     *
     * @param RedisMgr $redis
     * @param          $db
     * @param          $key
     * @param          $value
     *
     * @return bool
     * @throws \Exception
     */
    public static function setData(RedisMgr $redis, $db, $key, $value)
    {
        $debugInfo = debug_backtrace();

        if (is_array($value)) {
            $_value = json_encode($value, JSON_UNESCAPED_UNICODE);
        } else {
            $_value = $value;
        }

        $resData = $redis->setData($db, $key, $_value);

        //$logs = 'key[' . $key . ']_req[' . $_value . ']_res[' . $resData . ']';
        //LogMessage::info($logs, $debugInfo[1]['class'], $debugInfo[0]['function'], $debugInfo[0]['line']);

        return $resData;
    }

    /**
     * set redis data with expire time
     *
     * @param RedisMgr $redis
     * @param          $db
     * @param          $key
     * @param          $expireTime
     * @param          $value
     *
     * @return bool
     * @throws \Exception
     */
    public static function setDataWithExpire(RedisMgr $redis, $db, $key, $expireTime, $value)
    {
        $debugInfo = debug_backtrace();

        if (is_array($value)) {
            $_value = json_encode($value, JSON_UNESCAPED_UNICODE);
        } else {
            $_value = $value;
        }

        $resData = $redis->setDataWithExpire($db, $key, $expireTime, $_value);

        //$logs = 'key[' . $key . ']_req[' . $_value . ']_res[' . $resData . ']';
        //LogMessage::info($logs, $debugInfo[1]['class'], $debugInfo[0]['function'], $debugInfo[0]['line']);

        return $resData;
    }

    /**
     * set redis data without modify expire time
     *
     * @param RedisMgr $redis
     * @param          $key
     * @param          $value
     * @param int      $db
     *
     * @return mixed
     * @throws \Exception
     */
    public static function setDataNotModifyExpire(RedisMgr $redis, $key, $value, $db = CommonConst::REDIS_SESSION)
    {
        $debugInfo = debug_backtrace();

        if (is_array($value)) {
            $_value = json_encode($value, JSON_UNESCAPED_UNICODE);
        } else {
            $_value = $value;
        }

        $resData = $redis->setDataNotModifyExpire($db, $key, $_value);

        $logs = 'key[' . $key . ']_req[' . $_value . ']_res[' . $resData . ']';
        LogMessage::info($logs, $debugInfo[1]['class'], $debugInfo[0]['function'], $debugInfo[0]['line']);

        return $resData;
    }

    /**
     * get redis increment number
     *
     * @param RedisMgr $redis
     * @param          $key
     * @param int      $db
     *
     * @return int
     * @throws \Exception
     */
    public static function getIncr(RedisMgr $redis, $key, $db = CommonConst::REDIS_SESSION)
    {
        $debugInfo = debug_backtrace();

        $resData = $redis->incr($db, $key);

        $logs = 'key[' . $key . ']_res[' . $resData . ']';
        LogMessage::info($logs, $debugInfo[1]['class'], $debugInfo[0]['function'], $debugInfo[0]['line']);

        return $resData;
    }

    /**
     * get redis increment number with set expire
     *
     * @param RedisMgr $redis
     * @param          $key
     * @param          $expireTime
     * @param int      $db
     *
     * @return mixed
     * @throws \Exception
     */
    public static function getIncrWithExpire(RedisMgr $redis, $key, $expireTime, $db = CommonConst::REDIS_SESSION)
    {
        $debugInfo = debug_backtrace();

        $resData = $redis->incrWithExpire($db, $key, $expireTime);

        $logs = 'key[' . $key . ']_res[' . $resData . ']';
        LogMessage::info($logs, $debugInfo[1]['class'], $debugInfo[0]['function'], $debugInfo[0]['line']);

        return $resData;
    }

    /**
     * @param RedisMgr $redis
     * @param          $db
     * @param          $key
     * @param          $mode
     *
     * @return bool
     * @throws \Exception
     */
    public static function flush(RedisMgr $redis, $db, $key = null, $mode = null)
    {
        return $redis->flush($db, $key, $mode);
    }
}
