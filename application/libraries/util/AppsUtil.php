<?php

namespace libraries\util;

use libraries\constant\CommonConst;
use libraries\constant\ErrorConst;
use libraries\log\LogMessage;
use \Slim\Flash\Messages;


class AppsUtil
{

    /**
     * 오퍼레이션의 정보를 가지고 온다.
     *
     * @param $ci
     * @param $accountId
     * @param $teamId
     * @param $opId
     *
     * @return array|bool|mixed|string
     */
    public static function getOperationInfo($ci, $accountId, $teamId, $opId)
    {
        try {

            $opt = [
                'account_id'   => $accountId,
                'team_id'      => $teamId,
                'operation_id' => $opId,
            ];

            $db = CommonConst::REDIS_CLIENT_SESSION;
            $key = CommonConst::CLIENT_OPS_REDIS_KEY . $opId;

            if (true === $ci->redis->exist($db, $key)) {
                return RedisUtil::getData($ci->redis, $key, $db);
            }

            $result = CommonUtil::callProcedure($ci, 'executeGetOpt', $opt);
            if (0 !== $result['returnCode']) {
                return false;
            }

            $opInfo = $result['data'][0][1][0];
            $opInfoTargetUrlInfo = $result['data'][1][1][0] ?? null;

            $ops = [
                'op_id'                     => $opInfo['operation_id'],
                'op_key'                    => $opInfo['operation_key'],
                'op_name'                   => $opInfo['operation_name'],
                'op_ns_name'                => $opInfo['operation_namespace_name'] ?? '',
                'op_desc'                   => $opInfo['operation_description'],
                'method'                    => $opInfo['protocol_type_code'],
                'req_method'                => self::getReqMethod($opInfo['request_method_code']),
                'regist_date'               => $opInfo['register_date'],
                'modify_date'               => $opInfo['last_modify_date'],
                'target_url'                => $opInfoTargetUrlInfo['target_url'] ?? null,
                'header_transfer_type_code' => $opInfo['header_transfer_type_code'] ?? CommonConst::HTTP_HEADER_CONTENTS_TYPE_FORM_DATA_CODE,
                'auth_type_code'            => $opInfo['auth_type_code'],
                'request'                   => [],
                'response'                  => [],
            ];

            $result = CommonUtil::callProcedure($ci, 'executeGetOptParams', $opt);
            foreach ($result['data'][1] as $reqs) {
                $arrKey = ($reqs['direction_code'] === CommonConst::DIRECTION_IN_CODE) ? 'request' : 'response';
                $varKeyPreFix = ($reqs['direction_code'] === CommonConst::DIRECTION_IN_CODE) ? 'req' : 'res';
                $ops[$arrKey][] = [
                    'param_id'                       => $reqs['parameter_id'],
                    'param_seq'                      => $reqs['parameter_seq'],
                    $varKeyPreFix . '_required_flag' => $reqs['required_flag'],
                    $varKeyPreFix . '_key'           => $reqs['parameter_key_name'],
                    $varKeyPreFix . '_var_type'      => CommonConst::VAR_TYPE_CODE_TO_STR[$reqs['parameter_type_code']],
                    $varKeyPreFix . '_desc'          => $reqs['parameter_description'] ?? null,
                    'sub_parameter_format'           => CommonUtil::getValidJSON($reqs['sub_parameter_format']),
                ];

            }

            RedisUtil::setData($ci->redis, $db, $key, $ops);

        } catch (\Exception $ex) {
            LogMessage::error('Fail to Get Operator :: AppsUtil');
            $ops = false;
        }

        return $ops;
    }


