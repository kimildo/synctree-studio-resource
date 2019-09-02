<?php

namespace libraries\util;

//use Interop\Container\ContainerInterface;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;

use libraries\constant\CommonConst;
use libraries\constant\ErrorConst;
use libraries\log\LogMessage;
use \Slim\Flash\Messages;

use models\rdb\RDbHandler;


class CommonUtil
{
    /**
     * get secure config
     *
     * @param $field
     *
     * @return mixed
     * @throws \Exception
     */
    public static function getSecureConfig($field)
    {
        $config = include APP_DIR . 'config/' . APP_ENV . '.php';

        $iniFiles = parse_ini_file($config['settings']['secure_config']['path'] . CommonConst::SECURE_CONFIG_FILE_NAME, true);

        if ( ! isset($iniFiles[$field])) {
            throw new \Exception('failed to load config file (field:' . $field . ')');
        }

        return $iniFiles[$field];
    }

    /**
     * extract contents from http body
     *
     * @param Request $request
     *
     * @return bool|string
     */
    public static function getContents(Request $request)
    {
        $params = trim($request->getBody()->getContents());

        if (empty($params)) {
            return false;
        }

        return $params;
    }

    /**
     * extract contents from http body
     *
     * @param Request $request
     *
     * @return array|bool|null|object
     */
    public static function getParsedBody(Request $request)
    {
        $params = $request->getParsedBody();

        if (empty($params)) {
            return false;
        }

        return $params;
    }

    /**
     * extract contents Query
     *
     * @param Request $request
     *
     * @return array|bool
     */
    public static function getQueryParams(Request $request)
    {
        $params = $request->getQueryParams();

        if (empty($params)) {
            return false;
        }

        return $params;
    }

    /**
     * get http params
     *
     * @param Request $request
     *
     * @return array|bool|null|object|string
     * @throws \Exception
     */
    public static function getParams(Request $request)
    {
        $params = null;

        try {
            /*
             * get request method
             */
            $method = $request->getMethod();

            switch (strtoupper($method)) {
                case 'POST' :
                case 'PUT' :
                case 'DELETE' :
                    if (false === ($params = self::getParsedBody($request))) {
                        if (false === ($params = self::getContents($request))) {
                            throw new \Exception('failed to params from http body with post');
                        }
                    }
                    break;
                case 'GET' :
                    if (false === ($params = self::getQueryParams($request))) {
                        throw new \Exception('failed to params from http body with get');
                    }
                    break;
                default :
                    throw new \Exception('Not allow http method (method:' . $method . ')');
            }

//            if ('POST' === strtoupper($method)) {
//                if (false === ($params = self::getParsedBody($request))) {
//                    if (false === ($params = self::getContents($request))) {
//                        throw new \Exception('failed to params from http body with post');
//                    }
//                }
//            } else {
//                if ('GET' === strtoupper($method)) {
//                    if (false === ($params = self::getQueryParams($request))) {
//                        throw new \Exception('failed to params from http body with get');
//                    }
//                } else {
//                    throw new \Exception('Not allow http method (method:' . $method . ')');
//                }
//            }


        } catch (\Exception $ex) {

        }

        return $params;
    }

    public static function getHashKey($data, $algo = 'sha256')
    {
        return hash($algo, trim($data), false);
    }

    public static function getUsec($length = 6)
    {
        list($usec, $sec) = explode(" ", microtime());
        unset($sec);

        return str_pad(substr($usec, strpos($usec, '.') + 1, $length), $length, '0');
    }

