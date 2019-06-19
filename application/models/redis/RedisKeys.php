<?php
namespace models\redis;

use libraries\util\RedisUtil;
use libraries\constant\CommonConst;
use libraries\util\CommonUtil;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class RedisKeys
{
	/*
     * get router info
     */
    public static function getGMRouterInfo($who, $from, $prefix = CommonConst::REDIS_KEY_PREFIX)
    {
        $key = $who . '_get_gm_router_info_' . $from;
        if (!empty($prefix)) {
            $key = $prefix . '_' . $key;
        }

        if (true === RedisUtil::getIsCrypt()) {
            $key = CommonUtil::getHashKey($key);
        }

        return $key;
    }

    /*
     * get reserve command
     */
    public static function getReserveCommand($who, $from, $prefix = CommonConst::REDIS_KEY_PREFIX)
    {
        $key = $who . '_get_reserve_command_' . $from;
        if (!empty($prefix)) {
            $key = $prefix . '_' . $key;
        }

        if (true === RedisUtil::getIsCrypt()) {
            $key = CommonUtil::getHashKey($key);
        }

        return $key;
    }

    /*
     * get previous command
     */
    public static function getPreviousCommand($who, $from, $prefix = CommonConst::REDIS_KEY_PREFIX)
    {
        $key = $who . '_get_previous_command_' . $from;
        if (!empty($prefix)) {
            $key = $prefix . '_' . $key;
        }

        if (true === RedisUtil::getIsCrypt()) {
            $key = CommonUtil::getHashKey($key);
        }

        return $key;
    }

    /*
     * get session key
     */
    public static function getSessionKey($prefix = CommonConst::REDIS_KEY_PREFIX, $isCrypt = false)
    {
        $key = 'get_session_key';
        if (!empty($prefix)) {
            $key = $prefix . '_' . $key;
        }

        if (true === $isCrypt || true === RedisUtil::getIsCrypt()) {
            $key = CommonUtil::getHashKey($key);
        }

        return $key;
    }
	
	/*
     * get alarm key
     */
    public static function getAlarmKey($target, $prefix = CommonConst::REDIS_KEY_PREFIX, $isCrypt = false)
    {
        $key = 'get_alarm_key_'.$target;
        if (!empty($prefix)) {
            $key = $prefix . '_' . $key;
        }

        if (true === $isCrypt || true === RedisUtil::getIsCrypt()) {
            $key = CommonUtil::getHashKey($key);
        }

        return $key;
    }

    /*
     * get event key
     */
    public static function getEventKey($prefix = CommonConst::REDIS_KEY_PREFIX, $isCrypt = false)
    {
        $key = 'get_event_key';
        if (!empty($prefix)) {
            $key = $prefix . '_' . $key;
        }

        if (true === $isCrypt || true === RedisUtil::getIsCrypt()) {
            $key = CommonUtil::getHashKey($key);
        }

        return $key;
    }

    /*
     * get incr key
     */
    public static function getIncrKey($prefix = CommonConst::REDIS_KEY_PREFIX, $isCrypt = false)
    {
        $key = 'get_incr_key';
        if (!empty($prefix)) {
            $key = $prefix . '_' . $key;
        }

        if (true === $isCrypt || true === RedisUtil::getIsCrypt()) {
            $key = CommonUtil::getHashKey($key);
        }

        return $key;
    }
}
