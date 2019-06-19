<?php
/**
 * 비즈 유닛 컨트롤러
 * @author kimildo
 *
 */

namespace controllers\console;

use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Container\ContainerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

use libraries\{constant\GeneratorConst,
    log\LogMessage,
    constant\CommonConst,
    constant\ErrorConst,
    util\AppsUtil,
    util\CommonUtil,
    util\RedisUtil,
    util\GenerateUtil};

class BizUnit extends SynctreeConsole
{
    public function __construct(ContainerInterface $ci)
    {
        parent::__construct($ci);
    }

    /**
     * BizOps List
     *
     * @param Request  $request
     * @param Response $response
     * @param          $args
     *
     * @return Response
     */
    public function list(Request $request, Response $response, $args)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $appId = $args['app_id'];
            $bizOpsRedisKey = CommonConst::CLIENT_BIZ_LIST_REDIS_KEY . $this->teamId . $this->accountId . $appId;
            $bizOpsRedisDb = CommonConst::REDIS_CLIENT_SESSION;

            if (false !== ($bizList = RedisUtil::getData($this->redis, $bizOpsRedisKey, $bizOpsRedisDb))) {
                return $response->withJson(['data' => [
                        'biz_ops' => $bizList['biz_ops'],
                        //'partners' => $bizList['partners']
                    ]
                ], ErrorConst::SUCCESS_CODE);
            }

            $result = CommonUtil::callProcedure($this->ci,'executeGetBizList', [
                'account_id'     => $this->accountId,
                'team_id'        => $this->teamId,
                'application_id' => $appId,
            ]);

            $bizOps = [];
            $bizOpsInfo = $result['data'][1] ?? [];
            if (!empty($bizOpsInfo)) {

                if (false === ($isUpdates = $this->_checkUpdateApps())) {
                    $isUpdates = [];
                }

                foreach ($bizOpsInfo as $key => $b) {

                    $bizOps[$key] = [
                        'biz_id'              => $b['biz_ops_id'],
                        'app_id'              => $appId,
                        'biz_name'            => $b['biz_ops_name'],
                        'biz_desc'            => $b['biz_ops_description'],
                        'biz_uid'             => AppsUtil::replaceUid($b['biz_ops_key']),
                        'reg_date'            => $b['register_date'],
                        'request_method_code' => $b['request_method_code'] ?? null,
                        'operators'           => [],
                        'request'             => [],
                    ];

                    if ( ! empty($b['request_method_code'])) {

                        $result = CommonUtil::callProcedure($this->ci, 'executeGetBizParams', [
                            'account_id'     => $this->accountId,
                            'team_id'        => $this->teamId,
                            'application_id' => $appId,
                            'biz_ops_id'     => $b['biz_ops_id']
                        ]);

                        if (0 !== $result['returnCode']) {
                            throw new \Exception($result['message'], $result["returnCode"]);
                        }

                        $bizReqs = $result['data'][1];
                        $bQuery = [];
                        if ( ! empty($bizReqs)) {
                            foreach ($bizReqs as $req) {
                                $bizOps[$key]['request'][] = [
                                    'param_id'     => $req['parameter_id'],
                                    'req_key'      => $req['parameter_key_name'],
                                    'req_var_type' => CommonConst::VAR_TYPE_CODE_TO_STR[$req['parameter_type_code']],
                                    'req_desc'     => $req['parameter_description'],
                                ];
                            }

                            foreach ($bizOps[$key]['request'] as $rKey => $row) {
                                $bizOps[$key]['request'][$rKey] = array_map('trim', $row);
                                if ($b['request_method_code'] === CommonConst::REQ_METHOD_GET_CODE) {
                                    $bQuery[$row['req_key']] = '{' . $row['req_key'] . '}';
                                }
                            }
                        }

                        $endPoint = GeneratorConst::GEN_ROUTE_PREFIX . AppsUtil::replaceUid($b['biz_ops_key']);
                        //$bizOps[$key]['end_point'] = $endPoint;
                        $bizOps[$key]['end_point'] = urldecode(CommonUtil::makeUrl($endPoint, $bQuery));
                    }

                    //@todo 배포진행상태 고민 해야 함
                    //$redisKey = CommonConst::DEPLOYMENT_REDIS_KEY . $this->userPath . '_' . $appId . $b['biz_ops_id'];
                    //$bizOps[$key]['is_deploying'] = (!empty(RedisUtil::getData($this->redis, $redisKey, CommonConst::REDIS_DEPLOY_SESSION)));
                    $bizOps[$key]['is_deploying'] = false;

                    if (false !== ($updateAppIndex = array_search($b['biz_ops_id'], array_column($isUpdates, 'biz_id')))) {
                        $bizOps[$key]['is_new'] = true;
                    }

                    // 비즈옵스 빌드 리스트 (빌드 === 저장)
                    $result = CommonUtil::callProcedure($this->ci, 'executeGetBizBuildList', [
                        'account_id'         => $this->accountId,
                        'team_id'            => $this->teamId,
                        'application_id'     => $appId,
                        'biz_ops_id'         => $b['biz_ops_id'],
                    ]);

                    if (0 !== $result['returnCode']) {
                        throw new \Exception($result['message'], $result["returnCode"]);
                    }

                    $bizOps[$key]['last_build'] = array_pop($result['data'][1]);
                    $bizOps[$key]['has_build_history'] = (!empty($result['data'][1]));


                    // 배포 이력 조회
                    $result = CommonUtil::callProcedure($this->ci, 'executeGetBizDeployList', [
                        'account_id'     => $this->accountId,
                        'team_id'        => $this->teamId,
                        'application_id'     => $appId,
                        'biz_ops_id'         => $b['biz_ops_id'],
                        'environment_code' => CommonConst::DEPLOY_TARGET_PRD
                    ]);

                    if (0 !== $result['returnCode']) {
                        throw new \Exception($result['message'], $result["returnCode"]);
                    }

                    $bizOps[$key]['has_deploy_history'] = (!empty($result['data'][1]));

                    // 연결된 오퍼레이션 조회
                    $result = CommonUtil::callProcedure($this->ci, 'executeGetbindOptList', [
                        'account_id'       => $this->accountId,
                        'team_id'          => $this->teamId,
                        'application_id'   => $appId,
                        'biz_ops_id'       => $b['biz_ops_id'],
                    ]);

                    if (0 !== $result['returnCode']) {
                        throw new \Exception('', $result['returnCode']);
                    }

                    //$bizOps[$key]['operators']['count'] = count($result['data'][1]);
                    foreach ($result['data'][0][1] as $operator) {
                        $bizOps[$key]['operators'][] = [
                            'op_id'           => $operator['operation_id'],
                            'target_line_seq' => $operator['binding_seq'],
                            'op_info'         => [
                                'op_id'      => $operator['operation_id'],
                                'op_name'    => $operator['operation_name'],
                                'op_desc'    => $operator['operation_description'],
                                'method'     => $operator['protocol_type_code'],
                                'req_method' => (!empty($operator['request_method_code'])) ? CommonConst::REQ_METHOD_CODE_TO_STR[$operator['request_method_code']] : null,
                            ],
                        ];
                    }

                } // end for each
            }

