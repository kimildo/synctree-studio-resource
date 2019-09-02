<?php

namespace libraries\util;

use libraries\constant\CommonConst;
use libraries\constant\ErrorConst;
use libraries\constant\TFConst;
use libraries\log\LogMessage;
use \Slim\Flash\Messages;


class TidesqureUtil
{

    /**
     * 타이드 스퀘어 권한 체크
     *
     * @param Request  $request
     * @param $params
     *
     * @return bool $results
     */
    public static function authCheck($request, $params) : bool
    {
        if (false === CommonUtil::validateParams($params, ['supplierCode'])) {
            return false;
        }

        if ($params['supplierCode'] !== TFConst::TS_SUPPLIER_CODE) {
            LogMessage::error('TS Auth Fail - supplierCode :: ' . $params['supplierCode']);
            return false;
        }

        $accessToken = '';
        $headers = $request->getHeaders();

        // supplier code
        if (isset($headers['HTTP_AUTHORIZATION']) && !empty($headers['HTTP_AUTHORIZATION'])) {
            $accessToken = str_replace('Bearer ', '', $headers['HTTP_AUTHORIZATION'][0]);
        } elseif (isset($headers['HTTP_ACCESS_TOKEN']) && !empty($headers['HTTP_ACCESS_TOKEN'])) {
            $accessToken = $headers['HTTP_ACCESS_TOKEN'][0];
        }

        $accessTokenConst = (APP_ENV === APP_ENV_PRODUCTION) ? TFConst::TS_ACCESS_TOKEN : TFConst::TS_DEV_ACCESS_TOKEN;

        if ($accessToken != $accessTokenConst) {
            LogMessage::error('TS Auth Fail - AccessToken :: ' . $accessToken);
            return false;
        }

        // supplier code
        if (!isset($headers['HTTP_SUPPLIER_CODE'])) {
            LogMessage::error('TS Auth Fail - Supplier Code is not exist');
            return false;
        }

        if ($headers['HTTP_SUPPLIER_CODE'][0] !== TFConst::TS_SUPPLIER_CODE) {
            LogMessage::error('TS Auth Fail - Supplier Code :: ' . $accessToken);
            return false;
        }

        return true;

    }


}