    /**
     * Endpoint 호출 샘플코드 반환
     *
     * @param int   $snipet     CodeType
     * @param array $bizData
     *
     * @return mixed
     */
    public static function getSampleSource($snipet = 1, $bizData = [])
    {
        /**
         * const SAMPLE_SOURCE_TYPE_CURL_CODE      = 1;
         * const SAMPLE_SOURCE_TYPE_JQUERY_CODE    = 2;
         * const SAMPLE_SOURCE_TYPE_RUBY_CODE      = 3;
         * const SAMPLE_SOURCE_TYPE_PYTHON_CODE    = 4;
         * const SAMPLE_SOURCE_TYPE_NODE_CODE      = 5;
         * const SAMPLE_SOURCE_TYPE_PHP_CODE       = 6;
         * const SAMPLE_SOURCE_TYPE_GO_CODE        = 7;
         */

        if (empty($bizData)) {
            return false;
        }

        switch ($snipet) {

            case CommonConst::SAMPLE_SOURCE_TYPE_CURL_CODE :
                $result['type'] = 'bash';
                $result['code'] = 'curl --location --request';
                if ($bizData['req_method'] === CommonConst::REQ_METHOD_POST_STR) {
                    $result['code'] .= ' POST "'. $bizData['product_end_point_url'] .'" \\' . PHP_EOL;
                    foreach ($bizData['request'] as $req) {
                        $result['code'] .= ' --form "'. $req['req_key'] .'={{'. $req['req_key'] .'}}" \\' . PHP_EOL;
                    }
                } else {
                    $result['code'] .= ' GET "'. $bizData['product_end_point'] .'"' . PHP_EOL;
                    $result['code'] .= ' --data ""' ;
                }
                break;

            case CommonConst::SAMPLE_SOURCE_TYPE_JQUERY_CODE :
                $result['type'] = 'javascript';
                $result['code'] = '';

                if ($bizData['req_method'] === CommonConst::REQ_METHOD_POST_STR) {
                    $result['code'] .= 'var form = new FormData();' . PHP_EOL;
                    foreach ($bizData['request'] as $req) {
                        $result['code'] .= 'form.append("'. $req['req_key'] .'", "{{'. $req['req_key'] .'}}");' . PHP_EOL;
                    }
                }

                $result['code'] .= 'var settings = {' . PHP_EOL;
                $result['code'] .= '    "url": ';
                $result['code'] .= '"' . (($bizData['req_method'] == CommonConst::REQ_METHOD_GET_STR)
                        ? $bizData['product_end_point'] : $bizData['product_end_point_url']) . '",' . PHP_EOL;

                $result['code'] .= '    "method": "' . (($bizData['req_method'] == CommonConst::REQ_METHOD_GET_STR) ? 'GET' : 'POST') . '",' . PHP_EOL;
                $result['code'] .= '    "timeout": 0,' . PHP_EOL;

                if ($bizData['req_method'] === CommonConst::REQ_METHOD_POST_STR) {
                    $result['code'] .= '    "processData": false,' . PHP_EOL;
                    $result['code'] .= '    "mimeType": "multipart/form-data",' . PHP_EOL;
                    $result['code'] .= '    "contentType": false,' . PHP_EOL;
                    $result['code'] .= '    "data": form' . PHP_EOL;
                }

                $result['code'] .= '}' . PHP_EOL;

                $result['code'] .= <<<'SOURCE'
$.ajax(settings).done(function (response) {
    console.log(response);
});
SOURCE;
                break;


            case CommonConst::SAMPLE_SOURCE_TYPE_PHP_CODE :
                $result['type'] = 'php';
                //$result['code'] = '<span><</span><span>?</span>php' . PHP_EOL;
                $result['code'] = '<?php' . PHP_EOL;
                $result['code'] .= '    $curl = curl_init();' . PHP_EOL;
                $result['code'] .= '    curl_setopt_array($curl, [' . PHP_EOL;
                $result['code'] .= '        CURLOPT_URL => "' . (($bizData['req_method'] == CommonConst::REQ_METHOD_GET_STR)
                                ? $bizData['product_end_point'] : $bizData['product_end_point_url']) . '",' . PHP_EOL;

                $result['code'] .= <<<'SOURCE'
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
SOURCE;
                $result['code'] .= PHP_EOL. '        CURLOPT_CUSTOMREQUEST => "' . (($bizData['req_method'] == CommonConst::REQ_METHOD_GET_STR) ? 'GET' : 'POST') . '",' . PHP_EOL;

                if ($bizData['req_method'] === CommonConst::REQ_METHOD_POST_STR) {
                    $result['code'] .= '        CURLOPT_POSTFIELDS => [';
                    foreach ($bizData['request'] as $req) {
                        $result['code'] .= '"' . $req['req_key'] . '" => "{{' . $req['req_key'] . '}}", ';
                    }
                    $result['code'] .= ']' . PHP_EOL;
                }
                $result['code'] .= '    ]);' . PHP_EOL;
                $result['code'] .= <<<'SOURCE'
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
      echo "cURL Error #:" . $err;
    } else {
      echo $response;
    }
SOURCE;
                break;

            default :
                $result['type'] = 'json';
                $result['code'] = $snipet;
        }

