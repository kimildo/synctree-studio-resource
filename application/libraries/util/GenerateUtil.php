<?php
/**
 * Studio PHP Generate Engine
 *
 * @author kimildo
 * @see https://packagist.org/packages/nette/php-generator
 *
 */

namespace libraries\util;

use libraries\{
    constant\CommonConst,
    constant\GeneratorConst,
    util\CommonUtil,
    log\LogMessage
};

use Nette\PhpGenerator as PGen;
use SebastianBergmann\CodeCoverage\Report\PHP;

class GenerateUtil
{
    private $config;
    private $fileObj;
    private $fileInfo;
    private $params;
    private $userPath;
    private $class;
    private $error = false;
    private $asyncType = 0;
    private $ci;

    /**
     * GenerateUtil constructor.
     *
     * @param       $ci
     * @param array $params
     */
    public function __construct($ci, $params = [])
    {
        $configFile = include APP_DIR . 'config/' . APP_ENV . '.php';
        $this->config = $configFile['settings']['home_path'] ?? null;

        $this->fileObj = new PGen\PhpFile;
        $this->fileObj->addComment('This file is Created by Ntuple GeneratedEngine.');
        $this->fileObj->addComment(date('Y-m-d H:i:s'));

        $this->params = $params;
        $this->userPath = str_replace(['@', '.'], '_', $params['user_id']);

        $this->ci = $ci;
    }