    /**
     * @param $params
     * @param $fields
     * @param $emptyCheck
     *
     * @return bool
     */
    public static function validateParams($params, $fields, $emptyCheck = false)
    {
        if ( ! is_array($params)) {
            $logs = 'Not array type';
            LogMessage::debug($logs);

            return false;
        }

        foreach ($fields as $key => $field) {
            if (is_array($field)) {
                foreach ($field as $sub) {

                    if ( ! isset($params[$key][$sub])) {
                        $logs = 'Not valid required field(1) (' . $sub . ' of ' . $key . ')';
                        LogMessage::debug($logs);
                        return false;
                    }

                    if ( ! empty($emptyCheck) && ($params[$key][$sub] === '' || $params[$key][$sub] === null)) {
                        $logs = 'Empty required field(2) (' . $sub . ' of ' . $key . ')';
                        LogMessage::debug($logs);
                        return false;
                    }

                }

            } else {

                if (true === array_key_exists($field, $params)) {

                    if ( ! isset($params[$field])) {
                        $logs = 'Empty required field(3) (' . $field . ')';
                        LogMessage::debug($logs);
                        return false;
                    }

                    if ( ! empty($emptyCheck) && ($params[$field] === '' || $params[$field] === null)) {
                        $logs = 'Empty required field(4) (' . $field . ')';
                        LogMessage::debug($logs);
                        return false;
                    }

                } else {

                    $logs = 'Not found required field(5) (' . $field . ')';
                    LogMessage::debug($logs);
                    return false;

                }

            }
        }

        return true;
    }

    /**
     *
     * @param null $domain
     *
     * @return string
     */
    public static function getDomain($domain = null)
    {
        if ( ! empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'];
        } else {

            if (isset($_SERVER['HTTPS']) && ! empty($_SERVER['HTTPS'])) {
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = 'http';
            }
        }

        if (APP_ENV !== APP_ENV_DEVELOPMENT_LOCAL && APP_ENV !== APP_ENV_DEVELOPMENT_LOCAL_KIMILDO) {
            $protocol = 'https';
        }

        return $protocol . "://" . ($domain ?? $_SERVER['HTTP_HOST']);
    }


    /**
     *
     * @param string $uri
     * @param array  $params
     *
     * @return string
     */
    public static function getBaseUrl($uri = '', $params = [])
    {
        $url = self::getDomain() . $uri;

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

		return $url;
    }

    public static function getLocalHostUrl($uri = '', $params = [])
    {
        $url = self::getDomain('localhost:80') . $uri;
        $url = str_replace('https', 'http', $url);

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }


    public static function makeUrl($uri = '', $params = [])
    {
        $url = $uri;

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }



    /**
     * 프로시저 실행
     *
     * @param ContainerInterface $ci
     * @param string             $methodName
     * @param array              $procedureParams
     * @param string             $dbName
     * @param bool               $autoConnect
     *
     * @return mixed
     */
    public static function callProcedure(
        ContainerInterface $ci,
        $methodName = 'excuteGetSocialList',
        $procedureParams = [],
        $dbName = '',
        $autoConnect = true
    ) {

        switch ($dbName) {
            case 'm1' :
                $targetDb = $ci->get('rdb_m1');
                break;
            case 'rdb_korail' :
                $targetDb = $ci->get('rdb_korail');
                break;
            default :
                $targetDb = $ci->get('rdb');
        }

        if (true === $autoConnect) {
            $targetDb->makeConnection();
        }

        $result = RDbHandler::$methodName($targetDb, $procedureParams);

        if (true === $autoConnect) {
            $targetDb->closeConnection();
        }

        if (APP_ENV !== APP_ENV_PRODUCTION) {
            LogMessage::debug('procedure name ::' . $methodName);
            LogMessage::debug('returnCode::' . $result['returnCode']);
            LogMessage::debug('returnCodeMessage::' . $result['message']);
            LogMessage::debug('parameter::' . json_encode($procedureParams, JSON_UNESCAPED_UNICODE));
            LogMessage::debug('resultSet::' . json_encode($result['data'], JSON_UNESCAPED_UNICODE));
        }

        return $result;
    }


