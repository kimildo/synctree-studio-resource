<?php
namespace controllers\console;

use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Container\ContainerInterface;

use libraries\{
    log\LogMessage,
    constant\CommonConst,
    constant\ErrorConst,
    util\RedisUtil,
    util\CommonUtil
};

use \models\redis\RedisKeys;

class SynctreeConsole
{
    protected $ci;
    protected $response;
    protected $request;
    protected $rdb;
    protected $redis;
    protected $csrf;
    protected $lang;
    protected $dictionary;
    protected $flash;
    protected $flashMessage;
    protected $renderer;
    protected $jsonResult;
    protected $viewData;
    protected $routeName;
    protected $config;
    protected $apps;
    protected $userPath;

    protected $accountEmail;
    protected $accountId;
    protected $teamId;

    public function __construct(ContainerInterface $ci)
    {
        $this->ci       = $ci;
        $this->csrf     = $ci->get('csrf');
        $this->rdb      = $ci->get('rdb');
        $this->redis    = $ci->get('redis');
        $this->flash    = $ci->get('flash');
        $this->renderer = $ci->get('renderer');

        $this->response = $ci->get('response');
        $this->request = $ci->get('request');

        $this->lang = $ci->get('lang');
        $this->dictionary = $ci->get('dictionary');
		$this->config = $ci->get('settings');

        $this->jsonResult = [
            'result' => ErrorConst::SUCCESS_STRING,
            'data' => [
                'message' => '',
            ]
        ];

        $this->userPath = (!empty($_SESSION['sess_user']) && isset($_SESSION['sess_user']['sess_userid']))
                        ? str_replace(['@', '.'], '_', $_SESSION['sess_user']['sess_userid'])
                        : null
                        ;

        $this->accountEmail = $_SESSION['sess_user']['sess_userid'] ?? null;
        $this->accountId = $_SESSION['sess_user']['sess_user_info']['account_id'] ?? null;
        $this->teamId = $_SESSION['sess_user']['sess_user_info']['team_id'] ?? null;

        if (isset($_SESSION['sess_user']) && !empty($_SESSION['sess_user'])) {
            $this->apps = $this->_getAppList();
        }

        $this->flashMessage = $this->flash->getMessages();
        $this->viewData = [
            'tracking_id'    => CommonUtil::getTrackingID(),
            'flash_message'  => $this->flashMessage,
            'dictionary'     => $this->dictionary,
            'SCRIPT_UPDATED' => CommonConst::SCRIPT_UPDATED,
            'CSS_UPDATED'    => CommonConst::CSS_UPDATED,
            'page_title'     => CommonConst::DEFAULT_PAGE_TITLE,
            'page_desc'      => CommonConst::DEFAULT_PAGE_DESC,
            'user_info'      => $_SESSION['sess_user'] ?? null,
            'app_id'         => $_SESSION['sess_user']['selected_app_id'] ?? null,
            'accountId'      => $this->accountId,
            'teamId'         => $this->teamId,
            'client_apps'    => $this->apps,
        ];

    }

    /**
     * csrf 체크
     *
     * @param $params
     *
     * @throws \Exception
     * @return mixed
     */
    protected function _checkCsrf($params)
    {
        if (isset($params['csrf_name']) === false || isset($params['csrf_value']) === false) {
            return false;
        }

        $this->csrf->validateStorage();

        /**
         * validate tokens
         */
        $result = $this->csrf->validateToken($params['csrf_name'], $params['csrf_value']);

        if (true !== $result) {
            return false;
        }

        return $result;
    }

    /**
     * csrf 토큰 추가
     * @param array $data
     *
     * @return array
     */
    protected function _addCsrfToken($data = [])
    {
        $this->csrf->validateStorage();

        /*
         * generate new tokens
         */
        $keyPair = $this->csrf->generateToken();

        $data['csrf_name'] = $keyPair['csrf_name'];
        $data['csrf_value'] = $keyPair['csrf_value'];

        return $data;
    }

    /**
     * 에러 메세지
     *
     * @param \Exception $ex
     * @param            $errGrCode
     *
     * @return mixed
     */
    protected function _getErrorMessage(\Exception $ex, $errGrCode = '')
    {
        if (!empty($errGrCode)) {
            $errArray = [
                'gr'            => $errGrCode,
                'account_id'    => $this->accountId,
                'account_email' => $this->accountEmail,
                'team_id'       => $this->teamId,
            ];
            LogMessage::error('Error Group :: '. json_encode($errArray, JSON_UNESCAPED_UNICODE));
        }

        return CommonUtil::getErrorMessage($ex);
    }

    /**
     * 라우트 그룹 이름
     *
     * @param Request $request
     *
     * @return mixed
     */
    protected function _getRouteName(Request $request)
    {
        return $request->getAttribute('route_name');
    }