    /**
     * Controller 파일 생성
     *
     * @return $this
     * @throws \Exception
     */
    public function setControllerFile()
    {
        if (false !== $this->error) {
            return $this;
        }

        $className = str_replace('.php', '', $this->fileInfo['file_name']);

        $namespace = $this->fileObj->addNamespace('controllers\\generated\\usr\\' . $this->userPath);
        $namespace
            ->addUse('Slim\Http\Request')
            ->addUse('Slim\Http\Response')
            ->addUse('Slim\Container')
            ->addUse('libraries\constant\CommonConst')
            ->addUse('libraries\constant\ErrorConst')
            ->addUse('libraries\log\LogMessage')
            ->addUse('libraries\util\CommonUtil')
            ->addUse('libraries\util\RedisUtil')
            //->addUse('libraries\util\AppsUtil')
            ->addUse('libraries\util\GenerateCustomUtil')
            ->addUse('controllers\generated\Synctree')
            //->addUse('Ramsey\Uuid\Uuid')
            //->addUse('Ramsey\Uuid\Exception\UnsatisfiedDependencyException')
        ;

        $this->class = $namespace->addClass($className)->setExtends('Synctree');
        $this->class->addConstant('BIZOPS', json_encode($this->params, JSON_UNESCAPED_UNICODE));
        //$class->addConstant('BUNIT_TOKEN', $this->params['token']);

        //$this->class->addProperty('eventKey')->setVisibility('private');
        $this->class->addProperty('resultParams')->setVisibility('private');
        $this->class->addProperty('params')->setVisibility('private');

        $this->class->addMethod('__construct')->setBody('parent::__construct($ci);')->addParameter('ci')->setTypeHint('Container');

        $mainMethod = $this->class->addMethod(GeneratorConst::GEN_MAIN_METHOD_NAME)->setReturnType('object');
        $mainBody = <<<'SOURCE'
$result = [];
$errorFlag = false;
$result['result'] = ErrorConst::SUCCESS_STRING;

$requestUri = $request->getAttribute('method_name');
$startDate = CommonUtil::getDateTime();
$startTime = CommonUtil::getMicroTime();
$startTimeStemp =  $startDate . ' ' . $startTime;

$bizOps = (true === CommonUtil::isValidJSON(static::BIZOPS)) ? json_decode(static::BIZOPS, true) : [];

SOURCE;

        $mainBody .= PHP_EOL . '//Redis Key' . PHP_EOL;
        $mainBody .= '$redisDb = CommonConst::REDIS_BIZ_RES_SESSION;' . PHP_EOL;
        $mainBody .= '$redisKey = CommonConst::BIZ_RESPONSE_CACHE . \'' . $this->params['biz_id'] . '\';' . PHP_EOL . PHP_EOL;

        $mainBody .= <<<'SOURCE'
try {

    $header = $request->getHeaders();
    $parameters = $request->getAttribute('params');
    switch (true) {
        case (is_array($parameters) && is_array($args)) :
            $this->params = array_merge($parameters, $args);
            break;
        case (is_array($parameters) && !empty($parameters)) :
            $this->params = $parameters;
            break;
        default :
            $this->params = $args;
            break;
    }

SOURCE;

        $mainBody .= PHP_EOL;
        $requiredRequest = [];
        $requestEncode = '';

        // 비즈유닛 Response Redis 대응
        //$mainBody .= "\t" . '$redisKeyHash = \'\'' . PHP_EOL;
        //foreach ($this->params['request'] as $req) {
            //$mainBody .= "\t\t" . '. ((is_array(($this->params[\''. $req['req_key'] .'\'] ?? null))) ? json_encode($this->params[\''. $req['req_key'] .'\']) : $this->params[\''. $req['req_key'] .'\'])' . PHP_EOL;
        //}
        //$mainBody .= "\t\t" . ';' . PHP_EOL . PHP_EOL;
        //$mainBody .= "\t" . 'if (!empty($redisKeyHash)) $redisKey .= \'_\' . md5($redisKeyHash);' . PHP_EOL . PHP_EOL;


        foreach ($this->params['request'] as $req) {
            if (!empty($req['required_flag'])) {
                $requiredRequest[] = $req['req_key'];
            }
            if ($req['req_var_type'] === CommonConst::VAR_TYPE_JSON) {
                $requestEncode .= "\t" . 'if (isset($this->params[\'' . $req['req_key'] . '\'])) { ' . PHP_EOL;
                $requestEncode .= "\t\t" . '$this->params[\'' . $req['req_key'] . '\'] = CommonUtil::getValidJSON($this->params[\'' . $req['req_key'] . '\']);' . PHP_EOL;
                $requestEncode .= "\t" . '}' . PHP_EOL;
            }
        }

        if (!empty($requiredRequest)) {
            $requiredRequest = "'" . implode("', '", $requiredRequest) . "'";
            $mainBody .= "\t" . 'if (false === CommonUtil::validateParams($this->params, [' . $requiredRequest . '], true)) {' . PHP_EOL;
            $mainBody .= "\t\t" . 'LogMessage::error(\'Not found required field\');' . PHP_EOL;
            $mainBody .= "\t\t" . 'throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);' . PHP_EOL;
            $mainBody .= "\t" . '}' . PHP_EOL . PHP_EOL;
        }

        $mainBody .= "\t" . '$redisKey .= \'_\' . md5(APP_ENV . json_encode($this->params));' . PHP_EOL;
        $mainBody .= <<<'SOURCE'
        
    $resultData = [];
	$responseDatas = [];
    $resultData['request'] = $this->params;
SOURCE;

        $mainBody .= PHP_EOL . $requestEncode;

        $mainBodyArr = [];
        $controlInfoArr = [];
        $asyncArr = [];

        // async가 설정되어 있으면 타입을 설정한다.
        if (isset($this->params['async_bind_seq'])) {
            switch (true) {
                case (empty($this->params['async_bind_seq'][2])) :
                    $this->asyncType = 2; break;
                case (!empty($this->params['async_bind_seq'][2])) :
                    $this->asyncType = 3; break;
                default :
                    $this->asyncType = 1;
            }
        }

        // 비즈유닛 Response Redis 대응
        $resultTabSpace = "\t";
        if (!empty($this->params['cache_flag'])) {
            $mainBody .= <<<'SOURCE'
    
    if (true == $this->redis->exist($redisDb, $redisKey)) {
        $responseDatas = RedisUtil::getData($this->redis, $redisKey, $redisDb);
        $this->resultParams = array_pop($responseDatas['sync']);
    } else {

SOURCE;
            $resultTabSpace = "\t\t";

        } elseif (empty($this->params['cache_flag'])) {
            $mainBody .= <<<'SOURCE'

    if (true == $this->redis->exist($redisDb, $redisKey)) {
        RedisUtil::delData($this->redis, $redisKey, $redisDb);
    }


SOURCE;
        }

        // 모든 오퍼레이터 서브메소드/async 생성
        foreach ($this->params['operators'] as $opSeq => $row) {

            $bindSeq = $opSeq;
            $controlInfo = null;
            $async = false;

            if (!empty($row['control_container_code'])) {
                $controlInfo = json_decode($row['control_container_info'], true);
                $controlIndex = array_search($controlInfo['control_id'], array_column($this->params['controls'], 'control_alt_id'));
                $bindSeq = $this->params['controls'][$controlIndex]['binding_seq'];
            }

            $subMethodName = GeneratorConst::GEN_SUB_METHOD_PREFIX . strtoupper(str_replace(['-', '_'], '', $row['operation_key'])) . $opSeq;
            $resSourceText = $resultTabSpace . '$responseDatas[\'sync\'][] = $this->resultParams = $this->' . $subMethodName . '();';

            if (isset($this->params['async_bind_seq']) && ($row['binding_seq'] >= $this->params['async_bind_seq'][0] && $row['binding_seq'] <= $this->params['async_bind_seq'][1])) {
                // ASYNC 용 서브함수내 내용 생성
                $async = true;
                $asyncArr[$row['binding_seq']] = $this->_makeAsyncSource($row);

                // Async 가 promise 일 경우
                if ($this->asyncType === 3) {
                    $mainBodyArr[$this->params['async_bind_seq'][0]] = "\t" . '$responseDatas[\'async\'][] = $this->resultParams = $this->_ASYNC('. $this->asyncType .');';
                }

            } elseif (!empty($row['control_container_code'])) {
                // alt
                $mainBodyArr[$bindSeq][$opSeq] = $resSourceText;
                $controlInfoArr[$opSeq] = $controlInfo;
            } else {
                $mainBodyArr[$opSeq] = $resSourceText;
            }

            // 오퍼레이터 별 서브함수 생성 / async 일 경우 생성 안함
            if (empty($async)) {
                if (false === $this->_makeSubfuntion($subMethodName, $row, $opSeq)) {
                    $this->error = 'Error While Make Subfunction';
                    LogMessage::error($this->error);
                    return $this;
                }
            }

        } // end of $operation foreach


        // 메인메소드 조건문 or Response 호출 생성
        $controlOperators = CommonConst::CONTROLL_OPERATORS;
        foreach ($mainBodyArr as $key => $row) {
            // if alter
            if (is_array($row)) {

                $mainBody .= PHP_EOL . "\t" . 'switch (true) {' . PHP_EOL;
                foreach ($row as $opSeq => $source) {

                    $controlInfo = $controlInfoArr[$opSeq];
                    $controlIndex = array_search($controlInfo['control_id'], array_column($this->params['controls'], 'control_alt_id'));

                    $parameterKeyName = $this->params['controls'][$controlIndex]['parameter_key_name'];
                    $parameterJson = (!empty($this->params['controls'][$controlIndex]['sub_parameter_path']))
                                   ? $this->_jsonPathToArrayString($this->params['controls'][$controlIndex]['sub_parameter_path']) : '';

                    $parameterFrom = (!empty($this->params['controls'][$controlIndex]['biz_ops_id'])) ? '$this->params' : '$this->resultParams[\'response\']';
                    $targetValue = (is_numeric($controlInfo['value'])) ? (int)$controlInfo['value'] : '\'' . $controlInfo['value'] . '\'';

                    //if ($key === array_key_last($row)) {
                    //    $mainBody .= '        default : ' . PHP_EOL . '      ' . $source . PHP_EOL;
                    //} else {
                    $mainBody .= "\t\t" . 'case ('. $parameterFrom .'[\'' . $parameterKeyName . '\']' . $parameterJson . ' ' . $controlOperators[$controlInfo['operator']]
                              . ' ' . $targetValue . ') :'  . PHP_EOL
                              . '          ' . $source . PHP_EOL
                              . '              break;' . PHP_EOL
                              ;
                    //}
                }

                $mainBody .= "\t\t" . 'default : ' . PHP_EOL . '      ' . PHP_EOL;
                $mainBody .= "\t" . '}' . PHP_EOL;

            } else {
                $mainBody .= $row . PHP_EOL;
            }
        }

        // async 서브함수 생성
        if (!empty($asyncArr)) {

            if (false === $this->_makeAsyncSubfuntion('_ASYNC', $asyncArr)) {
                $this->error = 'Error While Make ASYNC Subfunction';
                LogMessage::error($this->error);
                return $this;
            }

            switch ($this->asyncType) {
                case 1 :
                case 2 :
                    $mainBody .= "\t\t" . '$responseDatas[\'async\'][] = $this->resultParams = $this->_ASYNC('. $this->asyncType .');';
                    break;
            }

        }

        $mainBody .= PHP_EOL;

        // 캐시 플래그가 1 이면
        if (!empty($this->params['cache_flag'])) {

            //시간이 설정되어 있으면
            if (!empty($this->params['cache_expire_time'])) {
                $mainBody .= "\t\t" . '$expireTime = '. $this->params['cache_expire_time'] .' * 60;' . PHP_EOL;
                $mainBody .= "\t\t" . 'RedisUtil::setDataWithExpire($this->redis, $redisDb, $redisKey, $expireTime, $responseDatas);' . PHP_EOL;
            } else {
                $mainBody .= "\t\t" . 'RedisUtil::setData($this->redis, $redisDb, $redisKey, $responseDatas);' . PHP_EOL;
            }

            $mainBody .= "\t" . '}' . PHP_EOL . PHP_EOL;
        }

        $mainBody .= <<<'SOURCE'
    $resultData['responses'] = $responseDatas;

} catch (\Exception $ex) {
    $errorFlag = true;
    $result = $this->_getErrorMessage($ex);
}

$result['uri'] = $requestUri;
$result['user_ip'] = CommonUtil::getUserIP();
$result['timestamp']['start'] = $startTimeStemp;

if (false === $errorFlag) {
    $result['data'] = $resultData;
}

$endTime = CommonUtil::getMicroTime();
$result['timestamp']['end'] = CommonUtil::getDateTime() . ' ' . $endTime;
$result['timestamp']['runtime'] = $endTime - $startTime;

try {
    $this->_saveLog('logBiz-' . $bizOps['biz_id'] . '-access', json_encode($result, JSON_UNESCAPED_UNICODE), $bizOps['app_id'], $bizOps['biz_id'], $startDate);
} catch (\Exception $ex) {
    LogMessage::error('Save Log Fail (biz:: ' . $bizOps['biz_id'] . ') - ' . json_encode($this->_getErrorMessage($ex), JSON_UNESCAPED_UNICODE));
}

unset($result['user_ip']);
unset($result['uri']);
unset($result['timestamp']);

if (APP_ENV !== APP_ENV_DEVELOPMENT_LOCAL_KIMILDO && APP_ENV !== APP_ENV_DEVELOPMENT_LOCAL
    && false === $errorFlag && $header['HTTP_USER_AGENT'][0] !== 'Synctree-Studio-Test' ) {
    $result = $this->resultParams['response'];
}

LogMessage::info('Removed gc :: ' . gc_collect_cycles());
return $response->withJson($result, ErrorConst::SUCCESS_CODE);


SOURCE;

        //$mainBody .= PHP_EOL . PHP_EOL . 'return $response->withJson($result, ErrorConst::SUCCESS_CODE);';
        $mainMethod->addComment($this->params['biz_name'] . " Main Method\n");

        $mainBody = str_replace("\t", '    ', $mainBody);
        $mainMethod->setBody($mainBody);
        $mainMethod->addParameter('request')->setTypeHint('Request');
        $mainMethod->addParameter('response')->setTypeHint('Response');

        $mainMethod->addComment("@param Request \$request\n");
        $mainMethod->addComment("@param Response \$response\n");

        //if ($this->params['req_method'] === CommonConst::REQ_METHOD_GET_CLEANURL_CODE) {
            $mainMethod->addParameter('args');
            $mainMethod->addComment("@param args \$args\n");
        //}

        $mainMethod->addComment("@return Response\n");
        $mainMethod->addComment("@throws \\Exception\n");

        // API 문서 메서드
        //$this->_setDocsMehod();

        // 샘플코드 메소드
        //$this->_setSampleCodesMehod();

        return $this;

    }

    /**
     * 라우트 파일 소스 생성
     *
     * @return $this
     */
    public function setRouteFile()
    {
        if (false !== $this->error) {
            return $this;
        }

        $uri = '';
        $controllerPath = '\'' . GeneratorConst::GEN_CONTOLLER_USR_ROUTE_DIRECTORY . $this->userPath . '\\' . $this->fileInfo['template_name']
                        . $this->params['biz_uid'] . 'Controller:';

        // group start
        $sourceText = '<?php' . PHP_EOL . PHP_EOL;
        $sourceText .= '$app->group(\'' . GeneratorConst::GEN_ROUTE_PREFIX . $this->params['biz_uid'] . '\', function () {' . PHP_EOL;

        // getCommand
        $sourceText .= PHP_EOL . "\t" . '$this->post(\'' . GeneratorConst::GET_COMMAND_URL . '\', '. $controllerPath .'getCommand\')->setName(\'getCommand\');';

        // docs
        $sourceText .= PHP_EOL . "\t" . '$this->get(\'/'. GeneratorConst::GEN_DOCS_METHOD_NAME  .'\', ';
        $sourceText .= $controllerPath . GeneratorConst::GEN_DOCS_METHOD_NAME .'\')->setName(\''. GeneratorConst::GEN_DOCS_METHOD_NAME .'\');' . PHP_EOL;

        // getSampleCodes
        $sourceText .= "\t" . '$this->post(\'/'. GeneratorConst::GEN_SAMPLECODES_METHOD_NAME  .'\', ';
        $sourceText .= $controllerPath . GeneratorConst::GEN_SAMPLECODES_METHOD_NAME .'\')->setName(\''. GeneratorConst::GEN_SAMPLECODES_METHOD_NAME .'\');' . PHP_EOL;

        // main
        $reqMethodCode = AppsUtil::getReqMethod($this->params['req_method']);
        $reqMethod = AppsUtil::getReqMethodString($reqMethodCode);
        if ((int)$this->params['req_method'] === CommonConst::REQ_METHOD_GET_CLEANURL_CODE) {
            foreach ($this->params['request'] as $req) {
                $uri .= '/{' . $req['req_key'] . '}';
            }
        }

        $reqMethod = strtolower($reqMethod);
        $sourceText .= PHP_EOL . "\t" . '$this->' . $reqMethod . '(\''. $uri .'\', ';
        $sourceText .= $controllerPath . GeneratorConst::GEN_MAIN_METHOD_NAME .'\')->setName(\''. GeneratorConst::GEN_MAIN_METHOD_NAME .'\');';
        $sourceText .= PHP_EOL . "\t" . '$this->' . $reqMethod . '(\''. $uri .'/\', ';
        $sourceText .= $controllerPath . GeneratorConst::GEN_MAIN_METHOD_NAME .'\')->setName(\''. GeneratorConst::GEN_MAIN_METHOD_NAME .'\');' . PHP_EOL;

        // group end
        $sourceText .= PHP_EOL . '})->add(new \middleware\Common($app->getContainer(), APP_ENV === APP_ENV_PRODUCTION));';

        $sourceText = str_replace("\t", '    ', $sourceText);
        $this->fileObj = $sourceText;

        return $this;
    }


    /**
     * API 명세서 파일 내용 작성
     *
     * @param $response
     *
     * @return $this
     */
    public function setDocsFile($response)
    {
        if (false !== $this->error) {
            return $this;
        }

        $this->fileObj = $this->ci->renderer->render($response, 'generated/apidoc-template.twig', $this->params);
        return $this;
    }


    /**
     * 파일 이름 얻기
     *
     * @param string $type
     * @param string $templateName
     *
     * @return $this|bool
     */
    public function getFileName($type = 'CONTROLLER', $templateName = GeneratorConst::GEN_FILE_PREFIX)
    {

        try {

            $filePath = $this->config . GeneratorConst::PATH_RULE[$type]['PATH'] . $this->userPath . '/';
            $fileName = ucfirst(strtolower($templateName)) . $this->params['biz_uid'] . GeneratorConst::PATH_RULE[$type]['SUBFIX'];

            if ( ! file_exists($filePath)) {
                mkdir($filePath, 0755, true);
            }

            $this->fileInfo = [
                'file_path'     => $filePath,
                'file_name'     => $fileName,
                'template_name' => $templateName
            ];

        } catch (\Exception $ex) {
            $this->error = 'Fail to get filename';
            LogMessage::error($this->error);
        }

        return $this;
    }

    /**
     * 파일쓰기
     *
     * @param string $mode
     *
     * @return string
     */
    public function writeFile($mode = 'w')
    {
        try {

            if (false !== $this->error) {
                throw new \Exception();
            }

            $fileContents = $this->_refining($this->fileObj);
            $generatedFile = fopen($this->fileInfo['file_path'] . $this->fileInfo['file_name'], $mode);
            fwrite($generatedFile, $fileContents);
            fclose($generatedFile);

        } catch (\Exception $ex) {
            LogMessage::error($ex->getMessage());
            LogMessage::error('Fail to write file Error :: ' . $this->error);
            LogMessage::error('Fail to write file Path :: ' . $this->fileInfo['file_path'] . $this->fileInfo['file_name']);
            return false;
        }

        return $this->fileInfo['file_name'];
    }

    public function exportBizUnit()
    {
        return false;
    }

    /**
     * API 문서 메서드 생성
     */
    private function _setDocsMehod()
    {
        $docsFileName = ucfirst(strtolower(GeneratorConst::GEN_FILE_PREFIX)) . $this->params['biz_uid'] . GeneratorConst::PATH_RULE['HTML']['SUBFIX'];
        $docsBody = ' try {' . PHP_EOL;
        $docsBody .= "\t" . '$this->renderer->render($response, \'generated/usr/' . $this->userPath . '/' . $docsFileName . '\');' . PHP_EOL;
        $docsBody .= <<<'SOURCE'
} catch (\Exception $ex) {
    $this->_viewErrorMessage($ex->getMessage());
}
	
return $response;
SOURCE;

        $docsBody = str_replace("\t", '    ', $docsBody);
        $docsMethod = $this->class->addMethod(GeneratorConst::GEN_DOCS_METHOD_NAME)->setReturnType('object');;
        $docsMethod->setBody($docsBody);
        $docsMethod->addParameter('request')->setTypeHint('Request');
        $docsMethod->addParameter('response')->setTypeHint('Response');
        $docsMethod->addComment('API Document Method' . "\n");
        $docsMethod->addComment("@param Request \$request\n");
        $docsMethod->addComment("@param Response \$response\n");
    }

