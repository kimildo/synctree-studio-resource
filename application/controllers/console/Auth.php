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
    util\RedisUtil
};

class Auth extends SynctreeConsole
{
    public function __construct(ContainerInterface $ci)
    {
        parent::__construct($ci);
    }

    /**
     * 로그인 폼
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     * @throws \Exception
     * @deprecated
     */
    public function signin(Request $request, Response $response)
    {
        $params = $request->getAttribute('params');

        $messages = $this->flashMessage;
        if ($messages) {
            CommonUtil::showArrDump($messages);
        }

        $errorNo = isset($messages['error']) ? $messages['error'][0] : '';
        $errorStr = '';

        if ( ! empty($errorNo)) {
            switch ($errorNo) {
                case -1 :
                    $errorStr = 'Fail to login. Exception Error.';
                    break;
                default :
                    $errorStr = $this->dictionary['error']['message'][$errorNo] ?? 'Fail to login. Exception Error.';
            }
        }

        $csrfData = $this->_addCsrfToken();
        $rememberEmail = isset($_COOKIE['remember']) ? $_COOKIE['remember'] : null;

        if (isset($params['code']) && !empty($params['code'])) {
            $tempData = RedisUtil::getData($this->redis, $params['code'], CommonConst::REDIS_PARTNERS_SESSION);
        }

        $this->renderer->render($response, 'auth/signin.twig', [
            'page_title'     => 'Welcome',
            'page_desc'      => 'Synctree Studio V2.1',
            'share_url'      => CommonUtil::getBaseUrl(),
            'share_image'    => CommonConst::AWS_S3_END_POINT . '/static/img/logo/synctree_logo_s.jpg',
            'dictionary'     => $this->dictionary,
            'csrf'           => $csrfData,
            'errorstr'       => $errorStr,
            'extr_page'      => true,
            'SCRIPT_UPDATED' => CommonConst::SCRIPT_UPDATED,
            'CSS_UPDATED'    => CommonConst::CSS_UPDATED,
            'domain'         => CommonUtil::getDomain(),
            'code'           => $params['code'] ?? null,
            'partner_email'  => $tempData['partner_account_email'] ?? null,
            'remember'       => $rememberEmail,
        ]);

        return $response;
    }


    /**
     * 로그인 폼 데이터 for react
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     * @throws \Exception
     */
    public function getSigninData(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');

            $csrfData = $this->_addCsrfToken();
            $rememberEmail = isset($_COOKIE['remember']) ? $_COOKIE['remember'] : null;

            if (isset($params['code']) && !empty($params['code'])) {
                $tempData = RedisUtil::getData($this->redis, $params['code'], CommonConst::REDIS_PARTNERS_SESSION);
            }

            $results['data'] = [
                'csrf'           => $csrfData,
                'SCRIPT_UPDATED' => CommonConst::SCRIPT_UPDATED,
                'CSS_UPDATED'    => CommonConst::CSS_UPDATED,
                'code'           => $params['code'] ?? null,
                'partner_email'  => $tempData['partner_account_email'] ?? null,
                'remember'       => $rememberEmail,
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
     * 로그인 수행
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function signinCallback(Request $request, Response $response)
    {
        $results = $this->jsonResult;
        $params = $request->getAttribute('params');

        $redirectUrl = '/' . $this->lang . '/auth/signin';
        if (!empty($params['code'])) {
            $redirectUrl .= '?code=' . $params['code'];
        }

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            if (APP_ENV == APP_ENV_PRODUCTION || /*APP_ENV == APP_ENV_DEVELOPMENT ||*/ APP_ENV == APP_ENV_STAGING) {

                $csrf = [
                    'csrf_name'  => $params['csrf_name'] ?? null,
                    'csrf_value' => $params['csrf_value'] ?? null,
                ];

                if (false === ($checkCsrf = $this->_checkCsrf($csrf))) {
                    throw new \Exception(null, ErrorConst::ERROR_CSRF_FAIL);
                }
            }

            if (false === CommonUtil::validateParams($params, ['email', 'password'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            $result = CommonUtil::callProcedure($this->ci, 'executeAdminLogin', $params);

            if ($result['returnCode'] != 0 || ! isset($result['data']) || empty($result['data'][1][0])) {
                throw new \Exception(null, $result['returnCode']);
            }

            $result = $result['data'][1][0];
            $userData = [
                'sess_id'           => session_id(),
                'account_id'        => $result['account_id'],
                'account_type_code' => $result['account_type_code'] ?? 0,
                'partner_flag'      => $result['partner_flag'] ?? 0,
                'team_id'           => $result['team_id'] ?? null,
                'team_name'         => $result['team_name'] ?? null,
                'email'             => $params['email'],
                'name'              => $result['full_name'],
            ];

            $this->_setSession($userData);

            unset($_COOKIE['remember']);
            setcookie('remember', '', time() - 1, '/');

            if ( isset($params['remember']) && ! empty($params['remember']) ) {
                setcookie('remember', $userData['email'], time() + 86400 * 30, '/');
            }

            unset($_SESSION['partner']);

            $redirectUrl = '/' . $this->lang . '/console/';
            if (!empty($result['partner_flag']) && isset($params['code']) && !empty($params['code'])) {
                $redirectUrl = '/' . $this->lang . '/partner/bunit';
                $tempData = RedisUtil::getData($this->redis, $params['code'], CommonConst::REDIS_PARTNERS_SESSION);
                $_SESSION['partner'] = [
                    'key'  => $params['code'],
                    'data' => $tempData
                ];
            }

            $results['data'] = [
                'csrf'         => $checkCsrf ?? false,
                'user_data'    => $userData,
                'redirect_url' => $redirectUrl,
                'partner_data' => $tempData ?? null,
            ];

        } catch (\Exception $ex) {
            $results = [
                'result' => ErrorConst::FAIL_STRING,
                'data'   => [
                    'message' => $this->_getErrorMessage($ex),
                    'redirect_url' => $redirectUrl,
                ]
            ];
        }

        return $response->withJson($results, ErrorConst::SUCCESS_CODE);

    }

    /**
     * 로그아웃
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function signout(Request $request, Response $response)
    {
        session_destroy();
        return $response->withJson($this->jsonResult, ErrorConst::SUCCESS_CODE);
    }

    /**
     * 비번 초기화
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */

    public function forgetPassword(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        $params = $request->getAttribute('params');
        $cpName = $params['company-name'];


        // 회사명에 매칭된 email 주소 찾아서 처리
        $results['data']['message'] = 'success';
        return $response->withJson($results, JSON_UNESCAPED_UNICODE);
    }

    /**
     *
     * @param $userData
     *
     * @throws \Exception
     */
    private function _setSession($userData)
    {
        $key = $userData['email'];
        RedisUtil::setDataWithExpire($this->redis, CommonConst::REDIS_SESSION, $key, CommonConst::REDIS_SESSION_EXPIRE_TIME_MIN_60, $userData);

        $_SESSION['sess_user'] = [
            'sess_userid' => $userData['email'],
            'sess_user_info' => $userData,
            'selected_app_id' => null,
            'selected_app_name' => null
        ];

    }


}