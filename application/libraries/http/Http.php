<?php
namespace libraries\http;

use libraries\log\LogMessage;
use libraries\constant\CommonConst;
use libraries\util\CommonUtil;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Http
{
//    private static function _post($url, $options)
//    {
//        $client = new \GuzzleHttp\Client();
//        $ret = $client->request('POST', $url, $options);
//
//        $contents = $ret->getBody()->getContents();
//
//        $logs = '[url::' . $url . '][request::' . json_encode($options, JSON_UNESCAPED_UNICODE) . '][response::' . json_encode($contents, JSON_UNESCAPED_UNICODE) . ']';
//        LogMessage::debug($logs);
//
//        return $contents;
//    }
//
//    private static function _get($url, $options)
//    {
//        $client = new \GuzzleHttp\Client();
//        $ret = $client->request('GET', $url, $options);
//
//        $contents = $ret->getBody()->getContents();
//
//        $logs = '[url::' . $url . '][request::' . json_encode($options, JSON_UNESCAPED_UNICODE) . '][response::' . json_encode($contents, JSON_UNESCAPED_UNICODE) . ']';
//        LogMessage::debug($logs);
//
//        return $contents;
//    }
//
//    public static function get($url, $body, $addHeader = [], $connectTimeout = CommonConst::HTTP_CONNECT_TIMEOUT, $responseTimeout = CommonConst::HTTP_RESPONSE_TIMEOUT)
//    {
//        $options = [
//            'connect_timeout' => $connectTimeout,
//            'timeout' => $responseTimeout,
//            'headers' => $addHeader,
//            'query' => $body
//        ];
//
//        return self::_get($url, $options);
//    }
//
//    public static function post($url, $body, $addHeader = [], $connectTimeout = CommonConst::HTTP_CONNECT_TIMEOUT, $responseTimeout = CommonConst::HTTP_RESPONSE_TIMEOUT)
//    {
//        $headers = self::getHeaders($addHeader);
//
//        $options = [
//            'connect_timeout' => $connectTimeout,
//            'timeout' => $responseTimeout,
//            'headers' => $headers->toArray(),
//            'body' => $body
//        ];
//
//        return self::_post($url, $options);
//    }
//
//    public static function postJson($url, $body, $addHeader = [], $connectTimeout = CommonConst::HTTP_CONNECT_TIMEOUT, $responseTimeout = CommonConst::HTTP_RESPONSE_TIMEOUT)
//    {
//        $headers = self::getHeaders($addHeader);
//        $headers->put('Content-Type', 'application/json; charser=UTF-8');
//
//        $options = [
//            'connect_timeout' => $connectTimeout,
//            'timeout' => $responseTimeout,
//            'headers' => $headers->toArray(),
//            'json' => $body
//        ];
//
//        return self::_post($url, $options);
//    }
//
//    public static function postUrlencoded($url, $body, $addHeader = [], $connectTimeout = CommonConst::HTTP_CONNECT_TIMEOUT, $responseTimeout = CommonConst::HTTP_RESPONSE_TIMEOUT)
//    {
//        $headers = self::getHeaders($addHeader);
//        $headers->put('Content-Type', 'application/x-www-form-urlencoded; charser=UTF-8');
//
//        $options = [
//            'connect_timeout' => $connectTimeout,
//            'timeout' => $responseTimeout,
//            'headers' => $headers->toArray(),
//            'form_params' => $body
//        ];
//
//        return self::_post($url, $options);
//    }
//
//    private static function getHeaders($addHeader = [])
//    {
//        $headers = new \Ds\Map();
//
//        if (!empty($addHeader)) {
//            $headers->putAll($addHeader);
//        }
//
//        return $headers;
//    }
//
//    public static function redirect($uri, $method = 'redirect', $permanent = false)
//    {
//        if (!preg_match('#^(\w+:)?//#i', $uri)) {
//            return false;
//        }
//
//        if ($permanent === true) {
//            $code = 301;
//        } else {
//            // reference: http://en.wikipedia.org/wiki/Post/Redirect/Get
//            if (isset($_SERVER['SERVER_PROTOCOL'], $_SERVER['REQUEST_METHOD']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.1') {
//                $code = ($_SERVER['REQUEST_METHOD'] !== 'GET') ? 303 : 307;
//            } else {
//                $code = 302;
//            }
//        }
//
//        switch ($method) {
//            case 'refresh':
//                header('Refresh:0;url=' . $uri);
//                break;
//
//            default:
//                header('Location: ' . $uri, true, $code);
//                break;
//        }
//
//        exit;
//    }


    /**
     * guzzle request
     *
     * @param        $targetUrl
     * @param        $options
     * @param string $method
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function httpRequest($targetUrl, $options, $method = CommonConst::REQ_METHOD_GET)
    {
        $resData = null;
        $resStatus = null;

        try {

            $client = new \GuzzleHttp\Client();

            $ret = $client->request($method, $targetUrl, $options);
            $resData = $ret->getBody()->getContents();
            $resData = CommonUtil::getValidJSON($resData);
            $resStatus = $ret->getStatusCode() . ' ' . $ret->getReasonPhrase();
            LogMessage::info('`'. $method . '`' . ' url :: ' . $targetUrl . ', status :: ' . $resStatus . ', response :: ' , $ret->getBody()->getContents());

        } catch (\GuzzleHttp\Exception\ServerException $e) {
            preg_match('/(5[0-9]{2}[a-z\s]+)/i', $e->getMessage(), $output);
            $resStatus = $output[1];
            LogMessage::error('`'. $method . '`' . ' url :: ' . $targetUrl . ', error :: ' . $resStatus . ', options :: ' . json_encode($options, JSON_UNESCAPED_UNICODE));
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            preg_match('/(4[0-9]{2}[a-z\s]+)/i', $e->getMessage(), $output);
            $resStatus = $output[1];
            LogMessage::error('`'. $method . '`' . ' url :: ' . $targetUrl . ', error :: ' . $resStatus . ', options :: ' . json_encode($options, JSON_UNESCAPED_UNICODE));
        } catch (\Exception $e) {
            $resStatus = "Name or service not known";
            LogMessage::error('`'. $method . '`' . ' url :: ' . $targetUrl . ', error :: ' . $resStatus . ', options :: ' . json_encode($options, JSON_UNESCAPED_UNICODE));
        }

        return $resData;

    }

}