    /**
     * 샘플코드 메소드
     */
    private function _setSampleCodesMehod()
    {
        $docsBody = <<<'SOURCE'
$results = [
    'result' => ErrorConst::SUCCESS_STRING,
    'data' => [
        'message' => '',
    ]
];

try {

    if (empty($request->isXhr())) {
        return $this->_viewErrorMessage(ErrorConst::ERROR_DESCRIPTION_ARRAY[ErrorConst::ERROR_NOT_ALLOW_REQ_METHOD]);
    }

    $params = $request->getAttribute('params');

    if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id', 'op_id', 'snipet'])) {
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

    $results['data'] = AppsUtil::getSecureSampleSource($params['snipet'], $params['op_id'], $bizOpsInfo);


} catch (\Exception $ex) {
    $results = [
        'result' => ErrorConst::FAIL_STRING,
        'data'   => [
            'message' => $this->_getErrorMessage($ex),
        ]
    ];
}

return $response->withJson($results, ErrorConst::SUCCESS_CODE);

SOURCE;

        $docsMethod = $this->class->addMethod(GeneratorConst::GEN_SAMPLECODES_METHOD_NAME)->setReturnType('object');
        $docsMethod->setBody($docsBody);
        $docsMethod->addParameter('request')->setTypeHint('Request');
        $docsMethod->addParameter('response')->setTypeHint('Response');
        $docsMethod->addComment('API Get SampleCode Method' . "\n");
        $docsMethod->addComment("@param Request \$request\n");
        $docsMethod->addComment("@param Response \$response\n");
    }


