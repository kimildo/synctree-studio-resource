<?php
/**
 * Created by PhpStorm.
 * User: kimildo
 * Date: 2018-11-23
 * Time: 오후 4:03
 */

namespace controllers\console;

use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Container\ContainerInterface;

use libraries\{
    log\LogMessage,
    constant\CommonConst,
    constant\ErrorConst,
    constant\GeneratorConst,
    util\CommonUtil,
    util\RedisUtil,
    util\AppsUtil,
    util\AwsUtil
};

use PHPZip\Zip\Core\ZipUtils;
use \PHPZip\Zip\File\Zip as ZipArchiveFile;
use PHPZip\Zip\File\Zip;

/**
 * Class Deploy
 * 배포 관련 클래스
 *
 * @package controllers\console
 */
class Deploy extends SynctreeConsole
{
    /*
                        Owner(소유자)	Group(그룹)	Public(유저)
    Read(읽기)	        400 (R)	        40 (R)	    4 (R)
    Write(쓰기)	        200 (W)	        20 (W)	    2 (W)
    Execute(실행)	    100 (X)	        10 (X)	    1 (X)
    Permission(권한)	    700 (RWX)	    70 (RWX)	7 (RWX)
    */

    const CREATE_DIR_PERMISSIONS = 0755;
    const DEPLOY_SOURCE_DIR_NAME = 'source';
    const DEPLOY_SCRIPTS_DIR_NAME = 'scripts';

    /** @var string $deployConf Deploy Config 파일 */
    private $deployConf;

    /**
     * Deploy constructor.
     *
     * @param ContainerInterface $ci
     */
    public function __construct(ContainerInterface $ci)
    {
        parent::__construct($ci);
        $this->deployConf = $this->config['amazon']['codedeploy'];
    }

