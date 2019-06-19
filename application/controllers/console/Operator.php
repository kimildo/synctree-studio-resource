<?php

namespace controllers\console;

//use SebastianBergmann\CodeCoverage\Report\Xml\Project;
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

use Ramsey\Uuid\Uuid;

class Operator extends SynctreeConsole
{
    public function __construct(ContainerInterface $ci)
    {
        parent::__construct($ci);
    }

    public function list(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $result = CommonUtil::getRedisData($this->ci,
                CommonConst::CLIENT_OPS_LIST_REDIS_KEY . $this->teamId . $this->accountId,
                'executeGetOpsList', [
                    'account_id'     => $this->accountId,
                    'team_id'        => $this->teamId,
                ]);

            $ops = [];
            foreach ($result[1] as $row) {
                $ops[$row['operation_id']] = [
                    'op_id'       => $row['operation_id'],
                    'op_name'     => $row['operation_name'] ?? '-',
                    'op_key'      => $row['operation_key'],
                    'op_desc'     => $row['operation_description'],
                    'regist_date' => $row['register_date'],
                    'modify_date' => $row['last_modify_date'],
                ];
            }

            // 오퍼레이터 ID로 역 정렬
            krsort($ops);
            foreach ($ops as $op) {
                $operators[] = $op;
            }

            $results['data'] = [
                CommonConst::OPERATOR_FILE_NAME => $operators ?? []
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
     * 오퍼레이터 추가 폼
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     * @deprecated
     *
     */
    public function add(Request $request, Response $response)
    {
        $this->viewData['page_title'] = 'Operation';

        if (empty($this->viewData['app_id'])) {
            $this->flash->addMessage('error', ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_EXIST_APP]);
            return $response->withRedirect('/' . $this->lang . '/console/apps/op');
        }

        try {
            $this->renderer->render($response, 'console/operator-add.twig', $this->viewData);
        } catch (\Exception $ex) {
            $this->_getErrorMessage($ex);
        }

        return $response;
    }

    /**
     * 오퍼레이터 추가 액션
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function addCallback(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');

            if (false === CommonUtil::validateParams($params, ['app_id', 'op_name', 'op_method', 'req_method', 'op_ns_name'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            $addOpParams = [
                'account_id'                => $this->accountId,
                'team_id'                   => $this->teamId,
                'header_transfer_type_code' => $params['header_transfer_type_code'] ?? CommonConst::HTTP_HEADER_CONTENTS_TYPE_FORM_DATA_CODE,
                'operation_namespace_name'  => $params['op_ns_name'],
                'operation_name'            => $params['op_name'],
                'operation_description'     => $params['op_desc'] ?? 'NONE',
                'auth_type_code'            => $params['auth_type_code'] ?? CommonConst::API_AUTH_NO_ATUH,
            ];

            $addOpParams['operation_namespace_name'] = strtoupper($addOpParams['operation_namespace_name']);
            $result = CommonUtil::callProcedure($this->ci, 'executeAddOps', $addOpParams);

            if (0 !== $result['returnCode']) {
                throw new \Exception('', $result['returnCode']);
            }

            $results['data'] = $result['data'][1][0];

            $operationIdx = $result['data'][1][0]['operation_id'];
            //$operationKey = $result['data'][1][0]['operation_key'];

            $opsParams = [
                'account_id'          => $this->accountId,
                'team_id'             => $this->teamId,
                'operation_id'        => $operationIdx,
                'protocol_type_code'  => $params['op_method'] ?? CommonConst::PROTOCOL_TYPE_SIMPLE_HTTP,
                'request_method_code' => (($params['req_method'] == 'G') ? CommonConst::REQ_METHOD_GET_CODE : CommonConst::REQ_METHOD_POST_CODE),
                'target_urls'         => $params['op_url'] ?? '',
                'target_method'       => $params['op_mehod'] ?? '',
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

                if (isset($params['req_sub_param_format'][$key]) && true === CommonUtil::isValidJSON($params['req_sub_param_format'][$key])) {
                    $opsParamsTrans[$opsParamsTransKey]['sub_parameter_format'] = json_decode($params['req_sub_param_format'][$key]);
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

                if (isset($params['res_sub_param_format'][$key]) && true === CommonUtil::isValidJSON($params['res_sub_param_format'][$key])) {
                    $opsParamsTrans[$opsParamsTransKey]['sub_parameter_format'] = json_decode($params['res_sub_param_format'][$key]);
                }

                $opsParamsTransKey++;
            }

            $opsParams['parameters'] = json_encode($opsParamsTrans, JSON_UNESCAPED_UNICODE);
            $result = CommonUtil::callProcedure($this->ci, 'executeSetOpsParams', $opsParams);
            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            RedisUtil::delData($this->redis, CommonConst::CLIENT_OPS_LIST_REDIS_KEY . $this->teamId . $this->accountId,
                CommonConst::REDIS_CLIENT_SESSION);

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
     * 오퍼레이터 삭제
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     * @throws \Exception
     */
    public function remove(Request $request, Response $response)
    {

        $results = $this->jsonResult;
        $redisDb = CommonConst::REDIS_CLIENT_SESSION;
        $delOps = [];

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');

            if (false === CommonUtil::validateParams($params, ['ops'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            if (is_array($params['ops'])) {
                foreach ($params['ops'] as $op) {

                    if ($op === 'false') continue;

                    $result = CommonUtil::callProcedure($this->ci, 'executeRemoveOpt', [
                        'account_id' => $this->accountId,
                        'team_id'    => $this->teamId,
                        'op_id'      => $op
                    ]);

                    if (0 !== $result['returnCode']) {
                        throw new \Exception($result['message'], $result["returnCode"]);
                    }

                    $delOps[] = $op;
                }
            } else {
                $result = CommonUtil::callProcedure($this->ci, 'executeRemoveOpt', [
                    'account_id' => $this->accountId,
                    'team_id'    => $this->teamId,
                    'op_id'      => $params['ops']
                ]);

                if (0 !== $result['returnCode']) {
                    throw new \Exception($result['message'], $result["returnCode"]);
                }

                $delOps[] = $params['ops'];
            }

            foreach ($delOps as $opId) {
                RedisUtil::delData($this->redis, CommonConst::CLIENT_OPS_REDIS_KEY . $opId, $redisDb);
            }

            RedisUtil::delData($this->redis, CommonConst::CLIENT_OPS_LIST_REDIS_KEY . $this->teamId . $this->accountId, $redisDb);

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
     * 오퍼레이터 수정 폼
     *
     * @param Request  $request
     * @param Response $response
     * @param          $args
     *
     * @return Response
     */
    public function modify(Request $request, Response $response, $args)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            if (false === CommonUtil::validateParams($args, ['op_id'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            if (false === ($opInfo = AppsUtil::getOperationInfo($this->ci, $this->accountId, $this->teamId, $args['op_id']))) {
                throw new \Exception('Error get Operation');
            }

            $results['data'] = $opInfo;

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
     * 오퍼레이터 수정 액션
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function modifyCallback(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            $params = $request->getAttribute('params');

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            if (false === CommonUtil::validateParams($params, ['op_id', 'op_name', 'op_ns_name'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }
            //if (false === ($opInfo = AppsUtil::getOperationInfo($this->ci, $this->accountId, $this->teamId, $params['op_id']))) {
            //    throw new \Exception('Error get Operation');
            //}

            $modifyOpt = [
                'account_id'                => $this->accountId,
                'team_id'                   => $this->teamId,
                'operation_id'              => $params['op_id'],
                'operation_name'            => $params['op_name'],
                'header_transfer_type_code' => $params['header_transfer_type_code'] ?? CommonConst::HTTP_HEADER_CONTENTS_TYPE_FORM_DATA_CODE,
                'operation_namespace_name'  => $params['op_ns_name'] ?? 'NONE',
                'operation_description'     => $params['op_desc'] ?? null,
                'auth_type_code'            => $params['auth_type_code'] ?? CommonConst::API_AUTH_NO_ATUH,
            ];

            $modifyOpt['operation_namespace_name'] = strtoupper($modifyOpt['operation_namespace_name']);
            $result = CommonUtil::callProcedure($this->ci, 'executeModifyOpt', $modifyOpt);

            if (0 !== $result['returnCode']) {
                throw new \Exception('', $result['returnCode']);
            }

            $opsParams = [
                'account_id'          => $this->accountId,
                'team_id'             => $this->teamId,
                'operation_id'        => $params['op_id'],
                'protocol_type_code'  => $params['op_method'] ?? CommonConst::PROTOCOL_TYPE_SIMPLE_HTTP,
                'request_method_code' => AppsUtil::getReqMethod($params['req_method']) ?? CommonConst::REQ_METHOD_GET_CODE,
                'target_urls'         => $params['op_url'] ?? null,
                'target_method'       => $params['op_target_method'] ?? null,
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
                    'parameter_description' => $params['req_desc'][$key]
                ];

                if (isset($params['req_param_id'][$key]) && !empty($params['req_param_id'][$key])) {
                    $opsParamsTrans[$opsParamsTransKey]['parameter_id'] = $params['req_param_id'][$key];
                }

                if (isset($params['req_sub_param_format'][$key]) && true === CommonUtil::isValidJSON($params['req_sub_param_format'][$key])) {
                    $opsParamsTrans[$opsParamsTransKey]['sub_parameter_format'] = json_decode($params['req_sub_param_format'][$key], true);
                }

                $opsParamsTransKey++;
            }

            foreach ($params['res_key'] as $key => $value) {
                $opsParamsTrans[$opsParamsTransKey] = [
                    'direction_code'        => CommonConst::DIRECTION_OUT_CODE,
                    'required_flag'         => $params['res_required_flag'][$key] ?? CommonConst::PARAMS_NO_REQUIRED_CODE,
                    'parameter_key_name'    => $value,
                    'parameter_type_code'   => CommonConst::VAR_TYPE_STR_TO_CODE[$params['res_var_type'][$key]],
                    'parameter_description' => $params['res_desc'][$key]
                ];

                if (isset($params['res_param_id'][$key]) && !empty($params['res_param_id'][$key])) {
                    $opsParamsTrans[$opsParamsTransKey]['parameter_id'] = $params['res_param_id'][$key];
                }

                if (isset($params['res_sub_param_format'][$key]) && true === CommonUtil::isValidJSON($params['res_sub_param_format'][$key])) {
                    $opsParamsTrans[$opsParamsTransKey]['sub_parameter_format'] = json_decode($params['res_sub_param_format'][$key], true);
                }

                $opsParamsTransKey++;
            }

            $opsParams['parameters'] = json_encode($opsParamsTrans, JSON_UNESCAPED_UNICODE);
            $result = CommonUtil::callProcedure($this->ci, 'executeSetOpsParams', $opsParams);
            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            $redisDb = CommonConst::REDIS_CLIENT_SESSION;
            RedisUtil::delData($this->redis, CommonConst::CLIENT_OPS_LIST_REDIS_KEY . $this->teamId . $this->accountId, $redisDb);
            RedisUtil::delData($this->redis, CommonConst::CLIENT_OPS_REDIS_KEY . $params['op_id'], $redisDb);

            // 해당 오퍼레이션을 참조하는 비즈옵스 목록
            $result = CommonUtil::callProcedure($this->ci, 'executeGetBizReferOperation', [
                'account_id'   => $this->accountId,
                'team_id'      => $this->teamId,
                'operation_id' => $params['op_id'],
            ]);

            if (0 === $result['returnCode'] && is_array($result['data'][1])) {
                $redisDb = CommonConst::REDIS_CLIENT_SESSION;
                foreach ($result['data'][1] as $row) {
                    $redisKey = CommonConst::CLIENT_RELAY_REDIS_KEY . $row['biz_ops_id'];
                    RedisUtil::delData($this->redis, $redisKey, $redisDb);
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
     * 오퍼레이터 목록 가지고 오기 Ajax
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function getOperators(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');

            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            // 해당 비즈의 연결된 오퍼레이터 조회
            $result = CommonUtil::getRedisData($this->ci, CommonConst::CLIENT_BIND_OPS_LIST_REDIS_KEY . $params['biz_id'], 'executeGetbindOptList', [
                'account_id'       => $this->accountId,
                'team_id'          => $this->teamId,
                'application_id'   => $params['app_id'],
                'biz_ops_id'       => $params['biz_id'],
            ]);

            $bizOps['operators'] = [];
            foreach ($result[0][1] as $operator) {
                $bizOps['operators'][$operator['operation_id']] = $operator['binding_seq'];
            }

            // 모든 오퍼레이터 조회
            $result = CommonUtil::callProcedure($this->ci, 'executeGetOpsList', [
                'account_id'            => $this->accountId,
                'team_id'               => $this->teamId
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception('', $result['returnCode']);
            }

            $tmp = [];
            foreach ($result['data'][1] as $row) {
                $tmp[$row['operation_name']] = $row;
            }
            ksort($tmp);

            $ops = [];
            foreach ($tmp as $key => $row) {
                $ops[] = [
                    'op_id'          => $row['operation_id'],
                    'op_name'        => $row['operation_name'],
                    'op_ns_name'     => $row['operation_namespace_name'] ?? '',
                    'op_key'         => $row['operation_key'],
                    'op_desc'        => $row['operation_description'],
                    'auth_type_code' => $row['auth_type_code'],
                    'regist_date'    => $row['register_date'],
                    'modify_date'    => $row['last_modify_date'],
                    'binding_seq'     => ((array_key_exists($row['operation_id'], $bizOps['operators'])) ? $bizOps['operators'][$row['operation_id']] : null),
                ];
            }

            $results['data'] = [
                'op' => $ops ?? []
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
     * 요청한 오퍼레이터 목록 가지고 오기 Ajax(for relay)
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function getOperatorsByIdxs(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            $params = $request->getAttribute('params');

            if (false === CommonUtil::validateParams($params, ['op_idxs'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            foreach ($params['op_idxs'] as $opId) {
                if (false === ($op = AppsUtil::getOperationInfo($this->ci, $this->accountId, $this->teamId, $opId))) {
                    throw new \Exception('Error get Operation');
                }

                $results['data']['op'][$opId] = $op ?? [];
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
     * 오퍼레이터 정보 가지고 온다. 1개
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function getOperator(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');

            if (false === CommonUtil::validateParams($params, ['op_id'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            if (false === ($op = AppsUtil::getOperationInfo($this->ci, $this->accountId, $this->teamId, $params['op_id']))) {
                throw new \Exception('Error get Operation');
            }

            $results['data']['op'] = $op ?? [];


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
     * 비즈 옵스에 오퍼레이션을 연결/해제 합니다.
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function setOperatorBind(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');

            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            // 해당 비즈의 연결된 오퍼레이터 조회
            $result = CommonUtil::callProcedure($this->ci, 'executeGetbindOptList', [
                'account_id'       => $this->accountId,
                'team_id'          => $this->teamId,
                'application_id'   => $params['app_id'],
                'biz_ops_id'       => $params['biz_id'],
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception('', $result['returnCode']);
            }

            $bizOps['operators'] = [];
            foreach ($result['data'][0][1] as $row) {
                $bizOps['operators'][$row['operation_id']] = $row;
            }

            if (isset($params['bind']) && !empty($params['bind'] && is_array($params['bind']))) {
                foreach ($params['bind'] as $bindOp) {
                    $opId = $bindOp['op_id'];
                    if (false === (array_key_exists($opId, $bizOps['operators']))) {
                        throw new \Exception('Error get Operation');
                    }

                    if (false === ($op = AppsUtil::getOperationInfo($this->ci, $this->accountId, $this->teamId, $opId))) {
                        throw new \Exception('Error get Operation');
                    }

                    $op['biz_ops_seq'] = $bizOps['operators'][$opId]['binding_seq'];
                    $results['data']['op'][] = $op;
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
     * 비즈에 오퍼레이터를 연결
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return mixed|Response
     */
    public function bindOperation(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');
            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id', 'bind'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            if (isset($params['bind']) && !empty($params['bind'] && is_array($params['bind']))) {
                foreach ($params['bind'] as $key => $op) {

                    $authKeys = [];
                    $bindOption = [
                        'account_id'             => $this->accountId,
                        'team_id'                => $this->teamId,
                        'application_id'         => $params['app_id'],
                        'biz_ops_id'             => $params['biz_id'],
                        'binding_seq'            => $op['binding_seq'],
                        'operation_id'           => $op['op_id'],
                        'control_container_code' => $op['control_container_code'] ?? CommonConst::CONTAINER_TYPE_NONE,
                        'control_container_info' => $op['control_container_info'] ?? null,
//                        'auth_keys'              => (isset($params['auth_keys']) && !empty($params['auth_keys']))
//                            ? json_encode($params['auth_keys'], JSON_UNESCAPED_UNICODE)
//                            : null,
                    ];

                    if (isset($params['env']) && !empty($params['env'])) {
                        foreach ($params['env'] as $k => $env) {
                            $tmp['env'] = $env;
                            if (isset($params['token'])) {
                                $tmp['token'] = $params['token'][0];
                            }
                            if (isset($params['username'])) {
                                $tmp['username'] = $params['username'][0];
                            }
                            if (isset($params['password'])) {
                                $tmp['password'] = $params['password'][0];
                            }
                            $authKeys[] = $tmp;
                        }
                    }

                    $bindOption['auth_keys'] = json_encode($authKeys, JSON_UNESCAPED_UNICODE);
                    $result = CommonUtil::callProcedure($this->ci, 'executeBindOpt', $bindOption);

                    if (0 !== $result['returnCode']) {
                        throw new \Exception('', $result['returnCode']);
                    }
                }
            }

            $this->_delRedisForBiz($params);

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
     * 해당 비즈에서 오퍼레이터 연결 해제
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return mixed|Response
     */
    public function unbindOperation(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');

            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id', 'bind'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            if (isset($params['bind']) && !empty($params['bind'] && is_array($params['bind']))) {

                $unbindOps = [];
                $dbOption = [
                    'account_id'       => $this->accountId,
                    'team_id'          => $this->teamId,
                    'application_id'   => $params['app_id'],
                    'biz_ops_id'       => $params['biz_id'],
                ];

                foreach ($params['bind'] as $op) {

                    if (false === CommonUtil::validateParams($op, ['op_id', 'binding_seq'])) {
                        throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                    }

                    $dbOption['binding_seq'] = $op['binding_seq'];
                    $dbOption['operation_id'] = $op['op_id'];

                    $unbindOps[] = [
                        'operation_id' => $op['op_id'],
                        'binding_seq'  => $op['binding_seq'],
                    ];
                }

                if (empty($unbindOps)) {
                    throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                }

                $dbOption['operations'] = json_encode($unbindOps);
                $result = CommonUtil::callProcedure($this->ci, 'executeUnbindOpts', $dbOption);
                if (0 !== $result['returnCode']) {
                    throw new \Exception($result['message'], $result["returnCode"]);
                }

            }

            $this->_delRedisForBiz($params);


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
     * 오퍼레이터 테스트
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testCallback(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            $params = $request->getAttribute('params');

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            if (false === CommonUtil::validateParams($params, ['op_id'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            if (false === ($op = AppsUtil::getOperationInfo($this->ci, $this->accountId, $this->teamId, $params['op_id']))) {
                throw new \Exception('Error get Operation');
            }

            $options = [
                'verify' => false,
                'timeout' => 10,
                'allow_redirects' => true,
            ];

            $cleanUrl = false;
            switch ($op['req_method']) {
                case CommonConst::REQ_METHOD_PUT_STR :
                    $reqMethod = CommonConst::REQ_METHOD_PUT;
                    $opReqTypeVar = 'body';
                    break;
                case CommonConst::REQ_METHOD_DEL_STR :
                case CommonConst::REQ_METHOD_POST_STR :
                    $reqMethod = CommonConst::REQ_METHOD_POST;
                    $opReqTypeVar = 'form_params';
                    break;
                case CommonConst::REQ_METHOD_GET_CLEANURL_STR :
                    $cleanUrl = true; // no break;
                default :
                    $reqMethod = CommonConst::REQ_METHOD_GET;
                    $opReqTypeVar = 'query';
            }

            $endPoint = $op['target_url'];
            $secureData = [];

            foreach ($op['request'] as $req) {
                if (!array_key_exists($req['req_key'], $params)) {
                    throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                }

                if (!empty($cleanUrl)) {
                    $endPoint .= '/' . $params[$req['req_key']];
                } else {
                    $options[$opReqTypeVar][$req['req_key']] = $params[$req['req_key']];
                }

                if ($op['method'] == CommonConst::PROTOCOL_TYPE_SECURE ) {
                    $secureData[$req['req_key']] = $params[$req['req_key']];
                }
            }

            if ($op['method'] == CommonConst::PROTOCOL_TYPE_SECURE ) {
                $data = ['params' => $secureData, 'op_code' => (int)$op['op_id']];
                $uuid = Uuid::uuid5(Uuid::NAMESPACE_DNS, session_id() . time());
                $eventKey = strtoupper('event-' . $uuid->toString());
                RedisUtil::setDataWithExpire($this->redis, CommonConst::REDIS_SECURE_PROTOCOL_COMMAND, $eventKey, CommonConst::REDIS_SESSION_EXPIRE_TIME_MIN_5, $data);
                $options[$opReqTypeVar] = ['event_key' => $eventKey];
            }

            $httpClient = new \GuzzleHttp\Client();
            $resData = null;
            $resDataType = null;
            $resStatus = null;

            try {

                $ret = $httpClient->request($reqMethod, $endPoint, $options);
                $resData = $originResponse = $ret->getBody()->getContents();

                $resDataLength = mb_strlen($resData, CommonConst::DEFAULT_CHAR_SET);
                if ($resDataLength > 1024) {
                    $resData = mb_substr($resData, 0, 1024, CommonConst::DEFAULT_CHAR_SET) . '....';
                }

                //$resData = json_decode($resData, true);
                $resDataType = 'JSON';

                $prevResData[] = [
                    'res_type' => $resDataType,
                    'res_data' => $resData
                ];

                $resStatus = $ret->getStatusCode() . ' ' . $ret->getReasonPhrase();

            } catch (\GuzzleHttp\Exception\ServerException $e) {
                LogMessage::error('guzzle server error :: ' . $e->getMessage());
                preg_match('/(5[0-9]{2}[a-z\s]+)/i', $e->getMessage(), $output);
                $resStatus = $output[1] ?? null;
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                LogMessage::error('guzzle client error :: ' . $e->getMessage());
                preg_match('/(4[0-9]{2}[a-z\s]+)/i', $e->getMessage(), $output);
                $resStatus = $output[1] ?? null;
            } catch (\Exception $e) {
                $resStatus = "Name or service not known";
                LogMessage::error('guzzle unknown error :: ' . $e->getMessage());
            }

            $results['data'] = [
                'server_status'      => $resStatus,
                'request_target_url' => $endPoint,
                'request_method'     => $reqMethod,
                'request'            => $options[$opReqTypeVar] ?? null,
                'results'            => $resData,
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



}