    /**
     * Async 호출 설정
     *
     * @param $op
     *
     * @return string
     */
    private function _makeAsyncSource($op)
    {

        $resSourceText = '';

        switch ($op['req_method']) {
            case CommonConst::REQ_METHOD_POST_STR :
                $opReqType = CommonConst::REQ_METHOD_POST;
                $opReqTypeVar = 'form_params';
                break;
            default :
                $opReqType = CommonConst::REQ_METHOD_GET;
                $opReqTypeVar = 'query';
        }

        $resSourceText .= '[';
        $resSourceText .= "'method' => '" . $opReqType . "', ";
        $resSourceText .= "'url' => '" . $op['target_url'] . (( ! empty($op['target_method'])) ? '/' . $op['target_method'] : '') . "', ";
        $resSourceText .= "'options' => [";

        switch ($op['header_transfer_type_code']) {
            case CommonConst::HTTP_HEADER_CONTENTS_TYPE_JSON_CODE :
                $opReqTypeVar = strtolower(CommonConst::VAR_TYPE_JSON_TEXT);
                break;
            case CommonConst::HTTP_HEADER_CONTENTS_TYPE_WWW_FORM_URLENCODED_CODE :
                $resSourceText .= '\'headers\' => [\'Content-Type\' => \''. CommonConst::HTTP_HEADER_CONTENTS_TYPE_WWW_FORM_URLENCODED_STR .'\'], ';
                break;
            case CommonConst::HTTP_HEADER_CONTENTS_TYPE_XML_CODE :
                $resSourceText .= '\'headers\' => [\'Content-Type\' => \''. CommonConst::HTTP_HEADER_CONTENTS_TYPE_XML_STR .'\'], ';
                $opReqTypeVar = 'body';
                break;
        }

        $resSourceText .= "'". $opReqTypeVar ."' => [";

        if ( ! empty($op['arguments'])) {
            foreach ($op['arguments'] as $reqs) {
                $reqVal = '\'' . $reqs['argument_value'] . '\'';
                if ( ! empty($reqs['relay_flag'])) {

                    $reqVal = ( ! empty($reqs['relay_biz_ops_id'])) ? '$this->params[\'' . $reqs['relay_parameter_key_name'] . '\']'
                        : '$this->resultParams[\'response\'][\'' . $reqs['relay_parameter_key_name'] . '\']'
                    ;

                    // 파라미터 타입이 JSON인 경우
                    if ($reqs['relay_parameter_type_code'] === CommonConst::VAR_TYPE_JSON_CODE && !empty($reqs['relay_sub_parameter_path'])) {
                        $reqVal .= $this->_jsonPathToArrayString($reqs['relay_sub_parameter_path']);
                    }

                    $reqVal .= ' ?? null';
                }

                $resSourceText .= '\'' . $reqs['parameter_key_name'] . '\' => ' . $reqVal . ', ';
            }
        }

        $resSourceText .= ']'; // data end

        $resSourceText .= ']'; // options end
        $resSourceText .= ']'; // array end

        return $resSourceText;
    }

