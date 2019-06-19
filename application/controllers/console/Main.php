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
    util\GenerateUtil
};

class Main extends SynctreeConsole
{
    public function __construct(ContainerInterface $ci)
    {
        parent::__construct($ci);
        $this->viewData['user_info'] = $_SESSION['sess_user'];

    }
	
	public function index(Request $request, Response $response)
    {
        try {

            $this->viewData['route_name'] = $this->_getRouteName($request);
			
			
            $this->renderer->render($response, 'console/index.twig', $this->viewData);

        } catch (\Exception $ex) {
            $this->_getErrorMessage($ex);
        }

        return $response;
    }

    public function dashboard(Request $request, Response $response)
    {
        try {

            $this->viewData['route_name'] = $this->_getRouteName($request);
            $this->renderer->render($response, 'console/dashboard.twig', $this->viewData);

        } catch (\Exception $ex) {
            $this->_getErrorMessage($ex);
        }

        return $response;
    }

    /**
     * 앱 목록
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function list(Request $request, Response $response)
    {
        try {

            $this->viewData['page_title'] = 'Apps';
            $this->viewData['route_name'] = $this->_getRouteName($request);
            $this->viewData['apps'] =$this->_getJsonFile('apps')['10001'];
            $this->renderer->render($response, 'console/apps.twig', $this->viewData);

        } catch (\Exception $ex) {
            $this->_getErrorMessage($ex);
        }

        return $response;
    }

    /**
     * 앱 추가
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

            if (false === CommonUtil::validateParams($params, ['app_id'])) {
                LogMessage::error('Not found required field [field:app_id]');
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            $oldApps = $this->_getJsonFile('apps');
            $incApp = $oldApps[$params['app_id']];
            unset($oldApps[$params['app_id']]);

            $seq = count($incApp) + 1;
            array_push($incApp, [
                'seq'       => $seq,
                'name'      => $params['app_name'],
                'desc'      => $params['app_desc'],
                'reg_date'  => date('Y-m-d H:i:s'),
                'connetors' => [],
            ]);

            $incApp = [
                $params['app_id'] => $incApp
            ];

            $appArray = $incApp + $oldApps;
            ksort($appArray);

            //LogMessage::debug('old jason :: ' . json_encode($oldApps));
            //LogMessage::debug('edit jason :: ' . json_encode($incApp));

            $appArray = [
                'apps' => $appArray
            ];

            $this->_writeJsonFile($appArray, CommonConst::APPS_FILE_NAME);
            LogMessage::debug('new jason :: ' . json_encode($appArray));

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



    public function opList(Request $request, Response $response)
    {
        try {

            $this->viewData['route_name'] = $this->_getRouteName($request);
            $this->renderer->render($response, 'console/dashboard.twig', $this->viewData);

        } catch (\Exception $ex) {
            $this->_getErrorMessage($ex);
        }

       return $response;
    }




}