    /**
     * 값에 맞는 데이터를 가지고 온다.
     * Redis 체크하고 없으면 DB 쿼리 질의
     *
     * @param       $ci
     * @param       $key
     * @param       $methodName
     * @param array $params
     *
     * @return bool|mixed|string
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function getRedisData($ci, $key, $methodName, $params = [])
    {
        $db = CommonConst::REDIS_CLIENT_SESSION;

        if (true == $ci->redis->exist($db, $key)) {
            return RedisUtil::getData($ci->redis, $key, $db);
        }

        try {

            $result = self::callProcedure($ci, $methodName, $params);
            if (0 !== $result['returnCode']) {
                throw new \Exception('', $result['returnCode']);
            }

            RedisUtil::setData($ci->redis, $db, $key, $result['data']);

        } catch (\Exception $ex) {
            //LogMessage::error($ex->getMessage());
            self::getErrorMessage($ex);
            throw $ex;
        }

        return $result['data'];
    }

    /**
     * @param       $ci
     * @param       $key
     * @param       $methodName
     * @param       $expire
     * @param array $params
     *
     * @return bool|mixed|string
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function resetRedisData($ci, $key, $methodName, $expire, $params = [])
    {
        try {
            RedisUtil::delData($ci->redis, $key, CommonConst::REDIS_SESSION);
            $result = self::getRedisData($ci, $key, $methodName, $expire, $params);
        } catch (\Exception $ex) {
            LogMessage::error($ex->getMessage());
            throw $ex;
        }

        return $result;
    }



    /**
     * @return mixed
     */
    public static function getTrackingID()
    {
        $config = include APP_DIR . 'config/' . APP_ENV . '.php';

        return $config['settings']['analytics']['tracking_id'] ?? null;
    }

    /**
     * 특수문자 제거
     *
     * @param $string
     * @param $pattern
     *
     * @return null|string|string[]
     */
    public static function removeSpecialCharacters($string, $pattern = null)
    {
        if (empty($pattern)) {
            //$pattern = "/[\&%=\/\\\:;,\.'\"\^`~|\!\?\*$<>()\[\]\{\}]/i";
            $pattern = "/[\'\"\^]/i";
        } else {
            $pattern = "/" . $pattern . "/i";
        }

        return preg_replace($pattern, "", $string);
    }

    /**
     * 이메일이나 이름등을 * 표시
     *
     * @param        $str
     * @param string $type
     * @param string $replaceChar
     * @param int    $showLength
     *
     * @return string|void
     */
    public static function setAsteriskString($str, $type = 'string', $replaceChar = '*', $showLength = 4)
    {
        if (empty($str)) {
            return;
        }

        if ($type == 'email') {
            $mail_parts = explode('@', $str);
            $string = $mail_parts[0];
            $length = mb_strlen($mail_parts[0], 'UTF-8');
            $suffix = '@' . $mail_parts[1];
        } else {
            $string = $str;
            $length = mb_strlen($str, 'UTF-8');
            $suffix = '';
        }

        if ($length <= $showLength & $length >= 1) {
            $showLength = 1;
        }

        $hide = $length - $showLength;
        $replace = str_repeat($replaceChar, $hide);

        //return substr_replace($string, $replace, $showLength, $hide) . $suffix;
        return mb_substr($string, 0, $showLength, 'UTF-8') . $replace . $suffix;

    }

    /**
     * @return string
     */
    public static function getUniqueID()
    {
        if (function_exists('posix_getpid')) {
            $baseUnique = posix_getpid();
        } else {
            $baseUnique = mt_rand();
        }

        return uniqid($baseUnique, true);
    }

    /**
     * return Remote IP
     * @return mixed
     */
    public static function getUserIP()
    {
        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (!empty($forward)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }

        return $ip;
    }