    /**
     * ASYNC 용 서브메서드 생성
     *
     * @param $methodName
     * @param $asyncArr
     *
     * @return bool
     */
    private function _makeAsyncSubfuntion($methodName, $asyncArr)
    {
        try {

            $subBody = '';
            ksort($asyncArr);

            foreach ($asyncArr as $bseq => $async) {
                $subBody .= '$asyncRequests['. $bseq .'] = ' . $async . ';' . PHP_EOL;
            }

            $subBody .=  PHP_EOL . '$wait = ($type === 3) ? true : false;' . PHP_EOL;
            $subBody .=  PHP_EOL . 'return $this->_httpAsyncRequest($asyncRequests, $wait);' . PHP_EOL;
            $subBody = str_replace("\t", '    ', $subBody);

            // 하위 메소드 생성
            $method = $this->class->addMethod($methodName)->setVisibility('private');
            $method->setBody($subBody);
            $method->addParameter('type', 3)->setTypeHint('int');

            $method->addComment('ASYNC Method');
            $method->addComment('');
            $method->addComment('@param integer $type 1:Queue/2:Full/3:Promise');
            $method->addComment('');
            $method->addComment('@return array');
            $method->addComment('@throws \GuzzleHttp\Exception\GuzzleException');

        } catch (\Exception $ex) {
            LogMessage::error('Fail to write file Path :: ' . $this->fileInfo['file_path'] . $this->fileInfo['file_name']);
            return false;
        }

        return true;


    }