            $results['data']['biz_ops'] = $bizOps;
            unset($results['data']['message']);

            RedisUtil::setData($this->redis, $bizOpsRedisDb, $bizOpsRedisKey, $results['data']);

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
     * BizOps 추가
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function add(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            $params = $request->getAttribute('params');

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            if ( ! isset($_SESSION['sess_user']['selected_app_id'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_EXIST_APP);
            }

            if (false === CommonUtil::validateParams($params, ['biz_name', 'app_id'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            $result = CommonUtil::callProcedure($this->ci, 'executeAddBiz', [
                'account_id'          => $this->accountId,
                'team_id'             => $this->teamId,
                'application_id'      => $params['app_id'],
                'biz_ops_name'        => $params['biz_name'],
                'biz_ops_description' => $params['biz_desc']
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            RedisUtil::delData($this->redis, CommonConst::CLIENT_BIZ_LIST_REDIS_KEY . $this->teamId . $this->accountId . $params['app_id'],
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
     * BizOps 여러개 삭제
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function remove(Request $request, Response $response)
    {

        $results = $this->jsonResult;

        try {

            $params = $request->getAttribute('params');
            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id', 'op_id'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
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
     * BizOps 개별 삭제
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function removeEach(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            $params = $request->getAttribute('params');
            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            $result = CommonUtil::callProcedure($this->ci, 'executeDelBizParams', [
                'account_id'     => $this->accountId,
                'team_id'        => $this->teamId,
                'application_id' => $params['app_id'],
                'biz_ops_id'     => $params['biz_id']
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception('', $result['returnCode']);
            }

            RedisUtil::getDataWithDel($this->redis, CommonConst::CLIENT_BIZ_LIST_REDIS_KEY . $this->teamId . $this->accountId . $params['app_id'],
                CommonConst::REDIS_CLIENT_SESSION);

            RedisUtil::delData($this->redis, CommonConst::CLIENT_BIZ_REDIS_KEY . $params['biz_id'],
                CommonConst::REDIS_CLIENT_SESSION);

        } catch (\Exception $ex) {

            $results = [
                'result' => ErrorConst::FAIL_STRING,
                'data'   => [
                    'message' => $this->_getErrorMessage($ex, ErrorConst::ERROR_GROUP_DEL_BIZ),
                ]
            ];
        }

        return $response->withJson($results, ErrorConst::SUCCESS_CODE);

    }


    /**
     * BizOps 속성 수정을 위한 정보
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

            if (false === CommonUtil::validateParams($args, ['app_id', 'biz_id'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            $appId = (int)$args['app_id'];
            $bizOpsId = (int)$args['biz_id'];

            $selectedApp = $this->_getSelectedApp($appId);
            $isUpdates = $this->_checkUpdateApps();

            $results['data']['app_id'] = $appId;
            $results['data']['biz_id'] = $bizOpsId;
            $results['data']['selected_app'] = $selectedApp;
            $results['data']['sample_code_types'] = CommonConst::SAMPLE_SOURCE_TYPE;
            $results['data']['var_types'] = CommonConst::REQUEST_VAR_TYPES;
            $results['data']['is_partner'] = false;
            $results['data']['partner_data'] = null;

            // 파트너 계정으로 로그인 한 경우
            if (isset($_SESSION['partner']['data']) && !empty($_SESSION['partner']['data'])) {
                $partnerData = $_SESSION['partner']['data'];
                $this->accountId = $partnerData['account_id'];
                $this->teamId = $partnerData['team_id'];
                $results['data']['is_partner'] = true;
                $results['data']['partner_data'] = $partnerData;

                if ($appId != $partnerData['app_id']) {
                    throw new \Exception(null, ErrorConst::ERROR_RDB_APP_ACC_AUTH_FAIL);
                }

                if ($bizOpsId != $partnerData['biz_id']) {
                    throw new \Exception(null, ErrorConst::ERROR_RDB_BIZ_ACC_FAIL);
                }
            }

            $redisKey = CommonConst::CLIENT_BIZ_REDIS_KEY . $bizOpsId;
            $redisDb = CommonConst::REDIS_CLIENT_SESSION;

            if (APP_ENV == APP_ENV_PRODUCTION || APP_ENV == APP_ENV_DEVELOPMENT || APP_ENV == APP_ENV_STAGING) {
                if (false !== ($bizOpsInfo = RedisUtil::getData($this->redis, $redisKey, $redisDb))) {
                    $results['data']['biz_info'] = $bizOpsInfo;
                    return $response->withJson($results, ErrorConst::SUCCESS_CODE);
                }
            }

            // 비즈옵스 정보 패치
            $result = CommonUtil::callProcedure($this->ci, 'executeGetBiz', [
                'account_id'     => $this->accountId,
                'team_id'        => $this->teamId,
                'application_id' => $appId,
                'biz_ops_id'     => $bizOpsId
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            $bizOps = $result['data'][1][0];
            $bizOpsInfo = [
                'app_id'            => $appId,
                'biz_id'            => $bizOps['biz_ops_id'],
                'biz_uid'           => AppsUtil::replaceUid($bizOps['biz_ops_key']),
                'biz_name'          => $bizOps['biz_ops_name'],
                'biz_desc'          => $bizOps['biz_ops_description'],
                'method'            => $bizOps['protocol_type_code'],
                'actor_alias'       => $bizOps['actor_alias'] ?? 'Consumer',
                'req_method'        => AppsUtil::getReqMethod($bizOps['request_method_code']) ?? CommonConst::REQ_METHOD_GET_STR,
                'cache_flag'        => $bizOps['cache_flag'] ?? 0,
                'cache_expire_time' => $bizOps['cache_expire_time'] ?? 0,
                'reg_date'          => $bizOps['register_date'],
                'request'           => [
                    [
                        'req_key'           => '',
                        'req_var_type'      => '',
                        'req_desc'          => '',
                        'req_required_flag' => '',
                    ]
                ],
            ];

            // 연결된 오퍼레이션/컨트롤 목록
            $result = CommonUtil::getRedisData($this->ci, CommonConst::CLIENT_BIND_OPS_LIST_REDIS_KEY . $bizOpsId, 'executeGetbindOptList', [
                'account_id'     => $this->accountId,
                'team_id'        => $this->teamId,
                'application_id' => $appId,
                'biz_ops_id'     => $bizOpsId
            ]);

            // ALTER
            $containers = $result[1][1] ?? [];
            if (!empty($containers)) {
                $bizOpsInfo['controlls'] = $containers;
            }

            // ASYNC
            $async = array_values($result[2][1][0]) ?? [];
            if (!empty($async) && $async[0] !== null ) {
                $bizOpsInfo['async_bind_seq'] = $async;
            }

            $tmpArr = [];
            foreach ($result[0][1] as $key => $ops) {
                $tmpArr[$ops['binding_seq']] = $ops;
            }
            ksort($tmpArr);

            $bizOpsInfo['operators'] = [];
            $bizOpsInfo['lines'] = [];

            foreach ($tmpArr as $key => $ops) {

                $opId = $ops['operation_id'];
                $bSeq = $ops['binding_seq'];
                $opInfo = AppsUtil::getOperationInfo($this->ci, $this->accountId, $this->teamId, $opId) ?? [];

                if (!empty($ops['control_container_code'])) {

                    $containerInfo = json_decode($ops['control_container_info'], true);
                    if (false !== ($containIdx = array_search($containerInfo['control_id'], array_column($containers, 'control_alt_id')))) {
                        $bSeq = $containers[$containIdx]['binding_seq'];
                    }

                    $bizOpsInfo['operators'][$bSeq][] = [
                        'op_id'           => $opId,
                        'op_text'         => $ops['operation_name'],
                        'binding_seq'     => $ops['binding_seq'],
                        'target_line_idx' => $ops['operation_namespace_id'],
                        'controll_info'   => $containerInfo,
                        'op_info'         => $opInfo,
                    ];


                } else {

                    $bizOpsInfo['operators'][$bSeq] = [
                        'op_id'           => $opId,
                        'op_text'         => $ops['operation_name'],
                        'binding_seq'     => $ops['binding_seq'],
                        'target_line_idx' => $ops['operation_namespace_id'],
                        'op_info'         => $opInfo,
                    ];

                }

                if ( ! empty($isUpdates)) {
                    if (false !== ($updateAppIndex = array_search($ops['operation_id'], array_column($isUpdates, 'op_id')))) {
                        $bizOpsInfo['operators'][$bSeq]['is_new'] = true;
                    }
                }

                $bizOpsInfo['lines'][$ops['operation_namespace_id']] = [
                    'line_idx'       => $ops['operation_namespace_id'],
                    'line_title'     => $ops['operation_namespace_name']
                ];

            }


            if ( ! empty($bizOpsInfo['req_method'])) {

                $result = CommonUtil::callProcedure($this->ci, 'executeGetBizReqParams', [
                    'account_id'     => $this->accountId,
                    'team_id'        => $this->teamId,
                    'application_id' => $appId,
                    'biz_ops_id'     => $bizOpsId
                ]);

                if (0 !== $result['returnCode']) {
                    throw new \Exception($result['message'], $result["returnCode"]);
                }

                $bizReqs = $result['data'][1];
                //CommonUtil::showArrDump($bizReqs);
                if ( ! empty($bizReqs)) {
                    $bizOpsInfo['request'] = [];
                    foreach ($bizReqs as $req) {
                        $bizOpsInfo['request'][] = [
                            'param_id'             => $req['parameter_id'],
                            'req_key'              => $req['parameter_key_name'],
                            'req_var_type'         => CommonConst::VAR_TYPE_CODE_TO_STR[$req['parameter_type_code']],
                            'req_desc'             => $req['parameter_description'] ?? null,
                            'req_required_flag'    => $req['required_flag'] ?? CommonConst::PARAMS_NO_REQUIRED_CODE,
                            'sub_parameter_format' => CommonUtil::getValidJSON($req['sub_parameter_format']),
                        ];
                    }
                }

                $bQuery = [];
                $endPoint = GeneratorConst::GEN_ROUTE_PREFIX . $bizOpsInfo['biz_uid'];
                $cleanUrl = '';
                $bizOpsInfo['get_command'] = $endPoint . CommonConst::GET_COMMAND_URL;
                $bizOpsInfo['docs_end_point'] = $endPoint . CommonConst::DOCS_URL;

                foreach ($bizOpsInfo['request'] as $rKey => $row) {
                    //$bizOpsInfo['request'][$rKey] = array_map('trim', $row);
                    $bizOpsInfo['request'][$rKey] = $row;
                    if ($bizOpsInfo['req_method'] === CommonConst::REQ_METHOD_GET_STR) {
                        $bQuery[$row['req_key']] = '{' . $row['req_key'] . '}';
                    } elseif ($bizOpsInfo['req_method'] === CommonConst::REQ_METHOD_GET_CLEANURL_STR) {
                        $cleanUrl .= '/{' . $row['req_key'] . '}';
                    }
                }

                if (!empty($cleanUrl)) {
                    $endPoint .= $cleanUrl;
                }

                // 팀 속성을 조회해 배포관련된 정보를 얻어옵니다.
                $result = CommonUtil::callProcedure($this->ci, 'executeGetTeamInfo', [
                    'account_id' => $this->accountId,
                    'team_id'    => $this->teamId,
                ]);

                if (0 !== $result['returnCode']) {
                    throw new \Exception($result['message'], $result["returnCode"]);
                }

                $conf = $result['data'][1][0];
                $bizOpsInfo['end_point'] = $endPoint;
                $bizOpsInfo['dev_end_point'] = urldecode(CommonUtil::getBaseUrl($bizOpsInfo['end_point'], $bQuery));
                $bizOpsInfo['product_end_point_url'] = CommonUtil::makeUrl($conf['domain_name'] . $bizOpsInfo['end_point']);
                //$bizOpsInfo['product_end_point'] = urldecode(CommonUtil::makeUrl($conf['domain_name'] . $bizOpsInfo['end_point'], $bQuery));
                $bizOpsInfo['product_end_point'] = urldecode(CommonUtil::getBaseUrl($bizOpsInfo['end_point'], $bQuery));
                $bizOpsInfo['get_command'] = CommonUtil::getBaseUrl($bizOpsInfo['get_command']);
                $bizOpsInfo['docs_end_point'] = CommonUtil::getBaseUrl($bizOpsInfo['docs_end_point']);
            }

            // 빌드/배포 이력조회
//            $result = CommonUtil::callProcedure($this->ci, 'executeGetBizDeployList', [
//                'account_id'       => $this->accountId,
//                'team_id'          => $this->teamId,
//                'application_id'   => $appId,
//                'biz_ops_id'       => $bizOpsId,
//                'environment_code' => CommonConst::DEPLOY_TARGET_DEV
//                //'environment_code' => ''
//            ]);
//
//            if (0 !== $result['returnCode']) {
//                throw new \Exception($result['message'], $result["returnCode"]);
//            }
//            $bizOpsInfo['deployment'] = $result['data'][1] ?? [];

            $results['data']['biz_info'] = $bizOpsInfo;
            RedisUtil::setData($this->redis, $redisDb, $redisKey, $bizOpsInfo);

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
     * BizOps 속성 수정
     * 속성만 수정 빌드/파일생성 X
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

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');
            LogMessage::debug('bunit modify params :: ' . json_encode($params, JSON_UNESCAPED_UNICODE));

            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id', 'biz_name', 'req_method'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            // @todo Redis
//            $redisKey = CommonConst::CLIENT_BIZ_REDIS_KEY . $params['biz_id'];
//            $redisDb = CommonConst::REDIS_CLIENT_SESSION;
//            $bizOpsRedis = RedisUtil::getData($this->redis, $redisKey, $redisDb);
//
//            $results['data']['exist_redis_data'] = true;
//            if (empty($bizOpsRedis)) {
//                $results['data']['exist_redis_data'] = false;
//            }

            // 비즈옵스 수정 프로시저 호출
            $bizInfo = [
                'account_id'          => $this->accountId,
                'team_id'             => $this->teamId,
                'application_id'      => (int)$params['app_id'],
                'biz_ops_id'          => (int)$params['biz_id'],
                'biz_ops_name'        => $params['biz_name'],
                'biz_ops_description' => $params['biz_desc'] ?? '',
                'actor_alias'         => $params['actor_alias'] ?? 'Consumer',
                'protocol_type_code'  => CommonConst::PROTOCOL_TYPE_SIMPLE_HTTP,
                'request_method_code' => AppsUtil::getReqMethod($params['req_method']) ?? CommonConst::REQ_METHOD_GET_CODE,
                'cache_flag'          => $params['cache_flag'] ?? 0, // 1 or 0
                'cache_expire_time'   => $params['cache_expire_time'] ?? 0, // minutes
            ];

            $result = CommonUtil::callProcedure($this->ci, 'executeModifyBiz', $bizInfo);
            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            $dBparams = [];
            $formData = $params['form_data'] ?? [];

            if (!empty($formData)) {
                foreach ($formData as $key => $row) {
                    $dBparams[$key] = [
                        'direction_code'        => CommonConst::DIRECTION_IN_CODE,
                        'required_flag'         => (!empty($row['req_required_flag'])) ? $row['req_required_flag'] : CommonConst::PARAMS_NO_REQUIRED_CODE,
                        'parameter_key_name'    => trim($row['req_key']),
                        'parameter_type_code'   => CommonConst::VAR_TYPE_STR_TO_CODE[$row['req_var_type']],
                        'parameter_description' => trim($row['req_desc']),
                    ];

                    // param_id 가 있으면 기존 파라미터 수정
                    if (isset($row['param_id']) && !empty($row['param_id'])) {
                        $dBparams[$key]['parameter_id'] = $row['param_id'];
                    }

                    // 파라미터 타입이 JSON일 경우만 sub_parameter_format가 존재 함
                    if (isset($row['sub_parameter_format']) && !empty($row['sub_parameter_format']) && $row['req_var_type'] === CommonConst::VAR_TYPE_JSON) {
                        $dBparams[$key]['sub_parameter_format'] = CommonUtil::getValidJSON($row['sub_parameter_format']);
                    }
                }

                // 비즈옵스 파라미터 저장
                $result = CommonUtil::callProcedure($this->ci, 'executeSetBizParams', [
                    'account_id'          => $this->accountId,
                    'team_id'             => $this->teamId,
                    'application_id'      => $params['app_id'],
                    'biz_ops_id'          => $params['biz_id'],
                    'protocol_type_code'  => CommonConst::PROTOCOL_TYPE_SIMPLE_HTTP,
                    'request_method_code' => AppsUtil::getReqMethod($params['req_method']) ?? CommonConst::REQ_METHOD_GET_CODE,
                    'parameters'          => json_encode($dBparams, JSON_UNESCAPED_UNICODE),
                ]);

                if (0 !== $result['returnCode']) {
                    throw new \Exception($result['message'], $result["returnCode"]);
                }
            }

            $this->_delRedisForBiz($params, true);

            // @todo Redis
//            if (!empty($bizOpsRedis)) {
//                $bizOpsRedis['biz_name'] = $bizInfo['biz_ops_name'];
//                $bizOpsRedis['biz_desc'] = $bizInfo['biz_ops_description'];
//                $bizOpsRedis['actor_alias'] = $bizInfo['actor_alias'];
//                $bizOpsRedis['method'] = $bizInfo['protocol_type_code'];
//                $bizOpsRedis['req_method'] = AppsUtil::getReqMethod($params['req_method']) ?? CommonConst::REQ_METHOD_GET_CODE;
//                $bizOpsRedis['biz_name'] = $bizInfo['biz_ops_name'];
//                $bizOpsRedis['biz_name'] = $bizInfo['biz_ops_name'];
//            }

        } catch (\Exception $ex) {
            $results = [
                'result' => ErrorConst::FAIL_STRING,
                'data'   => [
                    'message' => $this->_getErrorMessage($ex),
                ]
            ];
        }

        LogMessage::info('Removed gc :: ' . gc_collect_cycles());
        return $response->withJson($results, ErrorConst::SUCCESS_CODE);
    }

    /**
     * 비즈옵스 빌드/파일생성
     *
     *
     * @param Request  $request
     * @param Response $response
     * @param          $args - if has getData return data only
     *
     * @return Response
     */
    public function buildCallback(Request $request, Response $response, $args)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD);
            }

            $params = $request->getAttribute('params');
            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            $redisKey = CommonConst::CLIENT_BIZ_BUILD_REDIS_KEY . $params['biz_id'];
            $redisDb = CommonConst::REDIS_CLIENT_SESSION;

            // 문서용 데이터 리턴
            if (isset($args['getData']) && $args['getData'] === 'getData') {

                if (false === ($bizOpsInfo = RedisUtil::getData($this->redis, $redisKey, $redisDb))) {
                    throw new \Exception('', ErrorConst::ERROR_UNKNOWN_CODE);
                }

                $opGroup = [];
                foreach ($bizOpsInfo['operators'] as $op) {
                    $opGroup[$op['operation_namespace_id'] . '_' . $op['op_ns_name']][$op['target_url']][] = $op;
                }

                $bizOpsInfo['operators'] = $opGroup;
                return $response->withJson($bizOpsInfo, ErrorConst::SUCCESS_CODE);

            }

            // 비즈 옵스의 정보
            $result = CommonUtil::callProcedure($this->ci, 'executeGetBiz', [
                'account_id'     => $this->accountId,
                'team_id'        => $this->teamId,
                'application_id' => $params['app_id'],
                'biz_ops_id'     => $params['biz_id']
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            $bizOps = $result['data'][1][0] ?? [];
            if (!empty($bizOps)) {
                $bizOpsInfo = [
                    'app_id'            => (int)$params['app_id'],
                    'biz_id'            => (int)$bizOps['biz_ops_id'],
                    'biz_uid'           => AppsUtil::replaceUid($bizOps['biz_ops_key']),
                    'biz_name'          => $bizOps['biz_ops_name'],
                    'biz_desc'          => $bizOps['biz_ops_description'],
                    'actor_alias'       => $bizOps['actor_alias'],
                    'method'            => $bizOps['protocol_type_code'] ?? null,
                    'req_method'        => $bizOps['request_method_code'] ?? null,
                    'reg_date'          => $bizOps['register_date'],
                    'request'           => $bizOps['request'] ?? [],
                    'operators'         => $bizOps['operators'] ?? [],
                    'lines'             => $bizOps['lines'] ?? [],
                    'cache_flag'        => $bizOps['cache_flag'] ?? 0,
                    'cache_expire_time' => $bizOps['cache_expire_time'] ?? 0,
                    'user_id'           => $this->accountEmail,
                    'account_id'        => $this->accountId,
                    'team_id'           => $this->teamId
                ];
            }

            // 비즈의 IN 파라미터
            $result = CommonUtil::callProcedure($this->ci, 'executeGetBizReqParams', [
                'account_id'     => $this->accountId,
                'team_id'        => $this->teamId,
                'application_id' => (int)$params['app_id'],
                'biz_ops_id'     => (int)$bizOps['biz_ops_id'],
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            // 메소드가 정의 되어 있지 않으면 에러
            if (empty($bizOps['request_method_code'])) {
                throw new \Exception(null, ErrorConst::ERROR_REQUEST_METHOD_NOT_DEFINED);
            }

            // 리퀘스트가 정의 되어 있지 않으면 에러
            if (empty($result['data'][1])) {
                throw new \Exception(null, ErrorConst::ERROR_REQUEST_KEY_NOT_DEFINED);
            }

            $endPoint = GeneratorConst::GEN_ROUTE_PREFIX . AppsUtil::replaceUid($bizOps['biz_ops_key']);
            $bizOpsInfo['end_point'] = $endPoint;
            $bizOpsInfo['get_command'] = $endPoint . CommonConst::GET_COMMAND_URL;

            $bQuery = [];
            $bizReqs = $result['data'][1];
            if ( ! empty($bizReqs)) {
                $bizOpsInfo['request'] = [];
                foreach ($bizReqs as $req) {
                    $bizOpsInfo['request'][] = [
                        'param_id'      => $req['parameter_id'],
                        'req_key'       => $req['parameter_key_name'],
                        'req_var_type'  => CommonConst::VAR_TYPE_CODE_TO_STR[$req['parameter_type_code']],
                        'req_desc'      => $req['parameter_description'],
                        'required_flag' => $req['required_flag'],
                    ];

                    if ($bizOps['request_method_code'] === CommonConst::REQ_METHOD_GET_CODE) {
                        $bQuery[$req['parameter_key_name']] = '{' . $req['parameter_key_name'] . '}';
                    } elseif ($bizOps['request_method_code'] === CommonConst::REQ_METHOD_GET_CLEANURL_CODE) {
                        $endPoint .= '/' . '{' . $req['parameter_key_name'] . '}';
                    }
                }
            }

            // 팀 속성을 조회해 배포관련된 정보를 얻어옵니다.
            $result = CommonUtil::callProcedure($this->ci, 'executeGetTeamInfo', [
                'account_id' => $this->accountId,
                'team_id'    => $this->teamId,
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            $conf = $result['data'][1][0];

            $bizOpsInfo['get_command_end_point'] = CommonUtil::getBaseUrl($bizOpsInfo['get_command']);
            //$bizOpsInfo['product_end_point'] = urldecode(CommonUtil::makeUrl($conf['domain_name'] . $endPoint, $bQuery));
            $bizOpsInfo['product_end_point'] = urldecode(CommonUtil::getBaseUrl($endPoint, $bQuery));

            // 연결된 오퍼레이션/컨트롤 목록
            $result = CommonUtil::callProcedure($this->ci, 'executeGetbindOptList', [
                'account_id'     => $this->accountId,
                'team_id'        => $this->teamId,
                'application_id'      => $params['app_id'],
                'biz_ops_id'          => $params['biz_id'],
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            // ALTER
            $containers = $result['data'][1][1] ?? [];
            if (!empty($containers)) {
                $bizOpsInfo['controls'] = $containers;
            }

            // ASYNC
            $async = array_values($result['data'][2][1][0]) ?? [];
            if (!empty($async) && $async[0] !== null ) {
                $bizOpsInfo['async_bind_seq'] = $async;
            }

            $tmpArr = [];
            foreach ($result['data'][0][1] as $key => $ops) {

                $opId = $ops['operation_id'];

                if (false === ($tmpOp = AppsUtil::getOperationInfo($this->ci, $this->accountId, $this->teamId, $opId))) {
                    throw new \Exception('Error get Operation');
                }

                $bQuery = [];
                $tmpOp['origin_target_url'] = $tmpOp['target_url'];
                if ($tmpOp['req_method'] === CommonConst::REQ_METHOD_GET_CLEANURL_STR ) {
                    foreach ($tmpOp['request'] as $reqs) {
                        $tmpOp['target_url'] .= '/{' . $reqs['req_key'] . '}';
                    }
                } elseif ($tmpOp['req_method'] === CommonConst::REQ_METHOD_GET_STR ) {
                    foreach ($tmpOp['request'] as $reqs) {
                        $bQuery[$reqs['req_key']] = '{' . $reqs['req_key'] . '}';
                    }

                    $tmpOp['target_url'] = urldecode(CommonUtil::makeUrl($tmpOp['target_url'], $bQuery));
                }

                $ops['auth_keys_array'] = CommonUtil::getValidJSON($ops['auth_keys']);

                $argmentsResult = CommonUtil::callProcedure($this->ci, 'executeGetArgumentList', [
                    'account_id'     => $this->accountId,
                    'team_id'        => $this->teamId,
                    'application_id' => $params['app_id'],
                    'biz_ops_id'     => $params['biz_id'],
                    'binding_seq'    => $ops['binding_seq'],
                    'operation_id'   => $opId
                ]);

                if (0 !== $argmentsResult['returnCode']) {
                    throw new \Exception(null, $result['returnCode']);
                }

                $ops = $ops + $tmpOp;
                $ops['arguments'] = $argmentsResult['data'][1];
                $tmpArr[$ops['binding_seq']] = $ops;
            }
            ksort($tmpArr);
            $bizOpsInfo['operators'] = $tmpArr ?? [];

            $generator = new GenerateUtil($this->ci, $bizOpsInfo);

            // Generate Controller
            if (false === ($generator->getFileName()->setControllerFile()->writeFile())) {
                throw new \Exception(null, ErrorConst::ERROR_GEN_CONTROLLER);
            }

            // Generate HTML - API DOCS
            if (false === ($generator->getFileName('HTML')->setDocsFile($response)->writeFile())) {
                throw new \Exception(null, ErrorConst::ERROR_GEN_DOCS);
            }

            // Generate Router
            if (false === ($generator->getFileName('ROUTER')->setRouteFile()->writeFile())) {
                throw new \Exception(null, ErrorConst::ERROR_GEN_ROUTE);
            }

            // 비즈 옵스 빌드 생성 / 버전 생성
            $result = CommonUtil::callProcedure($this->ci, 'executeAddBizBuild', [
                'account_id'          => $this->accountId,
                'team_id'             => $this->teamId,
                'application_id'      => $params['app_id'],
                'biz_ops_id'          => $params['biz_id'],
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            $results['data'] = [
                'biz_ops_version_id' => $result['data'][1][0]['biz_ops_version_id'],
                'biz_ops_version'    => $result['data'][1][0]['biz_ops_version'],
            ];


            RedisUtil::setData($this->redis, $redisDb, $redisKey, $bizOpsInfo);


        } catch (\Exception $ex) {
            $results = [
                'result' => ErrorConst::FAIL_STRING,
                'data'   => [
                    'message' => $this->_getErrorMessage($ex),
                ]
            ];
        }

        LogMessage::info('Removed gc :: ' . gc_collect_cycles());
        return $response->withJson($results, ErrorConst::SUCCESS_CODE);
    }


    /**
     * BizOps 테스트를 위한 파라미터 정보
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function getBizParams(Request $request, Response $response)
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

            // 비즈옵스의 IN 파라미터 조회
            $result = CommonUtil::callProcedure($this->ci, 'executeGetBizReqParams', [
                'account_id'     => $this->accountId,
                'team_id'        => $this->teamId,
                'application_id' => $params['app_id'],
                'biz_ops_id'     => $params['biz_id'],
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            // 비즈옵스의 IN 파라미터가 없으면 에러
            if (empty($result['data'][1])) {
                throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
            }

            $results['data'] = $result['data'][1];


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
     * BizOps 테스트
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

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');
            LogMessage::debug('test params :: ' . json_encode($params, JSON_UNESCAPED_UNICODE));

            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            $redisKey = CommonConst::CLIENT_BIZ_REDIS_KEY . $params['biz_id'];
            $redisDb = CommonConst::REDIS_CLIENT_SESSION;

            if (false === ($bizOpsInfo = RedisUtil::getData($this->redis, $redisKey, $redisDb))) {
                throw new \Exception(null, ErrorConst::ERROR_RDB_NO_DATA_EXIST);
            }

            $httpClient = new \GuzzleHttp\Client();

            $options = [
                'verify' => false,
                'timeout' => 10,
                'headers' => [
                    'User-Agent' => CommonConst::BUNIT_TEST_HEADER
                ]
            ];

            $reqMethod = AppsUtil::getReqMethodString($bizOpsInfo['req_method']);

            $cleanUrl = false;
            switch ($bizOpsInfo['req_method']) {
                case CommonConst::REQ_METHOD_PUT_STR :
                    $opReqTypeVar = 'body';
                    break;
                case CommonConst::REQ_METHOD_DEL_STR :
                case CommonConst::REQ_METHOD_POST_STR :
                    $opReqTypeVar = 'form_params';
                    break;
                case CommonConst::REQ_METHOD_GET_CLEANURL_STR :
                    $cleanUrl = true; // no break;
                default :
                    $opReqTypeVar = 'query';
            }

            $endPoint = GeneratorConst::GEN_ROUTE_PREFIX . $bizOpsInfo['biz_uid'];
            foreach ($bizOpsInfo['request'] as $req) {

                // 비즈옵스의 IN 파라미터에 상응하는 파라미터 값이 없을 경우 에러
                if ((false === array_key_exists($req['req_key'], $params)) && $req['req_required_flag'] === 1) {
                    throw new \Exception('', ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
                }

                if (true === array_key_exists($req['req_key'], $params)) {
                    if (!empty($cleanUrl)) {
                        $endPoint .= '/' . $params[$req['req_key']];
                    } else {
                        $options[$opReqTypeVar][$req['req_key']] = $params[$req['req_key']];
                    }
                }
            }

            $endPoint = urldecode(CommonUtil::getBaseUrl($endPoint));

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
                'request'            => ($options['query'] ?? null) ?? ($options['form_params'] ?? null),
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

        LogMessage::info('Removed gc :: ' . gc_collect_cycles());
        return $response->withJson($results, ErrorConst::SUCCESS_CODE);

    }


    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function getArgumentInfo(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');
            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id', 'op_idxs'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            $sizeOfOps = count($params['op_idxs']);
            $params['op_idxs'] = (true === CommonUtil::isValidJSON($params['op_idxs'])) ? json_decode($params['op_idxs'], true) : $params['op_idxs'];

            if ($sizeOfOps < 1 || $sizeOfOps > 2) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            $redisKey = CommonConst::CLIENT_RELAY_REDIS_KEY . $params['biz_id'];
            $redisDb = CommonConst::REDIS_CLIENT_SESSION;

            //if (false !== ($redisData = RedisUtil::getData($this->redis, $redisKey, $redisDb))) {
                //LogMessage::debug('$redisData');
                //return $response->withJson($redisData, ErrorConst::SUCCESS_CODE);
            //}

            $appId = $params['app_id'];
            $bizOpsId = $params['biz_id'];
            $settedOpList = [];

            // 비즈 옵스에 포함된 오퍼레이션 목록을 조회
            if (!empty($params['op_idxs'][0])) {
                $result = CommonUtil::callProcedure($this->ci, 'executeGetbindOptList', [
                    'account_id'     => $this->accountId,
                    'team_id'        => $this->teamId,
                    'application_id' => $appId,
                    'biz_ops_id'     => $bizOpsId
                ]);

                if (0 !== $result['returnCode']) {
                    throw new \Exception($result['message'], $result["returnCode"]);
                }

                if (empty($result['data'][0][1])) {
                    throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                }

                foreach ($result['data'][0][1] as $row) {
                    $settedOpList[$row['operation_id']] = $row;
                }
            }

            // 비즈옵스의 IN 파라미터 조회
            $result = CommonUtil::callProcedure($this->ci, 'executeGetBizReqParams', [
                'account_id'     => $this->accountId,
                'team_id'        => $this->teamId,
                'application_id' => $appId,
                'biz_ops_id'     => $bizOpsId
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            // 비즈옵스의 IN 파라미터가 없으면 에러
            if (empty($result['data'][1])) {
                throw new \Exception('', ErrorConst::ERROR_RDB_NO_DATA_EXIST);
            }

            // 비즈옵스의 IN 파라미터는 항상 포함
            $bizReqs = $result['data'][1];
            if (!empty($bizReqs)) {
                foreach ($bizReqs as $key => $req) {
                    $results['data']['params'][0]['biz'][$key] = [
                        'param_id'             => $req['parameter_id'],
                        'param_seq'            => $req['parameter_seq'] ?? $key + 1,
                        'param_key'            => $req['parameter_key_name'],
                        'param_var_type'       => CommonConst::VAR_TYPE_CODE_TO_STR[$req['parameter_type_code']],
                        'sub_parameter_format' => (!empty($req['sub_parameter_format'])) ? json_decode($req['sub_parameter_format'], true) : null,
                        //'param_desc'     => $req['parameter_description'] ?? null,
                    ];
                }
            }


            foreach ($params['op_idxs'] as $key => $opId) {

                if (0 === (int)$opId) continue;

                if (!array_key_exists($opId, $settedOpList)) {
                    throw new \Exception('Error get Operation');
                }

                if (false === ($op = AppsUtil::getOperationInfo($this->ci, $this->accountId, $this->teamId, $opId))) {
                    throw new \Exception('Error get Operation');
                }

                // 해당 오퍼레이션에 전달 인자 목록을 조회 (request)
                $result = CommonUtil::callProcedure($this->ci, 'executeGetArgumentList', [
                    'account_id'     => $this->accountId,
                    'team_id'        => $this->teamId,
                    'application_id' => $appId,
                    'biz_ops_id'     => $bizOpsId,
                    'binding_seq'    => $settedOpList[$opId]['binding_seq'],
                    'operation_id'   => $opId
                ]);

                if (0 !== $result['returnCode']) {
                    throw new \Exception($result['message'], $result["returnCode"]);
                }

                $opDb = $result['data'][1];
                if ($sizeOfOps === 1) {
                    $opData = $op['request'];
                    $opIdx = 1;
                } else {
                    $opData = ($key === array_key_first($params['op_idxs'])) ? $op['response'] : $op['request'];
                    $opIdx = $key;
                }

                foreach ($opData as $seq => $opRes) {

                    $tmpData = [
                        'operation_id'         => $opId,
                        'binding_seq'          => $settedOpList[$opId]['binding_seq'],
                        'param_id'             => $opRes['param_id'],
                        'param_seq'            => $opRes['param_seq'],
                        'param_key'            => $opRes['req_key'] ?? $opRes['res_key'],
                        'param_var_type'       => $opRes['req_var_type'] ?? $opRes['res_var_type'],
                        'sub_parameter_format' => $opRes['sub_parameter_format'] ?? null,
                    ];

                    $dbKey = array_search($opRes['param_id'], array_column($opDb, 'parameter_id'));
                    if (false !== $dbKey) {
                        $tmpData['sub_parameter_path ']         = $opDb[$dbKey]['sub_parameter_path '] ?? null;
                        $tmpData['relay_flag']                  = $opDb[$dbKey]['relay_flag'];
                        $tmpData['argument_value']              = $opDb[$dbKey]['argument_value'];
                        $tmpData['relay_object_code']           = $opDb[$dbKey]['relay_object_code'];
                        $tmpData['relay_operation_id']          = $opDb[$dbKey]['relay_operation_id'];
                        $tmpData['relay_binding_seq']           = $opDb[$dbKey]['relay_binding_seq'] ?? null;
                        $tmpData['relay_biz_ops_id']            = $opDb[$dbKey]['relay_biz_ops_id'];
                        $tmpData['relay_object_name']           = $opDb[$dbKey]['relay_object_name'];
                        $tmpData['relay_parameter_id']          = $opDb[$dbKey]['relay_parameter_id'];
                        $tmpData['relay_parameter_key_name']    = $opDb[$dbKey]['relay_parameter_key_name'];
                        $tmpData['relay_parameter_type_code']   = $opDb[$dbKey]['relay_parameter_type_code'];
                        $tmpData['relay_parameter_type']        = CommonConst::VAR_TYPE_CODE_TO_STR[$opDb[$dbKey]['relay_parameter_type_code']] ?? null;
                        $tmpData['relay_sub_parameter_format']  = CommonUtil::getValidJSON($opDb[$dbKey]['relay_sub_parameter_format']);
                        $tmpData['relay_sub_parameter_path'] = $opDb[$dbKey]['relay_sub_parameter_path'] ?? null;
                    }

                    $results['data']['params'][$opIdx]['ops'][$seq] = $tmpData;
                }
            }

            RedisUtil::setData($this->redis, $redisDb, $redisKey, $results);

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
     * 릴레이 아귀먼트 저장
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function setArgumentInfo(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');
            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id', 'op_id', 'binding_seq', 'arguments'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            $inputArgs = [];
            $arguments = (true === CommonUtil::isValidJSON($params['arguments'])) ? json_decode($params['arguments'], true) : $params['arguments'];

            foreach ($arguments as $argument) {
                foreach ($argument as $argKey => $val) {
                    if ($argKey === 'relay_flag' || $argKey === 'argument_value') continue;
                    if (empty($val)) unset($argument[$argKey]);
                }
                $inputArgs[] = $argument;
            }

            // [{"parameter_id":"", "relay_flag":"릴레이 여부 / 0=고정값, 1=릴레이", "argument_value":"전달 인자의 값", "relay_parameter_id":"릴레이할 파라미터의 parameter_id"}, ...]
            $inputArgs = json_encode($inputArgs, JSON_UNESCAPED_UNICODE);

            $result = CommonUtil::callProcedure($this->ci, 'executeSetArgument', [
                'account_id'     => $this->accountId,
                'team_id'        => $this->teamId,
                'application_id' => $params['app_id'],
                'biz_ops_id'     => $params['biz_id'],
                'binding_seq'    => $params['binding_seq'],
                'operation_id'   => $params['op_id'],
                'arguments'      => $inputArgs,
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            $redisDb = CommonConst::REDIS_CLIENT_SESSION;
            RedisUtil::delData($this->redis, CommonConst::CLIENT_RELAY_REDIS_KEY . $params['biz_id'], $redisDb);


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
     * 릴레이 아귀먼트 삭제
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function delArgumentInfo(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');

            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id', 'op_id', 'binding_seq', 'param_idxs'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            if (empty($params['param_idxs'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            foreach ($params['param_idxs'] as $idx) {
                $result = CommonUtil::callProcedure($this->ci, 'executeDelArgument', [
                    'account_id'     => $this->accountId,
                    'team_id'        => $this->teamId,
                    'application_id' => $params['app_id'],
                    'biz_ops_id'     => $params['biz_id'],
                    'binding_seq'    => $params['binding_seq'],
                    'operation_id'   => $params['op_id'],
                    'parameter_id'   => $idx,
                ]);

                if (0 !== $result['returnCode']) {
                    throw new \Exception($result['message'], $result["returnCode"]);
                }
            }

            $redisDb = CommonConst::REDIS_CLIENT_SESSION;
            RedisUtil::delData($this->redis, CommonConst::CLIENT_RELAY_REDIS_KEY . $params['biz_id'], $redisDb);


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
     * 파트너 정보를 세팅하고 Share Url 반환
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function makeExportUrl(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');
            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id', 'expireDate', 'op_id', 'partner_id'])) {
                LogMessage::error('Not found required field');
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            $expire = strtotime($params['expireDate'] . ' 23:59:59');
            if (($expire - time()) <= 0) {
                LogMessage::error('expireDate is lower then now.');
                throw new \Exception(null, ErrorConst::ERROR_UNKNOWN_CODE);
            }

            $saveData = [
                'account_id'            => $this->accountId,
                'team_id'               => $this->teamId,
                'partner_account_email' => $params['partner_id'],
                'op_id'                 => $params['op_id'],
                'biz_id'                => $params['biz_id'],
                'app_id'                => $params['app_id'],
                'path'                  => $this->userPath,
                'expire_date'           => date('Y-m-d H:i:s', $expire)
            ];

            $result = CommonUtil::callProcedure($this->ci, 'executeLinkOpToPartner', $saveData);
            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            $key = $result['data'][1][0]['partner_direct_access_key'];
            $db = CommonConst::REDIS_PARTNERS_SESSION;

            if (false === ($partnerData = RedisUtil::getData($this->redis, $key, $db))) {
                RedisUtil::setDataWithExpire($this->redis, $db, $key, $expire, $saveData);
            }

            $results['data'] = [
                'full_url' => CommonUtil::getDomain() . '/' . $this->lang . '/partner/signup/' . $key,
                'uri'      => '/partner/signup/' . $key,
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
     * 언어별 샘플 소스
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function getSampleCodes(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');

            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id', 'snipet'])) {
                LogMessage::error('Not found required field');
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            $snipets = CommonConst::SAMPLE_SOURCE_TYPE;
            $snipet = $snipets[$params['snipet']] ?? null;

            if (empty($snipet)) {
                LogMessage::error('Not found required field');
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            $redisKey = CommonConst::CLIENT_BIZ_REDIS_KEY . $params['biz_id'];
            $redisDb = CommonConst::REDIS_CLIENT_SESSION;

            if (false === ($bizOpsInfo = RedisUtil::getData($this->redis, $redisKey, $redisDb))) {
                throw new \Exception(null, ErrorConst::ERROR_RDB_NO_DATA_EXIST);
            }

            $results['data'] = AppsUtil::getSampleSource($params['snipet'], $bizOpsInfo);


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


    private function _getSelectedApp($appId)
    {
        $selectedApp = $this->apps[0] ?? null;

        if (is_array($this->apps) && !empty($this->apps)) {
            if (false !== ($index = array_search($appId, array_column($this->apps, 'application_id')))) {
                $selectedApp = $this->apps[$index];
            }
        }

        return [
            'app_id'   => $selectedApp['application_id'],
            'app_name' => $selectedApp['application_name'],
        ];
    }
}