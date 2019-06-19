<?php

namespace controllers\console;

use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Container\ContainerInterface;

use libraries\{
    log\LogMessage,
    constant\CommonConst,
    constant\ErrorConst,
    util\CommonUtil,
    util\RedisUtil,
    util\AppsUtil
};

class Partners extends SynctreeConsole
{
    public function __construct(ContainerInterface $ci)
    {
        parent::__construct($ci);
    }

    public function temp(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }


        } catch (\Exception $ex) {
            $results = [
                'result' => ErrorConst::FAIL_STRING,
                'data'   => [
                    'message' => $this->_getErrorMessage($ex),
                ]
            ];
        }

        return $response->withJson($results, ErrorConst::SUCCESS_CODE);

    }


    /**
     * 파트너 로그인 시 필요한 정보 반환
     *
     * @param Request  $request
     * @param Response $response
     * @param          $args
     *
     * @return Response
     */
    public function signup(Request $request, Response $response, $args)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            if (false === CommonUtil::validateParams($args, ['key'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            $key = $args['key'];
            $result = CommonUtil::callProcedure($this->ci, 'executeGetPartnerInfo', ['partner_direct_access_key' => $key]);
            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            $partnerInfo = $result['data'][1][0];

            if (false === ($redisData = $this->_getDatas($key))) {
                throw new \Exception(null, ErrorConst::ERROR_SESSION_EXPIRED_PARTNER);
            }

            list($data, $appName, $clientName, $partnerId, $partnerName) = $redisData;
            //CommonUtil::showArrDump($redisData);

            $results['data']['csrf'] = $this->_addCsrfToken();
            $results['data']['partner'] = [
                'access_key'          => $key,
                'client_name'         => $clientName,
                'operator'            => $partnerName,
                'partner_id'          => $partnerId,
                'account_status_code' => $partnerInfo['account_status_code'],
                'biz_name'            => $appName,
            ];


        } catch (\Exception $ex) {
            $results = [
                'result' => ErrorConst::FAIL_STRING,
                'data'   => [
                    'message' => $this->_getErrorMessage($ex),
                ]
            ];
        }

        return $response->withJson($results, ErrorConst::SUCCESS_CODE);
    }

    /**
     * 비밀번호 세팅 콜백
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     * @throws \Exception
     */
    public function passwordSetCallback(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');

            if (false === CommonUtil::validateParams($params, ['password', 'password-re'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            $csrf = [
                'csrf_name'  => $params['csrf_name'],
                'csrf_value' => $params['csrf_value'],
            ];

            if (false === ($csrfCheck = $this->_checkCsrf($csrf))) {
                throw new \Exception(null, ErrorConst::ERROR_CSRF_FAIL);
            }

            if ($params['password'] !== $params['password-re']) {
                LogMessage::error('Password incorrect');
                throw new \Exception(null, ErrorConst::ERROR_NOT_EQUAL_PASSWORD);
            }

            $data = RedisUtil::getData($this->redis, $params['access_key'], CommonConst::REDIS_PARTNERS_SESSION);
            $result = CommonUtil::callProcedure($this->ci, 'executeSetPartnerInfo', [
                'partner_direct_access_key' => $params['access_key'],
                'account_email'             => $data['partner_account_email'],
                'passphrase'                => $params['password'],
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            $results['data'] = [
                'partner_direct_access_key' => $params['access_key'],
                'account_email'             => $data['partner_account_email'],
            ];


        } catch (\Exception $ex) {
            $results = [
                'result' => ErrorConst::FAIL_STRING,
                'data'   => [
                    'message' => $this->_getErrorMessage($ex),
                ]
            ];
        }

        return $response->withJson($results, ErrorConst::SUCCESS_CODE);

    }



    /**
     * 파트너사 오퍼레이션 저장
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function operatorModifyCallback(Request $request, Response $response)
    {

        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            if (!isset($_SESSION['partner']['data']) || empty($_SESSION['partner']['data'])) {
                throw new \Exception(null, ErrorConst::ERROR_SESSION_EXPIRED_PARTNER);
            }

            $params = $request->getAttribute('params');
            $data = $_SESSION['partner']['data'];

            if (false === CommonUtil::validateParams($params, ['app_id'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            $appId      = $data['app_id'];
            $bizId      = $data['biz_id'];
            $opId       = $data['op_id'];
            $account_id = $data['account_id'];
            $team_id    = $data['team_id'];

            $opsParams = [
                'account_id'          => $account_id,
                'team_id'             => $team_id,
                'operation_id'        => $opId,
                'protocol_type_code'  => $params['op_method'] ?? CommonConst::PROTOCOL_TYPE_SIMPLE_HTTP,
                'request_method_code' => AppsUtil::getReqMethod($params['req_method']) ?? CommonConst::REQ_METHOD_GET_CODE,
                'target_urls'         => $params['op_url'] ?? '',
            ];

            if (!empty($opsParams['target_urls'])) {
                $opsParams['target_urls'] = json_encode([[
                    'environment_code' => CommonConst::ENVIRONMENT_CODE_PRD,
                    'target_url' => $opsParams['target_urls'] . ((!empty($opsParams['target_method'])) ? '/' . $opsParams['target_method'] : ''),
                ]]);
            }

            $opsParamsTrans = [];
            $opsParamsTransKey = 0;

            foreach ($params['req_key'] as $key => $value) {
                $opsParamsTrans[$opsParamsTransKey] = [
                    'direction_code'        => CommonConst::DIRECTION_IN_CODE,
                    'required_flag'         => $params['req_required_flag'][$key] ?? CommonConst::PARAMS_NO_REQUIRED_CODE,
                    'parameter_key_name'    => $value,
                    'parameter_type_code'   => CommonConst::VAR_TYPE_STR_TO_CODE[$params['req_var_type'][$key]],
                    'parameter_description' => $params['req_desc'][$key],
                ];

                if (isset($params['req_param_id'][$key]) && !empty($params['req_param_id'][$key])) {
                    $opsParamsTrans[$opsParamsTransKey]['parameter_id'] = $params['req_param_id'][$key];
                }

                $opsParamsTransKey++;
            }

            foreach ($params['res_key'] as $key => $value) {
                $opsParamsTrans[$opsParamsTransKey] = [
                    'direction_code'        => CommonConst::DIRECTION_OUT_CODE,
                    'required_flag'         => $params['res_required_flag'][$key] ?? CommonConst::PARAMS_NO_REQUIRED_CODE,
                    'parameter_key_name'    => $value,
                    'parameter_type_code'   => CommonConst::VAR_TYPE_STR_TO_CODE[$params['res_var_type'][$key]],
                    'parameter_description' => $params['res_desc'][$key],
                ];

                if (isset($params['res_param_id'][$key]) && !empty($params['res_param_id'][$key])) {
                    $opsParamsTrans[$opsParamsTransKey]['parameter_id'] = $params['res_param_id'][$key];
                }

                $opsParamsTransKey++;
            }

            $opsParams['parameters'] = json_encode($opsParamsTrans, JSON_UNESCAPED_UNICODE);
            $result = CommonUtil::callProcedure($this->ci, 'executeSetOpsParams', $opsParams);
            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            RedisUtil::getDataWithDel($this->redis, CommonConst::CLIENT_OPS_REDIS_KEY . $opId,CommonConst::REDIS_CLIENT_SESSION);


            // 알림 데이터 셋(redis)
            $key = \models\redis\RedisKeys::getAlarmKey($data['path']);
            $beforeData = RedisUtil::getData($this->redis, $key);
            if ( ! $beforeData) {
                $d = [[
                          'app_id' => $appId,
                          'biz_id' => $bizId,
                          'op_id'  => $opId
                      ]];
            } else {
                $d = $beforeData;
                $cnt = count($beforeData);
                $existIdx = null;

                for ($i = 0; $i < $cnt; $i++) {
                    if ($beforeData[$i]['app_id'] === $appId && $beforeData[$i]['biz_id'] === $bizId && $beforeData[$i]['op_id'] === $opId) {
                        $existIdx = $i;
                    }
                }

                if ($existIdx === null) {
                    $d[] = [
                        'app_id' => $appId,
                        'biz_id' => $bizId,
                        'op_id'  => $opId
                    ];
                }
            }

            RedisUtil::setDataWithExpire($this->redis, CommonConst::REDIS_MESSAGE_SESSION, $key, CommonConst::REDIS_SESSION_EXPIRE_TIME_DAY_1, $d);

            // 해당 오퍼레이션을 참조하는 비즈옵스 목록
            $result = CommonUtil::callProcedure($this->ci, 'executeGetBizReferOperation', [
                'account_id'            => $account_id,
                'team_id'               => $team_id,
                'operation_id'          => $opId,
            ]);

            // 해당 오퍼레이션을 참조하는 비즈옵스 레디스 삭제
            if (0 === $result['returnCode'] && is_array($result['data'][1])) {
                foreach ($result['data'][1] as $row) {
                    RedisUtil::delData($this->redis, CommonConst::CLIENT_RELAY_REDIS_KEY . $row['biz_ops_id'], CommonConst::REDIS_CLIENT_SESSION);
                }
            }


        } catch (\Exception $ex) {

            $results = [
                'result' => ErrorConst::FAIL_STRING,
                'data'   => [
                    'message' => $this->_getErrorMessage($ex),
                ]
            ];

        }

        return $response->withJson($results, ErrorConst::SUCCESS_CODE);

    }



    /**
     * @param $key
     *
     * @return array|bool
     * @throws \Exception
     */
    private function _getDatas($key)
    {
        if (false === ($tempData = RedisUtil::getData($this->redis, $key, CommonConst::REDIS_PARTNERS_SESSION))) {
            return false;
        }

//        $result = CommonUtil::getRedisData($this->ci, CommonConst::CLIENT_APP_LIST_REDIS_KEY . $team_id . $acctId, 'executeGetAppList', [
//            'account_id'   => $acctId,
//            'team_id'      => $team_id,
//            'archive_flag' => 0,
//        ]);
//
//        $apps = $result[1] ?? [];
//
//        if (false === ($appIdx = array_search($tempData['app_id'], array_column($apps, 'application_id')))) {
//            return false;
//        }
//
//        $appName = $apps[$appIdx]['application_name'];
//
//        $result = CommonUtil::callProcedure($this->ci, 'executeGetbindOptList', [
//            'account_id'     => $acctId,
//            'team_id'        => $team_id,
//            'application_id' => $tempData['app_id'],
//            'biz_ops_id'     => $tempData['biz_id']
//        ]);
//
//        if (0 !== $result['returnCode']) {
//            return false;
//        }
//
//        $ops = $result['data'][1];

        $partnerId = $tempData['partner_account_email'];
        //$acctId = $tempData['account_id'];
        //$team_id = $tempData['team_id'];

        $redisKey = CommonConst::CLIENT_BIZ_REDIS_KEY . $tempData['biz_id'];
        $redisDb = CommonConst::REDIS_CLIENT_SESSION;
        if (false === ($bizOpsInfo = RedisUtil::getData($this->redis, $redisKey, $redisDb))) {
            return false;
        }

        if (false === (array_key_exists($tempData['op_id'], $bizOpsInfo['operators']))) {
            return false;
        }

        $appName = $bizOpsInfo['biz_name'];
        $clientName = $bizOpsInfo['actor_alias'];
        $partnerName = $bizOpsInfo['operators'][$tempData['op_id']]['op_text'];

        return [
            $tempData,
            $appName,
            $clientName,
            $partnerId,
            $partnerName
        ];
    }


}