    /**
     * 오퍼레이션에 따른 서브메소드 생성
     *
     * @param $methodName
     * @param $op
     * @param $bindingSeq
     *
     * @return bool
     * @throws \Exception
     */
    private function _makeSubfuntion($methodName, $op, $bindingSeq)
    {
        $subBody = '';
        $subBody .= '$targetUrl = \'' . $op['origin_target_url'] . (( ! empty($op['target_method'])) ? '/' . $op['target_method'] : '') . '\';' . PHP_EOL;

        $cleanUrl = false;
        switch ($op['req_method']) {
            case CommonConst::REQ_METHOD_PUT_STR :
                $opReqType = CommonConst::REQ_METHOD_PUT;
                $opReqTypeVar = strtolower(CommonConst::VAR_TYPE_JSON_TEXT);
                break;
            case CommonConst::REQ_METHOD_DEL_STR :
                $opReqType = CommonConst::REQ_METHOD_DEL;
                $opReqTypeVar = strtolower(CommonConst::VAR_TYPE_JSON_TEXT);
                break;
            case CommonConst::REQ_METHOD_POST_STR :
                $opReqType = CommonConst::REQ_METHOD_POST;
                $opReqTypeVar = 'form_params';
                break;
            case CommonConst::REQ_METHOD_GET_CLEANURL_STR :
                $cleanUrl = true; // no break;
            default :
                $opReqType = CommonConst::REQ_METHOD_GET;
                $opReqTypeVar = 'query';
        }

        if ($op['method'] !== CommonConst::PROTOCOL_TYPE_SECURE) {
            $subBody .= '$options[\'verify\'] = $this->httpReqVerify;' . PHP_EOL;
            $subBody .= '$options[\'timeout\'] = $this->httpReqTimeout;' . PHP_EOL;
            $subBody .= '$options[\'connect_timeout\'] = $this->httpConnectTimeout;' . PHP_EOL;
        }

        switch ($op['header_transfer_type_code']) {
            case CommonConst::HTTP_HEADER_CONTENTS_TYPE_JSON_CODE :
                $opReqTypeVar = strtolower(CommonConst::VAR_TYPE_JSON_TEXT);
                break;
            case CommonConst::HTTP_HEADER_CONTENTS_TYPE_WWW_FORM_URLENCODED_CODE :
                $subBody .= '$options[\'headers\'] = [\'Content-Type\' => \''. CommonConst::HTTP_HEADER_CONTENTS_TYPE_WWW_FORM_URLENCODED_STR .'\'];' . PHP_EOL;
                break;
            case CommonConst::HTTP_HEADER_CONTENTS_TYPE_XML_CODE :
                $subBody .= '$options[\'headers\'] = [\'Content-Type\' => \''. CommonConst::HTTP_HEADER_CONTENTS_TYPE_XML_STR .'\'];' . PHP_EOL;
                $opReqTypeVar = 'body';
                break;
        }

        if ( ! empty($op['auth_type_code']) && ! empty($op['auth_keys']) ) {

            if (null === ($authKeys = CommonUtil::getValidJSON($op['auth_keys']))) {
                LogMessage::error('Auth_keys Error Not valid JSON - File Generator');
                return false;
            }

            switch ($op['auth_type_code']) {
                case CommonConst::API_AUTH_BASIC :
                    $subBody .= '$options[\'auth\'] = [\'' . $authKeys[0]['username'] . '\', \'' . $authKeys[0]['password'] . '\'];' . PHP_EOL;
                    break;
                case CommonConst::API_AUTH_BEARER_TOKEN :
                    $subBody .= '$options[\'headers\'][\'Authorization\'] = \'Bearer ' . $authKeys[0]['token'] . '\';' . PHP_EOL;
                    break;
            }
        }

        $subBody .= '$options[\'headers\'][\'User-Agent\'] = \'Synctree/2.1 - ' . APP_ENV . '\';' . PHP_EOL;

        if ( ! empty($op['arguments'])) {
            $subBodyReqs = '';
            $requiredRequest = [];
            foreach ($op['arguments'] as $reqs) {
                $reqVal = '\'' . $reqs['argument_value'] . '\'';
                if ( ! empty($reqs['relay_flag'])) {

                    $reqVal = ( ! empty($reqs['relay_operation_id'])) ? '$this->resultParams[\'response\'][\'' . $reqs['relay_parameter_key_name'] . '\']'
                            : '$this->params[\'' . $reqs['relay_parameter_key_name'] . '\']'
                            ;

                    // 파라미터 타입이 JSON인 경우
                    if ($reqs['relay_parameter_type_code'] === CommonConst::VAR_TYPE_JSON_CODE && !empty($reqs['relay_sub_parameter_path'])) {
                        $reqVal .= $this->_jsonPathToArrayString($reqs['relay_sub_parameter_path']);
                    }

                    $reqVal .= ' ?? null';
                }

                $subBodyReqs .= "\t" . '\'' . $reqs['parameter_key_name'] . '\' => ' . $reqVal . ',' . PHP_EOL;

                if (!empty($reqs['required_flag'])) {
                    $requiredRequest[] = $reqs['parameter_key_name'];
                }
            }
        }

        // Making a Request
        $validArray = '$options[\'' . $opReqTypeVar . '\']';
        if (!empty($subBodyReqs)) {
            $subBody .= $validArray . ' = [' . PHP_EOL;
            $subBody .= $subBodyReqs;
            $subBody .= '];' . PHP_EOL . PHP_EOL;
        }

        // 필수항목 처리
        if (!empty($requiredRequest)) {
            $requiredRequest = "'" . implode("', '", $requiredRequest) . "'";
            $subBody .= 'if (false === CommonUtil::validateParams('. $validArray .', [' . $requiredRequest . '], true)) {' . PHP_EOL;
            $subBody .= "\t" . 'LogMessage::error(\'Operator Not found required field\');' . PHP_EOL;
            $subBody .= "\t" . 'throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);' . PHP_EOL;
            $subBody .= '}' . PHP_EOL . PHP_EOL;
        }

        // Clean Url 일경우
        if ($cleanUrl === true) {
            $subBody .= 'if (!empty('. $validArray .') && is_array('. $validArray .')) { ' . PHP_EOL;
            $subBody .= "\t" . '$targetUrl .= \'/\' . implode( \'/\', '. $validArray .' );' . PHP_EOL;
            if ($op['method'] != CommonConst::PROTOCOL_TYPE_SECURE) {
                $subBody .= "\t" . 'unset('. $validArray .');' . PHP_EOL;
            }
            $subBody .= '}' . PHP_EOL . PHP_EOL;
        }

        // 보안프로토콜 처리
        if ($op['method'] == CommonConst::PROTOCOL_TYPE_SECURE) {
            $subBody .= '$eventKey = $this->_getRedisEventKey([\'params\' => '. $validArray .', \'op_code\' => '. $op['op_id'] .']);' . PHP_EOL  . PHP_EOL;
            $subBody .= '$options[\'' . $opReqTypeVar . '\'] = [\'event_key\' => $eventKey];' . PHP_EOL;
        }

        $subBody .= '$ret = $this->_httpRequest($targetUrl, $options, \'' . $opReqType . '\');' . PHP_EOL;

//        $subBody .= '$servErr = $this->_chkServerStatus($ret[\'res_status\']);' . PHP_EOL;
//        $subBody .= 'if (false === $servErr) {' . PHP_EOL;
//        $subBody .= "\t" . 'throw new \Exception(null, ErrorConst::ERROR_OPERATOR_SERVER);' . PHP_EOL;
//        $subBody .= '}' . PHP_EOL;

        $subBody .= PHP_EOL . '$result = [' . PHP_EOL;
        $subBody .= "\t" . '\'op_name\' => \'' . $op['op_name'] . '\',' . PHP_EOL;
        $subBody .= "\t" . '\'request_target_url\' => $targetUrl,' . PHP_EOL;
        $subBody .= "\t" . '\'server_status\' => $ret[\'res_status\'],' . PHP_EOL;
        $subBody .= "\t" . '\'request_method\' => \''. $op['req_method'] .'\',' . PHP_EOL;

        if ($op['method'] == CommonConst::PROTOCOL_TYPE_SIMPLE_HTTP) {
            if ($cleanUrl === false) {
                $subBody .= "\t" . '\'request\' => '. $validArray .',' . PHP_EOL;
            }
        } else {
            $subBody .= "\t" . '\'request\' => $options[\'' . $opReqTypeVar . '\'],' . PHP_EOL;
        }

        $subBody .= "\t" . '\'response\' => [' . PHP_EOL;
        foreach ($op['response'] as $ress) {
            if ($ress['res_var_type'] === CommonConst::VAR_TYPE_JSON && empty($ress['sub_parameter_format'])) {
                $subBody .= "\t\t" . '\'' . $ress['res_key'] . '\' => CommonUtil::getValidJSON($ret[\'res_data\'] ?? []),' . PHP_EOL;
            } else {
                $subBody .= "\t\t" . '\'' . $ress['res_key'] . '\' => CommonUtil::getValidJSON($ret[\'res_data\'][\'' . $ress['res_key'] . '\'] ?? null),' . PHP_EOL;
            }
        }
        $subBody .= "\t" . ']' . PHP_EOL;
        $subBody .= '];' . PHP_EOL;

        $subBody .=  PHP_EOL . 'return $result;' . PHP_EOL;
        $subBody = str_replace("\t", '    ', $subBody);

        // 하위 메소드 생성
        $method = $this->class->addMethod($methodName)->setVisibility('private')->setReturnType('array');
        $method->setBody($subBody);
        //$method->addParameter('request')->setTypeHint('Request');
        //$method->addParameter('response')->setTypeHint('Response');
        //$method->addParameter('params')->setTypeHint('array');
        //if ($bindingSeq > 1) {
        //    $method->addParameter('responseDatas')->setTypeHint('array');
        //}

        $method->addComment('Operation Name : ' . $op['op_name']);
        $method->addComment('ID : ' . $op['op_id']);
        $method->addComment('Description : ' . $op['op_desc']);
        $method->addComment('Binding Seq : ' . $bindingSeq);

        if ($op['method'] == CommonConst::PROTOCOL_TYPE_SECURE) {
            $method->addComment('Secure Protocol : true');
        }

        $method->addComment('');
        //$method->addComment('@param Request  $request');
        //$method->addComment('@param Response $response');
        $method->addComment('@return array');
        $method->addComment('@throws \GuzzleHttp\Exception\GuzzleException');

        return true;
    }