    /**
     * 에러 메세지 반환 메소드
     *
     * @param      $ex
     * @param      $flash
     *
     * @return mixed
     */
    public static function getErrorMessage(\Exception $ex, Messages $flash = null)
    {

        $errCode = $ex->getCode();
        $errMessage = ErrorConst::ERROR_DESCRIPTION_ARRAY[$errCode] ?? ErrorConst::ERROR_DESCRIPTION_ARRAY[0];

        if (stristr($errMessage, 'SQLSTATE')) {
            $errMessage = ErrorConst::ERROR_DESCRIPTION_ARRAY[0] . ' (' . $errCode . ')';
        }

        if (empty($errCode) && $ex->getMessage() != '') {
            $errMessage = $ex->getMessage();
        }

        $trace = debug_backtrace();
        $logMessage = $ex->getMessage() . ' - ' . $errMessage;
        LogMessage::error('Error :: ' . $logMessage, $trace[1]['class'], $trace[0]['function'], $trace[0]['line']);

        if ($flash instanceof Messages) {
            $flash->addMessage('error', $errMessage);
        }

        return $errMessage;

    }
	/**
     * @param $input
     * @return string
     */
    public static function base64UrlEncode($input) {
        return strtr(base64_encode($input), '+/=', '._-');
    }

    /**
     * 배열값 표시후 종료
     *
     * @param      $arr
     * @param bool $exit
     */
    public static function showArrDump($arr, $exit = true)
    {
        echo '<pre>';
        var_dump($arr);
        echo '</pre>';

        if (!empty($exit)) {
            exit();
        }
    }

    /**
     * XML 유효성 검사
     *
     * @param $content
     *
     * @return bool
     */
    public static function isValidXml($content)
    {
        $content = trim($content);
        if (empty($content)) {
            return false;
        }
        //html go to hell!
        if (stripos($content, '<!DOCTYPE html>') !== false) {
            return false;
        }

        libxml_use_internal_errors(true);
        simplexml_load_string($content);
        $errors = libxml_get_errors();
        libxml_clear_errors();

        return empty($errors);
    }

    /**
     * JSON Valid Check
     *
     * @param $string
     *
     * @return bool
     */
    public static function isValidJSON($string)
    {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    public static function getValidJSON($string, $assoc = true)
    {
        if (true === self::isValidJSON($string)) {
            return json_decode($string, $assoc);
        }

        return $string;
    }

    /**
     * DateTime 반환
     *
     * @param        $interval 오늘기준 Interval
     * @param string $type     Interval 유형 (year : 'Y', month : 'M', day : 'D', minute : 'I', hour : 'H')
     *
     * @return string
     * @throws \Exception
     */
    public static function getDateTime($interval = 0, $type = 'D')
    {
        $date = new \DateTime();
        $type = strtoupper($type);

        switch ($type) {
            case 'I' :
                $intervalSpec = 'PT';
                $type = 'M';
                break;
            case 'H' :
                $intervalSpec = 'PT';
                break;
            default :
                $intervalSpec = 'P';
        }

        if ($interval > 0) {
            $date->add(new \DateInterval($intervalSpec . $interval . $type));
        } else {
            $interval = 0 - $interval;
            $date->sub(new \DateInterval($intervalSpec . $interval . $type));
        }

        return $date->format('Y-m-d H:i:s');
    }

    /**
     * @return float
     */
    public static function getMicroTime()
    {
        list($usec, $sec) = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * 슬랙 메세지로 발송
     *
     * @param        $msg
     * @param string $userName
     * @param string $webHookUrl
     * @param string $channel
     *
     * @return bool
     */
    public static function sendSlack($msg, $userName = '', $webHookUrl = '', $channel = '')
    {
        try {

            $slack = new SlackMessage();

            if (!empty($userName)) {
                $slack->setUserName($userName);
            }

            if (!empty($webHookUrl)) {
                $slack->setUserName($webHookUrl);
            }

            if (!empty($channel)) {
                $slack->setChannel($channel);
            }

            $slack->setMessage($msg);
            $slack->send();

        } catch (\Exception $e) {
            LogMessage::error('Slack send fail');
        }

        return true;
    }



}
