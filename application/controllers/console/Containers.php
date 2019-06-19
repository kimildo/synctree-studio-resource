<?php

/**
 * 오퍼레이터 컨트롤 alt, loop, async 등을 위한 컨트롤러
 *
 * @author kimildo
 */

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

class Containers extends SynctreeConsole
{
    public function __construct(ContainerInterface $ci)
    {
        parent::__construct($ci);
    }

    /**
     * 연산자 목록
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function getControllOperators(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $results['data'] = CommonConst::CONTROLL_OPERATORS;

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
     * alternative 컨트롤을 추가.
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function bindAlter(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');
            //LogMessage::debug('bunit modify params :: ' . json_encode($params, JSON_UNESCAPED_UNICODE));

            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id', 'binding_seq', 'parameter_id', 'bind'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            if (is_array($params['bind'])) {

                // 컨테이너 먼저 등록
                $result = CommonUtil::callProcedure($this->ci, 'executeAddContainerAlt', [
                    'account_id'         => $this->accountId,
                    'team_id'            => $this->teamId,
                    'application_id'     => $params['app_id'],
                    'biz_ops_id'         => $params['biz_id'],
                    'binding_seq'        => $params['binding_seq'],
                    'parameter_id'       => $params['parameter_id'],
                    'sub_parameter_path' => $params['sub_parameter_path'] ?? null,
                    'alt_description'    => $params['alt_description'] ?? '',
                ]);

                if (0 !== $result['returnCode']) {
                    throw new \Exception($result['message'], $result["returnCode"]);
                }

                $altId = $result['data'][1][0]['control_alt_id'];

                foreach ($params['bind'] as $key => $op) {

                    $controlContainerInfo = json_encode([
                        'control_id' => (int)$altId,
                        'operator'   => (int)$op['control_operator'],
                        'value'      => $op['control_value'],
                    ], JSON_UNESCAPED_UNICODE);

                    $option = [
                        'account_id'             => $this->accountId,
                        'team_id'                => $this->teamId,
                        'application_id'         => $params['app_id'],
                        'biz_ops_id'             => $params['biz_id'],
                        'binding_seq'            => $op['binding_seq'],
                        'operation_id'           => $op['op_id'],
                        'control_container_code' => CommonConst::CONTAINER_TYPE_ALT,
                        'control_container_info' => $controlContainerInfo ?? null,
                        'auth_keys'              => (!empty($params['auth_keys'])) ? json_encode($params['auth_keys'], JSON_UNESCAPED_UNICODE) : null,
                    ];

                    $result = CommonUtil::callProcedure($this->ci, 'executeBindOpt', $option);

                    if (0 !== $result['returnCode']) {
                        throw new \Exception($result['message'], $result["returnCode"]);
                    }
                }

                $results['data']['control_alt_id'] = $altId;

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
     * alternative 컨트롤을 삭제
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function unbindAlter(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');
            //LogMessage::debug('bunit modify params :: ' . json_encode($params, JSON_UNESCAPED_UNICODE));

            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id', 'bind', 'control_alt_id'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            if (is_array($params['bind'])) {

                // 삭제에는 오퍼레이터를 먼저 언바인드
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

                // 언바인드된 컨테이너 삭제
                $result = CommonUtil::callProcedure($this->ci, 'executeDelContainerAlt', [
                    'account_id'      => $this->accountId,
                    'team_id'         => $this->teamId,
                    'application_id'  => $params['app_id'],
                    'biz_ops_id'      => $params['biz_id'],
                    'control_alt_id'  => $params['control_alt_id'],
                ]);

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
     * alternative 컨트롤 속성을 수정
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function modifyAlterCallback(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');
            //LogMessage::debug('bunit modify params :: ' . json_encode($params, JSON_UNESCAPED_UNICODE));

            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id', 'control_alt_id', 'bind'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            // 컨테이너 먼저 수정
            $result = CommonUtil::callProcedure($this->ci, 'executeModifyContainerAlt', [
                'account_id'         => $this->accountId,
                'team_id'            => $this->teamId,
                'application_id'     => $params['app_id'],
                'biz_ops_id'         => $params['biz_id'],
                'control_alt_id'     => $params['control_alt_id'],
                'binding_seq'        => $params['binding_seq'] ?? null,
                'parameter_id'       => $params['parameter_id'] ?? null,
                'sub_parameter_path' => $params['sub_parameter_path'] ?? null,
                'alt_description'    => $params['alt_description'] ?? null,
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            // 연결된 오퍼레이션/컨트롤 목록
            $result = CommonUtil::getRedisData($this->ci, CommonConst::CLIENT_BIND_OPS_LIST_REDIS_KEY . $params['biz_id'], 'executeGetbindOptList', [
                'account_id'     => $this->accountId,
                'team_id'        => $this->teamId,
                'application_id' => $params['app_id'],
                'biz_ops_id'     => $params['biz_id']
            ]);

            $ops = $result[0][1] ?? [];

            // 컨트롤 정보가 있는 오퍼레이터를 언바인드
            if (!empty($ops)) {
                $unbindOps = [];
                $unbindOption = [
                    'account_id'       => (int)$this->accountId,
                    'team_id'          => (int)$this->teamId,
                    'application_id'   => (int)$params['app_id'],
                    'biz_ops_id'       => (int)$params['biz_id'],
                ];

                foreach ($ops as $key => $op) {
                    if (empty($op['control_container_info'])) {
                        continue;
                    }

                    $controlContainerInfo = CommonUtil::getValidJSON($op['control_container_info']);
                    if ($controlContainerInfo['control_id'] == $params['control_alt_id']) {
                        $unbindOps[] = [
                            'operation_id' => (int)$op['operation_id'],
                            'binding_seq'  => (int)$op['binding_seq'],
                        ];
                    }
                }

                if (!empty($unbindOps)) {
                    $unbindOption['operations'] = json_encode($unbindOps);
                    $result = CommonUtil::callProcedure($this->ci, 'executeUnbindOpts', $unbindOption);
                    if (0 !== $result['returnCode']) {
                        throw new \Exception($result['message'], $result["returnCode"]);
                    }
                }
            }


            // 넘겨온 오퍼레이터 바인드
            foreach ($params['bind'] as $key => $op) {

                $controlContainerInfo = json_encode([
                    'control_id' => (int)$params['control_alt_id'],
                    'operator'   => (int)$op['control_operator'],
                    'value'      => $op['control_value'],
                ], JSON_UNESCAPED_UNICODE);

                $option = [
                    'account_id'             => $this->accountId,
                    'team_id'                => $this->teamId,
                    'application_id'         => $params['app_id'],
                    'biz_ops_id'             => $params['biz_id'],
                    'binding_seq'            => $op['binding_seq'],
                    'operation_id'           => $op['op_id'],
                    'control_container_code' => CommonConst::CONTAINER_TYPE_ALT,
                    'control_container_info' => $controlContainerInfo ?? null,
                    'auth_keys'              => (!empty($params['auth_keys'])) ? json_encode($params['auth_keys'], JSON_UNESCAPED_UNICODE) : null,
                ];

                $result = CommonUtil::callProcedure($this->ci, 'executeBindOpt', $option);

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
     * Async. 처리할 오퍼레이션 범위를 설정
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return mixed|Response
     */
    public function setAsyncRange(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');

            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id', 'first_binding_seq', 'last_binding_seq'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            $result = CommonUtil::callProcedure($this->ci, 'executeSetAsyncRange', [
                'account_id'        => $this->accountId,
                'team_id'           => $this->teamId,
                'application_id'    => $params['app_id'],
                'biz_ops_id'        => $params['biz_id'],
                'first_binding_seq' => $params['first_binding_seq'],
                'last_binding_seq'  => $params['last_binding_seq'],
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
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
     * Async. 처리할 오퍼레이션 범위를 삭제
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return mixed|Response
     */
    public function unsetAsyncRange(Request $request, Response $response)
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

            $result = CommonUtil::callProcedure($this->ci, 'executeUnsetAsyncRange', [
                'account_id'        => $this->accountId,
                'team_id'           => $this->teamId,
                'application_id'    => $params['app_id'],
                'biz_ops_id'        => $params['biz_id']
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
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


}