    /**
     * 배포 요청
     * 압축파일을 S3 업로드 후 CodeDeploy로 배포 요청
     *
     * @see https://packagist.org/packages/phpzip/phpzip
     * @see https://docs.aws.amazon.com/ko_kr/aws-sdk-php/v3/api/api-codedeploy-2014-10-06.html#createdeployment
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function deployment(Request $request, Response $response)
    {
        $results = $this->jsonResult;
        $permission = self::CREATE_DIR_PERMISSIONS; // 폴더 생성시 권한
        $userPath = $this->userPath;

        try {

            $params = $request->getAttribute('params');

            // 필수 파라미터 검사
            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id'])) {
                LogMessage::error('Not found required field [field:app_id]');
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            // 비즈옵스의 정보
            $result = CommonUtil::callProcedure($this->ci, 'executeGetBiz', [
                'account_id'     => $this->accountId,
                'team_id'        => $this->teamId,
                'application_id' => $params['app_id'],
                'biz_ops_id'     => $params['biz_id'],
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            $bizOps = $result['data'][1][0];
            $uid = AppsUtil::replaceUid($bizOps['biz_ops_key']);

            // Redis에 배포 진행 중인 데이터가 있으면 에러
            if (false !== ($redisData = RedisUtil::getData($this->redis,
                    CommonConst::DEPLOYMENT_REDIS_KEY . $userPath . '_' . $params['app_id'] . $params['biz_id'], CommonConst::REDIS_DEPLOY_SESSION))) {
                throw new \Exception(null, ErrorConst::ERROR_ALEADY_DEPLOY);
            }

            // 비즈옵스 빌드 리스트
            $result = CommonUtil::callProcedure($this->ci, 'executeGetBizBuildList', [
                'account_id'     => $this->accountId,
                'team_id'        => $this->teamId,
                'application_id' => $params['app_id'],
                'biz_ops_id'     => $params['biz_id'],
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            if (empty($result['data'][1])) {
                throw new \Exception('', ErrorConst::ERROR_RDB_NOT_EXIST_BUILD);
            }

            // 비즈옵스 빌드 리스트 마지막
            $lastBuild = array_pop($result['data'][1]);

            // 팀 속성을 조회해 배포관련된 정보를 얻어옵니다.
            //executeGetTeamInfo
            $result = CommonUtil::callProcedure($this->ci, 'executeGetTeamInfo', [
                'account_id' => $this->accountId,
                'team_id'    => $this->teamId,
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            $conf = $result['data'][1][0];
            $iniFileName = 'default';
            $appName = 'studio-deploy';

            if (isset($conf['IAM_info']) && !empty($conf['IAM_info'])) {
                $info = CommonUtil::getValidJSON($conf['IAM_info']);
                $iniFileName = $info['company-name'];
                $appName = $info['app-name'];
            }

            // Codedeploy CLI Config
            $deployConfig = [
                'applicationName'     => $appName,
                'deploymentGroupName' => $conf['deploy_group_name'],
                'bucket'              => $conf['deploy_bucket_name'],
                'ec2TagName'          => $conf['deploy_ec2_tag_name']
            ];

            $originPath = $this->config['deploy']['origin_file_path'];
            $archiveTargetPath = $this->config['deploy']['source_group_file_path'];
            $archiveLocationPath = $this->config['deploy']['achive_target_path'];
            $ver = microtime(true);

            $preFix = GeneratorConst::GEN_FILE_PREFIX;
            $fileInfo = [
                'controller' => [
                    'file_name' => ucfirst(strtolower($preFix)) . $uid . GeneratorConst::PATH_RULE['CONTROLLER']['SUBFIX'],
                    'file_path' => GeneratorConst::PATH_RULE['CONTROLLER']['PATH'] . $userPath . DIRECTORY_SEPARATOR,
                ],
                'router'     => [
                    'file_name' => ucfirst(strtolower($preFix)) . $uid . GeneratorConst::PATH_RULE['ROUTER']['SUBFIX'],
                    'file_path' => GeneratorConst::PATH_RULE['ROUTER']['PATH'] . $userPath . DIRECTORY_SEPARATOR,
                ]
            ];

            ob_start();
            $zip = new ZipArchiveFile();

            switch (APP_ENV) {
                case APP_ENV_PRODUCTION :
                    $ver .= '';
                    break;
                case APP_ENV_STAGING :
                    $ver .= '_stg';
                    break;
                case APP_ENV_DEVELOPMENT :
                    $ver .= '_dev';
                    break;
                default :
                    $ver .= '_dev_local';
            }

            $zipFileName = 'deploy_' . $ver . '.zip';
            $zipFile = $archiveLocationPath . $zipFileName;

            $zip->setZipFile($zipFile);
            //$zip->addDirectoryContent($deployTmpDir, '');

            // 압축파일에 application 폴더 생성 후 Controller, router 파일 추가
            $zip->addDirectory(self::DEPLOY_SOURCE_DIR_NAME . '/application');
            foreach ($fileInfo as $type => $row) {
                $filePath = $originPath . $userPath . '/' . $row['file_name'];
                $zip->addFile(file_get_contents($filePath), self::DEPLOY_SOURCE_DIR_NAME . DIRECTORY_SEPARATOR . $row['file_path'] . $row['file_name'],
                    filectime($filePath));
            }

            // 압축파일에 scripts 폴더 생성 후 파일 추가
            $zip->addDirectory('scripts');
            $handle = opendir($archiveTargetPath . 'scripts');
            if ($handle) {
                while ($filename = readdir($handle)) {
                    if (($filename != '.') && ($filename != '..')) {
                        $filePath = $archiveTargetPath . self::DEPLOY_SCRIPTS_DIR_NAME . DIRECTORY_SEPARATOR . $filename;
                        $zip->addFile(file_get_contents($filePath), 'scripts/' . $filename, filectime($filePath));
                    }
                }
                closedir($handle);
            }

            // 압축파일에 appspec.yml 파일 추가
            $zip->addFile(file_get_contents($archiveTargetPath . 'appspec.yml'), 'appspec.yml', filectime($archiveTargetPath . 'appspec.yml'));

            // 압축파일 생성 과정 끝
            $zip->finalize();

            // 삭제를 위해 권한 변경
            chmod($zipFile, $permission);

            // s3 업로드
            if (false === ($s3Result = AwsUtil::s3FileUpload($userPath . DIRECTORY_SEPARATOR . $zipFileName, $zipFile, 's3deploy', $iniFileName))) {
                @unlink($zipFile);
                throw new \Exception(null, ErrorConst::ERROR_FILE_UPLOAD);
            }

            // 실서버 환경에서는 압축파일 삭제
            if (APP_ENV === APP_ENV_PRODUCTION) {
                @unlink($zipFile);
            }

            $deployConfig['fileName'] = $userPath . DIRECTORY_SEPARATOR . $zipFileName;

            // 배포요청 생성
            if (false === ($deployResult = AwsUtil::createCodeDeploy($deployConfig, $iniFileName))) {
                throw new \Exception(null, ErrorConst::ERROR_FAIL_DEPLOY);
            }

            // 배포요청이 성공하면 반환된 deploy_id를 저장
            $result = CommonUtil::callProcedure($this->ci, 'executeSetBizDeploy', [
                'account_id'         => $this->accountId,
                'team_id'            => $this->teamId,
                'application_id'     => $params['app_id'],
                'biz_ops_id'         => $params['biz_id'],
                'biz_ops_version_id' => $lastBuild['biz_ops_version_id'],
                'environment_code'   => CommonConst::DEPLOY_TARGET_PRD,
                'deployment_key'     => $deployResult['deploymentId'],
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            // 배포요청이 성공하면 반환된 deploy_id를 Redis에 저장 90sec
            RedisUtil::setDataWithExpire($this->redis, CommonConst::REDIS_DEPLOY_SESSION,
                CommonConst::DEPLOYMENT_REDIS_KEY . $userPath . '_' . $params['app_id'] . $params['biz_id'], CommonConst::REDIS_SESSION_EXPIRE_TIME_SEC_90,
                ['deploymentId' => $deployResult['deploymentId'], 'created_time' => date('Y-m-d H:i:s')]);

            $results['data'] = [
                'file_name' => $userPath . DIRECTORY_SEPARATOR . $zipFileName,
                'deploy_id' => $deployResult['deploymentId'],
            ];

            // 비즈 관련 Redis 삭제
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
     * 이전의 배포 파일로 다시 배포
     * 배포ID는 재성성 됩니다.
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function reDeploy(Request $request, Response $response)
    {
        $results = $this->jsonResult;
        $userPath = $this->userPath;

        try {

            $params = $request->getAttribute('params');

            // 필수 파라미터 검사
            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id', 'deploy_id'])) {
                LogMessage::error('Not found required field');
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
            }

            // Redis에 배포 진행 중인 데이터가 있으면 에러
            if (false !== ($redisData = RedisUtil::getData($this->redis,
                    CommonConst::DEPLOYMENT_REDIS_KEY . $userPath . '_' . $params['app_id'] . $params['biz_id']))) {
                throw new \Exception(null, ErrorConst::ERROR_ALEADY_DEPLOY);
            }

            // 배포 이력 조회
            $result = CommonUtil::callProcedure($this->ci, 'executeGetBizDeployList', [
                'account_id'       => $this->accountId,
                'team_id'          => $this->teamId,
                'application_id'   => $params['app_id'],
                'biz_ops_id'       => $params['biz_id'],
                'environment_code' => CommonConst::DEPLOY_TARGET_PRD
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            $deployment = $result['data'][1];

            // 해당 비즈옵스에 deploy_id 가 있는지 체크
            if (false === ($depIdx = array_search($params['deploy_id'], array_column($deployment, 'deployment_key')))) {
                LogMessage::error('Deploy error');
                throw new \Exception(null, ErrorConst::ERROR_NOT_EXIST_DEPLOY);
            }

            // 해당 비즈옵스의 빌드번호
            $buildInfo = $deployment[$depIdx];


            // 팀 속성을 조회해 배포관련된 정보를 얻어옵니다.
            $result = CommonUtil::callProcedure($this->ci, 'executeGetTeamInfo', [
                'account_id' => $this->accountId,
                'team_id'    => $this->teamId,
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            $conf = $result['data'][1][0];

            // AWS 에서 해당 deploy_id 로 체크
            if (false === ($deployList = AwsUtil::getDeployList([
                    'applicationName'     => $this->deployConf['app_name'],
                    'deploymentGroupName' => $conf['deploy_group_name'],
                ], [$params['deploy_id']]))) {
                throw new \Exception(null, ErrorConst::ERROR_NOT_EXIST_DEPLOY);
            }

            // 해당 배포가 성공한 것인지 체크
            $deploy = current($deployList);
            if ($deploy['status'] !== 'Succeeded') {
                throw new \Exception(null, ErrorConst::ERROR_NOT_EXIST_DEPLOY);
            }

            // 배포 설정
            $deployConfig = [
                'applicationName'     => $deploy['applicationName'],
                'deploymentGroupName' => $deploy['deploymentGroupName'],
                'bucket'              => $deploy['revision']['s3Location']['bucket'],
                'fileName'            => $deploy['revision']['s3Location']['key'],
                'ec2TagName'          => $conf['deploy_ec2_tag_name'],
            ];

            // 배포생성
            if (false === ($deployResult = AwsUtil::createCodeDeploy($deployConfig))) {
                throw new \Exception(null, ErrorConst::ERROR_FAIL_DEPLOY);
            }

            // 배포요청이 성공하면 반환된 deploy_id를 저장
            $result = CommonUtil::callProcedure($this->ci, 'executeSetBizDeploy', [
                'account_id'         => $this->accountId,
                'team_id'            => $this->teamId,
                'application_id'     => $params['app_id'],
                'biz_ops_id'         => $params['biz_id'],
                'biz_ops_version_id' => $buildInfo['biz_ops_version_id'],
                'environment_code'   => CommonConst::DEPLOY_TARGET_PRD,
                'deployment_key'     => $deployResult['deploymentId'],
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            // 배포요청이 성공하면 반환된 deploy_id를 Redis에 저장 90sec
            RedisUtil::setDataWithExpire($this->redis, CommonConst::REDIS_SESSION,
                CommonConst::DEPLOYMENT_REDIS_KEY . $userPath . '_' . $params['app_id'] . $params['biz_id'], CommonConst::REDIS_SESSION_EXPIRE_TIME_SEC_90,
                ['deploymentId' => $deployResult['deploymentId'], 'created_time' => date('Y-m-d H:i:s')]);

            $results['data'] = [
                'file_name' => $deploy['revision']['s3Location']['bucket'],
                'deploy_id' => $deployResult['deploymentId'],
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
     * 해당 비즈옵스의 배포 리스트 반환
     *
     * @see https://docs.aws.amazon.com/ko_kr/aws-sdk-php/v3/api/api-codedeploy-2014-10-06.html#getdeployment
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function getDeployList(Request $request, Response $response)
    {
        $results = $this->jsonResult;

        try {

            $params = $request->getAttribute('params');

            if (false === CommonUtil::validateParams($params, ['app_id', 'biz_id'])) {
                LogMessage::error('Not found required field [field:app_id]');
                throw new \Exception(null, ErrorConst::ERROR_NOT_FOUND_REQUIRE_FIELD);
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

            // Codedeploy CLI Config
            $deployConfig = [
                'applicationName'     => $this->deployConf['app_name'],
                'deploymentGroupName' => $conf['deploy_group_name'],
                'bucket'              => $conf['deploy_bucket_name'],
                'ec2TagName'          => $conf['deploy_ec2_tag_name']
            ];

            // 배포 이력 조회
            $result = CommonUtil::callProcedure($this->ci, 'executeGetBizDeployList', [
                'account_id'       => $this->accountId,
                'team_id'          => $this->teamId,
                'application_id'   => $params['app_id'],
                'biz_ops_id'       => $params['biz_id'],
                'environment_code' => CommonConst::DEPLOY_TARGET_PRD
            ]);

            if (0 !== $result['returnCode']) {
                throw new \Exception($result['message'], $result["returnCode"]);
            }

            $deployment = $result['data'][1];
            $deployList = $awsDeployList = [];

            if ( ! empty($deployment)) {
                $deployIdxs = array_column($deployment, 'deployment_key');
                if (false === ($awsDeployList = AwsUtil::getDeployList($deployConfig, $deployIdxs))) {
                    throw new \Exception(null, ErrorConst::ERROR_NOT_EXIST_DEPLOY);
                }

                foreach ($awsDeployList as $dep) {
                    $depIdx = array_search($dep['deploymentId'], array_column($deployment, 'deployment_key'));
                    $deployList[] = [
                        'deploymentId'  => $dep['deploymentId'],
                        'status'        => $dep['status'],
                        'createTime'    => $dep['createTime'],
                        'completeTime'  => $dep['completeTime'],
                        'bizOpsVersion' => $deployment[$depIdx]['biz_ops_version'],
                    ];
                }
            }

            $results['data'] = $deployList;

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