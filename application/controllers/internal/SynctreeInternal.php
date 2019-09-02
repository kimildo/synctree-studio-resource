<?php

namespace controllers\internal;

use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Container\ContainerInterface;

use libraries\{
    log\LogMessage,
    constant\CommonConst,
    constant\ErrorConst,
    constant\TFConst,
    util\CommonUtil,
    util\RedisUtil
};

use Ramsey\Uuid\Uuid;

class SynctreeInternal
{
    protected $ci;
    protected $response;
    protected $request;
    protected $rdb;
    protected $redis;
    protected $lang;
    protected $dictionary;
    protected $renderer;
    protected $config;
    protected $jsonResult;
    protected $logger;

    public function __construct(ContainerInterface $ci)
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

    /**
     * 에러 메세지
     *
     * @param \Exception $ex
     * @param bool       $mailSend
     * @param array      $mailTemplate
     * @param string     $titlePrefix
     *
     * @return mixed
     */
    protected function _getErrorMessage(\Exception $ex, $mailSend = false, $mailTemplate = [], $titlePrefix = 'TF ')
    {

        $errMessage = CommonUtil::getErrorMessage($ex);
        if (true === $mailSend && !empty($mailTemplate)) {

            try {

//                $receiver = [
//                    'info@nntuple.com',
//                ];
//
//                if (false === \libraries\util\AwsUtil::sendEmail($receiver, ($mailTemplate['subject'] ?? 'Synctree Error'), ($mailTemplate['body'] ?? $errMessage))) {
//                    throw new \Exception(null, ErrorConst::ERROR_SEND_EMAIL);
//                }

                if (APP_ENV === APP_ENV_STAGING || APP_ENV === APP_ENV_PRODUCTION) {
                    if ($ex->getCode() !== ErrorConst::ERROR_RDB_NO_DATA_EXIST) {
                        CommonUtil::sendSlack('['. APP_ENV .']['. date('Y-m-d H:i:s') .'] ' . $titlePrefix . ($mailTemplate['subject'] ?? '') . ' :: ' . ($mailTemplate['body'] ?? $errMessage));
                    }
                }

            } catch (\Exception $ex) {
                LogMessage::error('Error Email send Fail');
            }

        }

        return $errMessage;
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
            if (false === CommonUtil::validateParams($params, ['event_key'], true)) {
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
     * Redis UUID
     *
     * @param array     $datas
     * @param float|int $expire
     *
     * @return string
     * @throws \Exception
     */
    protected function _getRedisEventKey($datas = [], $expire = CommonConst::REDIS_SESSION_EXPIRE_TIME_MIN_3)
    {
        $uuid = Uuid::uuid5(Uuid::NAMESPACE_DNS, session_id() . time());
        $eventKey = strtoupper('event-' . $uuid->toString());
        RedisUtil::setDataWithExpire($this->redis, CommonConst::REDIS_SECURE_PROTOCOL_COMMAND, $eventKey, $expire, $datas);

        return $eventKey;
    }

}