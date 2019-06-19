<?php
/**
 * 생성된 파일의 부모 클래스
 * 이 클래스는 추상 클래스를 부모로 둔다.
 *
 * @author kimildo
 */

namespace controllers\generated;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use libraries\{
    constant\CommonConst,
    constant\ErrorConst,
    constant\TFConst,
    log\LogMessage,
    util\CommonUtil,
    util\RedisUtil,
    util\AwsUtil,
    util\AppsUtil
};

use Ramsey\Uuid\Uuid;

abstract class Synctree
{
    protected $ci;
    protected $response;
    protected $logger;
    protected $renderer;
    protected $redis;
    protected $jsonResult;
    protected $httpClient;
    protected $promise;
    protected $promiseResponseData;
    protected $asyncReturnDatas;
    protected $httpReqTimeout = 5;
    protected $httpConnectTimeout = 5;
    protected $httpReqVerify = false;

    /**
     * Synctree constructor.
     *
     * @param Container $ci
     *
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function __construct(Container $ci)
    {
        $this->ci = $ci;

        try {

            $this->ci = $ci;
            $this->logger = $ci->get('logger');
            $this->renderer = $ci->get('renderer');
            $this->response = $ci->get('response');
            $this->redis = $ci->get('redis');

        } catch (\Exception $ex) {
            LogMessage::error($ex->getMessage());
        }

        $this->jsonResult = [
            'result' => ErrorConst::SUCCESS_CODE,
            'data'   => [
                'message' => '',
            ]
        ];
    }

    // abstract functions
    public abstract function main(Request $request, Response $response, $args);
    //public abstract function docs(Request $request, Response $response);
    //public abstract function getSampleCodes(Request $request, Response $response);


    /**
     * 보안 프로토콜
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function getCommand(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            $params = $request->getAttribute('params');
            if (false === CommonUtil::validateParams($params, ['event_key'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            // get redis data //
            if (APP_ENV === APP_ENV_PRODUCTION || APP_ENV === APP_ENV_STAGING) {
                if (false === ($redisData = RedisUtil::getDataWithDel($this->redis, $params['event_key'], CommonConst::REDIS_SECURE_PROTOCOL_COMMAND))) {
                    throw new \Exception(null, ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                }
            } else {
                if (false === ($redisData = RedisUtil::getData($this->redis, $params['event_key'], CommonConst::REDIS_SECURE_PROTOCOL_COMMAND))) {
                    throw new \Exception(null, ErrorConst::ERROR_RDB_NO_DATA_EXIST);
                }
            }

            $results = [
                'result' => true,
                'data'   => $redisData
            ];

        } catch (\Exception $ex) {
            $results = [
                'result' => false,
                'data'   => [
                    'message' => $this->_getErrorMessage($ex),
                ]
            ];
        }

        return $response->withJson($results, ErrorConst::SUCCESS_CODE);
    }

    /**
     * 샘플코드 메소드
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return object
     */
    public function getSampleCodes(Request $request, Response $response): object
    {
        $results = $this->jsonResult;

        try {

            if (empty($request->isXhr())) {
                return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
            }

            $params = $request->getAttribute('params');

            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id', 'target_url',  'snipet'])) {
                LogMessage::error('Not found required field');
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            // 캐시된 도큐먼트 데이터 Redis
            //$redisDb = CommonConst::REDIS_BIZ_RES_SESSION;
            //$redisKey = CommonConst::BIZ_DOCS_CACHE . $params['biz_id'] . $params['op_id'] . $params['snipet'];

            //if (false !== ($bizDocsInfo = RedisUtil::getData($this->redis, $redisKey, $redisDb))) {
                //$results['data'] = $bizDocsInfo;
                //return $response->withJson($results, ErrorConst::SUCCESS_CODE);
            //}

            $snipets = CommonConst::SAMPLE_SOURCE_TYPE;
            $snipet = $snipets[$params['snipet']] ?? null;

            if (empty($snipet)) {
                LogMessage::error('Not found required field');
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            // 비즈 메타데이터 Redis
            $redisKey = CommonConst::CLIENT_BIZ_BUILD_REDIS_KEY . $params['biz_id'];
            $redisDb = CommonConst::REDIS_CLIENT_SESSION;

            if (false === ($bizOpsInfo = RedisUtil::getData($this->redis, $redisKey, $redisDb))) {
                throw new \Exception(null, ErrorConst::ERROR_RDB_NO_DATA_EXIST);
            }

            $results['data'] = AppsUtil::getSecureSampleSource($params['snipet'], $params['target_url'], $bizOpsInfo);


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
     * Redis UUID
     *
     * @param $datas
     *
     * @return bool
     * @throws \Exception
     */
    protected function _getRedisEventKey($datas = [])
    {
        $uuid = Uuid::uuid5(Uuid::NAMESPACE_DNS, session_id() . time());
        $eventKey = strtoupper('event-' . $uuid->toString());
        RedisUtil::setDataWithExpire($this->redis, CommonConst::REDIS_SECURE_PROTOCOL_COMMAND, $eventKey, CommonConst::REDIS_SESSION_EXPIRE_TIME_MIN_5, $datas);

        return $eventKey;
    }


    /**
     * 역방향 보안프로토콜을 위한 리스너
     *
     * @param $params
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function _eventListener($params)
    {
        $result = false;

        if (false === CommonUtil::validateParams($params, ['target_url', 'event_key'])) {
            return $result;
        }

        $options = [
            'verify'          => $this->httpReqVerify,
            'timeout'         => $this->httpReqTimeout,
            'connect_timeout' => $this->httpConnectTimeout,
            'json'            => ['event_key' => $params['event_key']]
        ];

        $result = $this->_httpRequest($params['target_url'], $options);

        $resStatus = $result['res_status'];
        LogMessage::info('_eventListener :: ' . $resStatus);

        return $result;
    }

    /**
     * guzzle request
     *
     * @param        $targetUrl
     * @param        $options
     * @param string $method
     * @param bool   $exceptTag
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function _httpRequest($targetUrl, $options, $method = CommonConst::REQ_METHOD_POST, $exceptTag = false)
    {
        $resData = null;
        $resStatus = null;
        $resServErr = true;
        $resStatus = 'Name or service not known';

        try {

            if (empty($this->httpClient) || ! is_object($this->httpClient)) {
                $this->httpClient = new \GuzzleHttp\Client();
            }

            $ret = $this->httpClient->request($method, $targetUrl, $options);
            $resData = $ret->getBody()->getContents();
            $resData = (!empty($exceptTag)) ? strip_tags($resData) : $resData;
            $resData = CommonUtil::getValidJSON($resData);
            $resStatus = $ret->getStatusCode() . ' ' . $ret->getReasonPhrase();
            $resServErr = false;

        } catch (\GuzzleHttp\Exception\ServerException $e) {
            preg_match('/(5[0-9]{2}[a-z\s]+)/i', $e->getMessage(), $output);
            $resStatus = $output[1];
            LogMessage::error('url :: ' . $targetUrl . ', error :: ' . $resStatus . ', options :: ' . json_encode($options, JSON_UNESCAPED_UNICODE));
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            preg_match('/(4[0-9]{2}[a-z\s]+)/i', $e->getMessage(), $output);
            $resStatus = $output[1];
            LogMessage::error('url :: ' . $targetUrl . ', error :: ' . $resStatus);
        } catch (\Exception $e) {
            LogMessage::error('url :: ' . $targetUrl . ', error :: ' . $e->getMessage());
        }

        if (true === $resServErr) {
            throw new \Exception(null, ErrorConst::ERROR_OPERATOR_SERVER);
        }

        return [
            'res_status' => $resStatus,
            'res_data'   => $resData,
        ];
    }

    /**
     * 비동기 호출
     *
     * @param array $asyncRequests
     * @param int $concurrency
     * @param bool $wait if wait === true then promise else full async
     *
     * @return array
     *
     */
    protected function _httpAsyncRequest(array $asyncRequests, $wait = true, $concurrency = 5)
    {
        $this->promiseResponseData = [];

        try {

            if (empty($this->httpClient) || ! is_object($this->httpClient)) {
                $this->httpClient = new \GuzzleHttp\Client();
            }

            if (true === $wait) {
                $this->promiseResponseData = $this->_sendPromiseAsync($asyncRequests, $concurrency);
            } else {
                $this->promiseResponseData = $this->_sendFullAsync($asyncRequests);
            }

        } catch (\Exception $e) {
            LogMessage::error('');
        }

        return $this->promiseResponseData;

    }

    /**
     * async case 3 Promise Async
     *
     * @param $asyncRequests
     * @param $concurrency
     *
     * @return array
     */
    private function _sendPromiseAsync($asyncRequests, $concurrency)
    {
        $this->asyncReturnDatas = [];
        $requestPromises = function ($targets) {
            foreach ($targets as $target) {
                yield function() use ($target) {
                    $target['options']['verify'] = $this->httpReqVerify;
                    $target['options']['timeout'] = $this->httpReqTimeout;
                    $target['options']['connect_timeout'] = $this->httpConnectTimeout;
                    return $this->httpClient->requestAsync($target['method'], $target['url'], $target['options']);
                };
            }
        };

        $pool = new \GuzzleHttp\Pool($this->httpClient, $requestPromises($asyncRequests), [
            'concurrency' => $concurrency,
            'fulfilled'   => function ($response, $index) {
                $resData = json_decode($response->getBody()->getContents(), true);
                //$this->asyncReturnDatas['seq'][] = $index;
                $this->asyncReturnDatas['response'][] = $resData;
            },
            'rejected'    => function ($reason, $index) {

            },
        ]);

        $promise = $pool->promise();
        $promise->wait();

        return $this->asyncReturnDatas;

    }


    /**
     * async case 2 Full Async
     *
     * @param $asyncRequests
     *
     * @return array|bool
     */
    private function _sendFullAsync($asyncRequests)
    {

        foreach ($asyncRequests as $index => $target) {
            $url = $target['url'];
            $command = 'curl ';

            if ($target['method'] === CommonConst::REQ_METHOD_POST && !empty($target['options']['form_data'])) {
                $command .= '-d "' . http_build_query($target['options']['form_data']) . '" ';
            }

            $command .= $url . ' -s > /dev/null 2>&1 &';
            LogMessage::debug('curl command :: ' . $command);
            passthru($command);
        }

        return true;

//        $ch = $results = [];
//        $mh = curl_multi_init();
//        $running = 0;
//
//        shuffle($asyncRequests);
//        foreach ($asyncRequests as $index => $target) {
//
//            //$results['seq'][] = $index;
//            $ch[$index] = curl_init($target['url']);
//
//            curl_setopt($ch[$index], CURLOPT_NOBODY, true);
//            curl_setopt($ch[$index], CURLOPT_HEADER, true);
//
//            if ($target['method'] === CommonConst::REQ_METHOD_POST) {
//                curl_setopt($ch[$index], CURLOPT_POST, true);
//                curl_setopt($ch[$index], CURLOPT_POSTFIELDS, $target['options']['form_data']);
//            }
//
//            curl_setopt($ch[$index], CURLOPT_RETURNTRANSFER, true);
//            curl_setopt($ch[$index], CURLOPT_TIMEOUT_MS, 500);
//
//            curl_multi_add_handle($mh, $ch[$index]);
//        }
//
//        do {
//            curl_multi_exec($mh, $running);
//            curl_multi_select($mh);
//        } while ($running > 0);
//
//        foreach (array_keys($ch) as $key) {
//            $results['result'][] = [
//                'url' => curl_getinfo($ch[$key], CURLINFO_EFFECTIVE_URL),
//                'code' => curl_getinfo($ch[$key], CURLINFO_HTTP_CODE)
//            ];
//            curl_multi_remove_handle($mh, $ch[$key]);
//        }
//
//        curl_multi_close($mh);
//
//        return $results;

    }


    /**
     * 로그 S3 업로드
     * @todo 추후 로그서버를 별도로 구축, 비동기로 전환
     *
     * @param $fileName
     * @param $contents
     * @param $appId
     * @param $bizId
     * @param $timestamp
     *
     * @return bool
     */
    protected function _saveLog($fileName, $contents, $appId, $bizId, $timestamp = null)
    {
        $result = true;

        try {

            $curDateTime = $timestamp ?? date('Y-m-d H:i:s');

            $filePath = BASE_DIR . '/logs/biz/';
            $fileName = $fileName . '.' . date('Ymd', strtotime($curDateTime)) . '.log';
            $file = $filePath . $fileName;

            $logfile = fopen($file, 'a');
            fwrite($logfile, '[' . $curDateTime . ' - ' . CommonUtil::getUserIP() . '] ' . $contents . "\n\n");
            fclose($logfile);

            if (APP_ENV === APP_ENV_PRODUCTION) {
                $s3FileName = date('Y/m/d', strtotime($curDateTime));
                $s3FileName .= '/' . $appId . '/' . $bizId . '/' . $fileName;

                if (true === ($s3Result = AwsUtil::s3FileUpload($s3FileName, $file, 's3Log'))) {
                    @unlink($file);
                }
            }

        } catch (\Exception $ex) {
            $result = false;
        }

        return $result;
    }

    /**
     * 에러메세지 출력
     *
     * @param \Exception $ex
     *
     * @return array
     */
    protected function _getErrorMessage(\Exception $ex)
    {
        $results = [
            'result' => ErrorConst::FAIL_CODE,
            'data'   => [
                'error_code' => $ex->getCode(),
                'message' => CommonUtil::getErrorMessage($ex),
            ]
        ];

        return $results;
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
            ]);
    }

}