    /**
     * jsonpath 를 배열스트링으로 변환해 반환
     *
     * @param $jsonPath
     *
     * @return string
     */
    private function _jsonPathToArrayString($jsonPath)
    {
        $arrayString = '';
        //$relayJson = json_decode($reqs['relay_sub_parameter_format'], true);
        $relayJsonPath = str_replace('$.', '', $jsonPath);

        $tmp = explode('.', $relayJsonPath);
        foreach ($tmp as $tpKey) {
            if (!empty(strpos($tpKey, '['))) {
                $tpKeyTemp = explode('[', $tpKey);
                $arrayString .= '[\'' . $tpKeyTemp[0] . '\']' . '[' . str_replace(']', '' , $tpKeyTemp[1]) . ']';
            } else {
                $arrayString .= '[\'' . $tpKey . '\']';
            }
        }

        return $arrayString;


    }

    private function _refining($file)
    {
        $file = str_replace([
            'extends \\Synctree',
            '(\\Container',
            '(\\Request $request, \\Response $response',
            '(\\Request $request, \\Response $response, \\$args',
            'HTTP/1.1 200 OK',
            'Content-Type: text/html; charset=UTF-8',
        ], [
            'extends Synctree',
            '(Container',
            '(Request $request, Response $response',
            '(Request $request, Response $response, $args',
            '',
            '',
        ], $file);

        return $file;
    }

}