        return $result;
    }


    /**
     * 보안 프로토콜 샘플 코드
     *
     * @param int    $snipet
     * @param string $targeUrl
     * @param array  $bizData
     *
     * @return bool
     */
    public static function getSecureSampleSource($snipet = 1, $targeUrl = '', $bizData = [])
    {

        /**
         * const SAMPLE_SOURCE_TYPE_CURL_CODE      = 1;
         * const SAMPLE_SOURCE_TYPE_JQUERY_CODE    = 2;
         * const SAMPLE_SOURCE_TYPE_RUBY_CODE      = 3;
         * const SAMPLE_SOURCE_TYPE_PYTHON_CODE    = 4;
         * const SAMPLE_SOURCE_TYPE_NODE_CODE      = 5;
         * const SAMPLE_SOURCE_TYPE_PHP_CODE       = 6;
         * const SAMPLE_SOURCE_TYPE_GO_CODE        = 7;
         */

        if (empty($targeUrl) || empty($bizData)) {
            return false;
        }

        //CommonUtil::showArrDump($bizData);

        $opGroup = [];
        foreach ($bizData['operators'] as $op) {
            if ($targeUrl == $op['target_url']) {
                $opGroup[] = $op;
            }
        }

        if (empty($opGroup)) {
            return false;
        }

        //CommonUtil::showArrDump($bizData);

        //$reqMethod = self::getReqMethodString($opInfo['req_method']);
        $reqMethod = 'POST';
        $getCommendUrl = CommonUtil::getBaseUrl($bizData['get_command']);

        switch ($snipet) {

            case CommonConst::SAMPLE_SOURCE_TYPE_JQUERY_CODE :
                $result['type'] = 'jQeury';
                $result['code'] = '';
                break;

            case CommonConst::SAMPLE_SOURCE_TYPE_NODE_CODE :
                $result['type'] = 'javascript';
                $result['code'] = '';
                $result['code'] .= <<<'SOURCE'
const express = require('express')
const app = express()
const axios = require('axios') // https://github.com/axios/axios
const bodyParser = require('body-parser')


SOURCE;
                $result['code'] .= 'app.post(\'/yourRouter\', (req, res) => {' . PHP_EOL;

                $result['code'] .= "\t" . 'const eventKey = req.body.event_key' . PHP_EOL;
                $result['code'] .= "\t" . 'const url = \''. $getCommendUrl .'\'' . PHP_EOL;
                $result['code'] .= "\t" . 'const p = { event_key: eventKey }' . PHP_EOL;
                $result['code'] .= "\t" . 'let instance = axios.create()' . PHP_EOL . PHP_EOL;

                $result['code'] .= "\t" . 'instance' . PHP_EOL;
                $result['code'] .= "\t\t" . '.post(url, p, {' . PHP_EOL;
                $result['code'] .= "\t\t\t" . 'headers: {' . PHP_EOL;
                $result['code'] .= "\t\t\t\t" . '\'Content-Type\': \'application/json\'' . PHP_EOL;
                $result['code'] .= "\t\t\t" . '}' . PHP_EOL;
                $result['code'] .= "\t\t" . '})' . PHP_EOL;

                $result['code'] .= "\t\t" . '.then((response) => {' . PHP_EOL . PHP_EOL;

                $result['code'] .= "\t\t\t" . 'const opCode = response.body.op_code' . PHP_EOL;
                $result['code'] .= "\t\t\t" . 'switch (opCode) { ' . PHP_EOL . PHP_EOL;
                foreach ($opGroup as $opInfo) {
                    $result['code'] .= "\t\t\t\t" . 'case '. $opInfo['operation_id'] .' : ' . ' // ' . $opInfo['op_name'] . ' - ' . ($opInfo['operation_description'] ?? '') . PHP_EOL . PHP_EOL;

                    foreach ($opInfo['request'] as $req) {
                        $result['code'] .= "\t\t\t\t\t" . 'const ' . $req['req_key'] . ' = response.body.params.' . $req['req_key'] . ';' . PHP_EOL;
                    }

                    $result['code'] .= PHP_EOL . "\t\t\t\t\t" . '// Do your work with the above variables' . PHP_EOL . PHP_EOL;

                    $result['code'] .= "\t\t\t\t\t" . 'let result = {' . PHP_EOL;
                    foreach ($opInfo['response'] as $req) {
                        $result['code'] .= "\t\t\t\t\t\t" . '\'' . $req['res_key'] . '\': {some_your_data},' . PHP_EOL;
                    }
                    $result['code'] .= "\t\t\t\t\t" . '}' . PHP_EOL;
                    $result['code'] .= PHP_EOL;

                    $result['code'] .= "\t\t\t\t\t" . 'break;' . PHP_EOL . PHP_EOL;
                }

                $result['code'] .= "\t\t\t" . '}' . PHP_EOL;
                $result['code'] .= PHP_EOL;

                $result['code'] .= "\t\t\t" . 'res.send(result)' . PHP_EOL;
                $result['code'] .= "\t\t" . '})' . PHP_EOL;

                $result['code'] .= "\t\t" . '.catch((error) => {' . PHP_EOL;
                $result['code'] .= "\t\t\t" . 'console.log(\'request error\', error)' . PHP_EOL;
                $result['code'] .= "\t\t" . '})' . PHP_EOL;

                $result['code'] .= '})' . PHP_EOL;

                break;

            case CommonConst::SAMPLE_SOURCE_TYPE_PHP_CODE :
                $result['type'] = 'php';
                $result['code'] = '<?php' . PHP_EOL;
                $result['code'] .= '$eventKey = $_' . $reqMethod . '[\'event_key\'];' . PHP_EOL;
                $result['code'] .= '$curl = curl_init();' . PHP_EOL;
                $result['code'] .= 'curl_setopt_array($curl, [' . PHP_EOL;
                $result['code'] .= "\t" . 'CURLOPT_URL => "' . $getCommendUrl . '",' . PHP_EOL;
                $result['code'] .= "\t" . 'CURLOPT_RETURNTRANSFER => true,' . PHP_EOL;
                $result['code'] .= "\t" . 'CURLOPT_CUSTOMREQUEST => "' . CommonConst::REQ_METHOD_POST . '",' . PHP_EOL;
                $result['code'] .= "\t" . 'CURLOPT_POSTFIELDS => ["event_key" => $eventKey]' . PHP_EOL;
                $result['code'] .= ']);' . PHP_EOL;
                $result['code'] .= <<<'SOURCE'
                
$retData = curl_exec($curl);
$retData = json_decode($retData, true);
$err = curl_error($curl);

curl_close($curl);

if (!empty($err)) {
  return json_encode(['result' => false, 'message' => 'cURL Error #:' . $err], JSON_UNESCAPED_UNICODE);
}

SOURCE;
                $result['code'] .= PHP_EOL;


                if (count($opGroup) > 1) {

                    $result['code'] .= '$opCode = $retData[\'data\'][\'op_code\'];';
                    $result['code'] .= ' // operator unique code' . PHP_EOL . PHP_EOL;
                    $result['code'] .= '// The operation differs depending on the operator\'s unique code.' . PHP_EOL;
                    $result['code'] .= 'switch ($opCode) { ' . PHP_EOL . PHP_EOL;

                    foreach ($opGroup as $opInfo) {
                        $result['code'] .= "\t" . 'case '. $opInfo['operation_id'] .' : ' . ' // ' . $opInfo['op_name'] . ' - ' . ($opInfo['operation_description'] ?? '') . PHP_EOL . PHP_EOL;

                        foreach ($opInfo['request'] as $req) {
                            $result['code'] .= "\t\t" . '$' . $req['req_key'] . ' = $retData[\'data\'][\'params\'][\'' . $req['req_key'] . '\'];' . PHP_EOL;
                        }

                        $result['code'] .= PHP_EOL . "\t\t" . '// Do your work with the above variables' . PHP_EOL . PHP_EOL;

                        $result['code'] .= "\t\t" . '$result = [' . PHP_EOL;
                        foreach ($opInfo['response'] as $req) {
                            $result['code'] .= "\t\t\t" . '\'' . $req['res_key'] . '\' => ${some_your_data},' . PHP_EOL;
                        }
                        $result['code'] .= "\t\t" . '];' . PHP_EOL;
                        $result['code'] .= PHP_EOL;

                        $result['code'] .= "\t\t" . 'break;' . PHP_EOL . PHP_EOL;
                    }
                    $result['code'] .= '} ' . PHP_EOL . PHP_EOL;

                } else {

                    $opInfo = $opGroup[0];
                    foreach ($opInfo['request'] as $req) {
                        $result['code'] .= '$' . $req['req_key'] . ' = $retData[\'data\'][\'params\'][\'' . $req['req_key'] . '\'];' . PHP_EOL;
                    }

                    $result['code'] .= PHP_EOL . '// Do your work with the above variables' . PHP_EOL . PHP_EOL;

                    $result['code'] .= '$result = [' . PHP_EOL;
                    foreach ($opInfo['response'] as $req) {
                        $result['code'] .= "\t\t\t" . '\'' . $req['res_key'] . '\' => ${some_your_data},' . PHP_EOL;
                    }
                    $result['code'] .= '];' . PHP_EOL;
                    $result['code'] .= PHP_EOL;

                }

                $result['code'] .= '// Must return json' . PHP_EOL;
                $result['code'] .= 'return json_encode($result, JSON_UNESCAPED_UNICODE);';
                break;

            default :
                $result['type'] = 'json';
                $result['code'] = $snipet;
        }

        //exit($result['code']);

        $result['code'] = str_replace("\t", '    ', $result['code']);
        return $result;
    }


    /**
     * replace $uid
     *
     * @param $uid
     *
     * @return string|string[]|null
     */
    public static function replaceUid($uid = '')
    {
        if (empty($uid)) return null;

        $config = include APP_DIR . 'config/' . APP_ENV . '.php';
        $config = $config['settings'];

        $availableLang = $config['language']['availableLang'];
        $pattern = '/(' . implode('|' , $availableLang) . ')/';

        return preg_replace_callback($pattern, function ($matches) {
            return ucfirst(strtolower($matches[0]));
        }, $uid);
    }

    /**
     * Swap Request Code
     *
     * @param $code
     *
     * @return int|string
     */
    public static function getReqMethod($code)
    {

        switch ($code) {

            case CommonConst::REQ_METHOD_GET_STR :
                $reqMethod = CommonConst::REQ_METHOD_GET_CODE; break;
            case CommonConst::REQ_METHOD_GET_CLEANURL_STR :
                $reqMethod = CommonConst::REQ_METHOD_GET_CLEANURL_CODE; break;
            case CommonConst::REQ_METHOD_POST_STR :
                $reqMethod = CommonConst::REQ_METHOD_POST_CODE; break;
            case CommonConst::REQ_METHOD_PUT_STR :
                $reqMethod = CommonConst::REQ_METHOD_PUT_CODE; break;
            case CommonConst::REQ_METHOD_DEL_STR :
                $reqMethod = CommonConst::REQ_METHOD_DEL_CODE; break;

            case CommonConst::REQ_METHOD_GET_CODE :
                $reqMethod = CommonConst::REQ_METHOD_GET_STR; break;
            case CommonConst::REQ_METHOD_GET_CLEANURL_CODE :
                $reqMethod = CommonConst::REQ_METHOD_GET_CLEANURL_STR; break;
            case CommonConst::REQ_METHOD_POST_CODE :
                $reqMethod = CommonConst::REQ_METHOD_POST_STR; break;
            case CommonConst::REQ_METHOD_PUT_CODE :
                $reqMethod = CommonConst::REQ_METHOD_PUT_STR; break;
            case CommonConst::REQ_METHOD_DEL_CODE :
                $reqMethod = CommonConst::REQ_METHOD_DEL_STR; break;

            default :
                $reqMethod = null;
        }

        return $reqMethod;
    }

    /**
     * 요청 메소드 코드에 따른 풀 스트링 반환
     *
     * @param $code
     *
     * @return string|null
     */
    public static function getReqMethodString($code)
    {
        switch ($code) {
            case CommonConst::REQ_METHOD_GET_STR :
            case CommonConst::REQ_METHOD_GET_CLEANURL_STR :
                $reqMethod = CommonConst::REQ_METHOD_GET;
                break;
            case CommonConst::REQ_METHOD_POST_STR :
                $reqMethod = CommonConst::REQ_METHOD_POST;
                break;
            case CommonConst::REQ_METHOD_PUT_STR :
                $reqMethod = CommonConst::REQ_METHOD_PUT;
                break;
            case CommonConst::REQ_METHOD_DEL_STR :
                $reqMethod = CommonConst::REQ_METHOD_DEL;
                break;
            default :
                $reqMethod = null;
        }

        return $reqMethod;

    }


}