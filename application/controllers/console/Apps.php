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
    util\AwsUtil
};

class Apps extends SynctreeConsole
{
    public function __construct(ContainerInterface $ci)
    {
        parent::__construct($ci);
    }

    /**
     * 어플리케이션 목록
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function list(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            if (false === ($isUpdates = $this->_checkUpdateApps())) {
                $isUpdates = [];
            }

            $apps = $appsSort = [];
            foreach ($this->apps as $key => $app) {
                $appSortKey = (int)$app['application_id'];
                $apps[$appSortKey] = [
                    'app_id'   => $app['application_id'],
                    'app_name' => $app['application_name'],
                    'app_type' => $app['application_type_code'],
                    'app_desc' => $app['application_description'],
                    'reg_date' => $app['register_date']
                ];

                if (false !== ($updateAppIndex = array_search($app['application_id'], array_column($isUpdates, 'app_id')))) {
                    $apps[$appSortKey]['is_new'] = true;
                }
            }

            krsort($apps, 1);

            // json 배열형식을 위해 키 제거
            foreach ($apps as $app) {
                $appsSort[] = $app;
            }

            $results['data']['apps'] = $appsSort;

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
     * 어플리케이션 추가
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

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');

            if (false === CommonUtil::validateParams($params, ['app_name', 'app_type'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            $result = CommonUtil::callProcedure($this->ci, 'executeAddApp', [
                'account_id' => $this->accountId,
                'team_id'    => $this->teamId,
                'app_name'   => $params['app_name'],
                'app_type'   => $params['app_type'],
                'app_desc'   => $params['app_desc'],
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            $results['data']['app_id'] = $result['data'][1][0]['application_id'];
            RedisUtil::delData($this->redis, CommonConst::CLIENT_APP_LIST_REDIS_KEY . $this->teamId . $this->accountId, CommonConst::REDIS_CLIENT_SESSION);

        } catch (\Exception $ex) {

            $results = [
                'result' => ErrorConst::FAIL_STRING,
                'message' => $this->_getErrorMessage($ex),
            ];
        }

        return $response->withJson($results, ErrorConst::SUCCESS_CODE);
    }

    /**
     * 어플리케이션 수정
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

            if (false === CommonUtil::validateParams($params, ['app_id'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            $result = CommonUtil::callProcedure($this->ci, 'executeModifyApp', [
                'account_id'   => $this->accountId,
                'team_id'      => $this->teamId,
                'app_id'       => $params['app_id'] ?? null,
                'app_name'     => $params['app_name'] ?? null,
                'app_desc'     => $params['app_desc'] ?? null,
                'archive_flag' => $params['archive_flag'] ?? null
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            $results['data'] = $result;
            RedisUtil::delData($this->redis, CommonConst::CLIENT_APP_LIST_REDIS_KEY . $this->teamId . $this->accountId, CommonConst::REDIS_CLIENT_SESSION);

        } catch (\Exception $ex) {

            $results = [
                'result' => ErrorConst::FAIL_STRING,
                'message' => $this->_getErrorMessage($ex),
            ];
        }

        return $response->withJson($results, ErrorConst::SUCCESS_CODE);
    }


    /**
     * 앱 삭제
     *
     * @param Request  $request
     * @param Response $response
     * @param          $args
     *
     * @return Response
     */
    public function remove(Request $request, Response $response, $args)
    {

        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            if (false === $delAppIdx = $request->getAttribute('params')) {
                $delAppIdx = $args;
            }

            RedisUtil::delData($this->redis, CommonConst::CLIENT_APP_LIST_REDIS_KEY . $this->teamId . $this->accountId, CommonConst::REDIS_CLIENT_SESSION);

        } catch (\Exception $ex) {

            $results = [
                'result' => ErrorConst::FAIL_STRING,
                'message' => $this->_getErrorMessage($ex),
            ];
        }

        return $response->withJson($results, ErrorConst::SUCCESS_CODE);

    }


    public function sendEmail(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $recipients = ['kimildo78@nntuple.com'];
            $subject = 'Amazon SES test (PHP용 AWS SDK)';
            $body =  '<h1>AWS Amazon Simple Email Service Test Email</h1>'.
                '<p>This email was sent with <a href="https://aws.amazon.com/ses/">'.
                'Amazon SES</a> using the <a href="https://aws.amazon.com/sdk-for-php/">'.
                'PHP용 AWS SDK</a>.</p>';

            if (false === AwsUtil::sendEmail($recipients, $subject, $body)) {
                throw new \Exception(null, ErrorConst::ERROR_SEND_EMAIL);
            }

        } catch (\Exception $ex) {

            $results = [
                'result' => ErrorConst::FAIL_STRING,
                'message' => $this->_getErrorMessage($ex),
            ];
        }

        return $response->withJson($results, ErrorConst::SUCCESS_CODE);

    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param          $args
     *
     * @return Response
     */
    public function showAppModal(Request $request, Response $response, $args)
    {
        $this->flash->addMessage('show_app_add', '1');
        return $response->withRedirect('/' . $this->dictionary['lang'] . '/console/apps');
    }

    /**
     * Top 에서 앱을 선택시 호출 메서드
     *
     * @param Request  $request
     * @param Response $response
     * @param          $args
     *
     * @return Response
     */
    public function selectApp(Request $request, Response $response, $args)
    {

        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            if (empty(($appId = $args['app_id']))) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            if (false !== ($index = array_search($appId, array_column($this->apps, 'application_id')))) {
                $app = $this->apps[$index];
                $_SESSION['sess_user']['selected_app_id'] = $app['application_id'];
                $_SESSION['sess_user']['selected_app_name'] = $app['application_name'];
            }

        } catch (\Exception $ex) {

            $results = [
                'result' => ErrorConst::FAIL_STRING,
                'data' => [
                    'message' => $this->_getErrorMessage($ex),
                ]
            ];
        }

        return $response->withJson($results, ErrorConst::SUCCESS_CODE);

    }

    /**
     * 파트너 계정 목록
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function getPartnerList(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            // 파트너 계정 리스트
            $result = CommonUtil::callProcedure($this->ci, 'executeGetPartnerList', [
                'account_id'     => $this->accountId,
                'team_id'        => $this->teamId,
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            $results['data']['partners'] = $result['data'][1] ?? [];

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