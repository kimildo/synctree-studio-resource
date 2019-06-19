<?php

namespace controllers\console;

use SebastianBergmann\CodeCoverage\Report\Xml\Project;
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


class SecureTest extends SynctreeConsole
{
    public function __construct(ContainerInterface $ci)
    {
        parent::__construct($ci);
    }

    public function secureTest(Request $request, Response $response, $args)
    {

        $results = $this->jsonResult;

        try {

            $params = $request->getAttribute('params');

            if (false === CommonUtil::validateParams($params, ['event_key'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            $resData = null;
            $resStatus = null;

            $env = $args['env'];
            $code = $args['code'];

            if (false === CommonUtil::validateParams($args, ['code'])) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            switch ($env) {
                case 'dev' :
                case 'stg' :
                    $targetUrl = 'https://'. $env .'.studio.synctreengine.com/Gen'. $code .'/secure/getCommand';
                    break;
                default :
                    $targetUrl = 'http://local.studio.synctreengine.com/Gen'. $code .'/secure/getCommand';
            }

            $options = [
                'verify' => false,
                'timeout' => 10,
                'allow_redirects' => true,
                'form_params' => [
                    'event_key' => $params['event_key'],
                ]
            ];


            try {

                $httpClient = new \GuzzleHttp\Client();
                $ret = $httpClient->request('POST', $targetUrl, $options);
                $resData = $ret->getBody()->getContents();
                $resData = strip_tags($resData);
                $resData = CommonUtil::getValidJSON($resData);
                $resStatus = $ret->getStatusCode() . ' ' . $ret->getReasonPhrase();

            } catch (\GuzzleHttp\Exception\ServerException $e) {
                preg_match('/`(5[0-9]{2}[a-z\s]+)`/i', $e->getMessage(), $output);
                $resStatus = $output[1];
                LogMessage::error('url :: ' . $targetUrl . ', error :: ' . $resStatus . ', options :: ' . json_encode($options, JSON_UNESCAPED_UNICODE));
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                preg_match('/`(4[0-9]{2}[a-z\s]+)`/i', $e->getMessage(), $output);
                $resStatus = $output[1];
                LogMessage::error('url :: ' . $targetUrl . ', error :: ' . $resStatus);
            } catch (\Exception $e) {
                $resStatus = "Name or service not known";
                LogMessage::error('url :: ' . $targetUrl . ', error :: ' . $resStatus);
            }

            $results = [];
            $results['res_secure_data'] = $resData['data']['params']['user_name'] ?? $resData['data']['params']['user_key'];
            $results['res_secure_status'] = $resStatus;

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