    /**
     * 클라이언트의 앱 리스트를 가지고 온다.
     *
     * @return array
     */
    protected function _getAppList()
    {
        $apps = [];

        try {

            if (empty($this->teamId)) {
                return $apps;
            }

            $result = CommonUtil::getRedisData($this->ci,
                CommonConst::CLIENT_APP_LIST_REDIS_KEY . $this->teamId . $this->accountId,
                'executeGetAppList', [
                    'account_id'   => $this->accountId,
                    'team_id'      => $this->teamId,
                    'archive_flag' => 0,
            ]);

            $apps = $result[1] ?? [];

            if (!empty($apps)) {
                if ( !isset($_SESSION['sess_user']['selected_app_id']) || empty($_SESSION['sess_user']['selected_app_id'])) {
                    $_SESSION['sess_user']['selected_app_id'] = $apps[0]['application_id'];
                    $_SESSION['sess_user']['selected_app_name'] = $apps[0]['application_name'];
                }
            } else {
                $_SESSION['sess_user']['selected_app_id'] = null;
                $_SESSION['sess_user']['selected_app_name'] = null;
            }

        } catch (\Exception $ex) {
            $this->_getErrorMessage($ex);
        }

        return $apps;

    }

    /**
     * json 파일 가지고 오기
     *
     * @param string $fileName
     *
     * @return array
     * @deprecated DB로 교체
     *
     */
    protected function _getJsonFile($fileName = 'apps')
    {
        try {
            $jsonfile = json_decode(file_get_contents(APP_DIR . 'templates/usr/' . $this->userPath . '/' . $fileName .'.json'), true);
        } catch (\Exception $ex) {
            LogMessage::debug('Get Json failed (_getJsonFile()). Not exist '. $fileName .'.json File');
            $jsonfile = [
                $fileName => null
            ];
        }

        return $jsonfile[$fileName];
    }

    /**
     * json 파일 쓰기
     *
     * @param $contents
     * @param $fileName
     *
     * @return bool
     * @throws \Exception
     *
     * @deprecated DB로 교체
     */
    protected function _writeJsonFile($contents, $fileName)
    {
        try {

            $json = [
                $fileName => $contents
            ];

            $filePath = APP_DIR . 'templates/usr/' . $this->userPath . '/';

            if (!file_exists($filePath)) {
                mkdir($filePath, 0700, true);
            }

            $file = $filePath . $fileName .'.json';
            LogMessage::debug("file path :: " . $file );

            $jsonfile = fopen($file, 'w');
            fwrite($jsonfile, json_encode($json, JSON_UNESCAPED_UNICODE));
            fclose($jsonfile);

        } catch (\Exception $ex) {
            LogMessage::error($ex->getMessage());
            throw $ex;
        }

        return true;
    }

    /**
     * 업데이트 체크
     *
     * @return array|bool|mixed|string
     */
    protected function _checkUpdateApps()
    {
		$key = RedisKeys::getAlarmKey($this->userPath);
        try {
			$data = RedisUtil::getData($this->redis, $key, CommonConst::REDIS_MESSAGE_SESSION);
        } catch (\Exception $ex) {
            LogMessage::debug('Redis getData error. key : '. $key . ' message : ' . $this->_getErrorMessage($ex));
            $data = [];
        }

        return $data;
    }

    /**
     * 에러 발생시 랜더 뷰
     *
     * @param       $message
     * @param array $params
     *
     * @return mixed
     */
    protected function _viewErrorMessage($message, $params = [])
    {
        return $this->renderer->render($this->response, 'message.twig', [
                'message'       => $message,
                'message_title' => '페이지에 오류가 있습니다.',
                'params'        => $params,
            ] + $this->viewData);
    }

    /**
     * @param      $key
     * @param bool $delAlarm
     *
     * @throws \Exception
     */
    protected function _delRedisForBiz($key, $delAlarm = false)
    {
        $db = CommonConst::REDIS_CLIENT_SESSION;
        RedisUtil::delData($this->redis, CommonConst::CLIENT_BIZ_LIST_REDIS_KEY . $this->teamId . $this->accountId . $key['app_id'], $db);
        RedisUtil::delData($this->redis, CommonConst::CLIENT_BIZ_REDIS_KEY . $key['biz_id'], $db);
        RedisUtil::delData($this->redis, CommonConst::CLIENT_BIND_OPS_LIST_REDIS_KEY . $key['biz_id'], $db);

        if ( ! empty($delAlarm)) {
            $alarmKey = RedisKeys::getAlarmKey($this->userPath);
            RedisUtil::delData($this->redis, $alarmKey, CommonConst::REDIS_MESSAGE_SESSION);
        }
    }

    /**
     * 오퍼레이션 관련 redis 삭제
     * @param $params
     */
    protected function _delRedisForOperation($params)
    {

    }

}