<?php
/**
 * AWS Util
 *
 * @author kimildo
 */

namespace libraries\util;

use libraries\log\LogMessage;
use libraries\util\CommonUtil;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

use Aws\CodeDeploy\CodeDeployClient;
use Aws\CodeDeploy\Exception\CodeDeployException;

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

use Aws\Credentials\CredentialProvider;


class AwsUtil
{
    private static $s3;
    private static $codeDeploy;
    private static $awsConfig;

    const S3_IMAGE_UPLOAD_PATH = 'upload' . DIRECTORY_SEPARATOR;

    /**
     * S3 파일 업로드
     *
     * @param        $fileName
     * @param        $filePath
     * @param string $confType
     *
     * @return bool
     */
    public static function s3FileUpload ($fileName, $filePath, $confType = 's3')
    {
        $result = false;

        if (false === self::_setAwsS3Config($confType)) {
            return $result;
        }

        try {

            $putResult = self::$s3->putObject(
                [
                    'Bucket'       => self::$awsConfig[$confType]['bucket_name'],
                    'Key'          => $fileName,
                    'SourceFile'   => $filePath,
                    //'ACL'          => 'public-read',
                    'ContentType'  => 'zip',
                    'StorageClass' => 'STANDARD'
                ]
            );

            $putResult = $putResult->toArray();

            if ($putResult['@metadata']['statusCode'] !== 200) {
                LogMessage::error('S3 file upload fail.');
            }

            $result = true;

        } catch (S3Exception $e) {
            LogMessage::error('Error S3 file Upload :: ', $e->getMessage());
        } catch (\Exception $e) {
            LogMessage::error('Error S3 file Upload :: ', $e->getMessage());
        }

        return $result;
    }

    /**
     * S3 파일 삭제
     * @param $fileName
     *
     * @return bool

    public static function deleteFile($fileName)
    {
        if (false === self::_setAwsS3Config()) {
            return false;
        }

        $result = false;

        try {

            $delResult = self::$s3->deleteObject([
                'Bucket' => self::$awsConfig[$confType]['bucket_name'],
                'Key'    => self::S3_IMAGE_UPLOAD_PATH . $fileName
            ]);

            $delResult = $delResult->toArray();

            switch ($delResult['@metadata']['statusCode']) {
                case 200 :
                case 204 :
                    $result = true;
                    break;
            }

        } catch (S3Exception $e) {
            LogMessage::error('S3 Delete file Exception :: ', $e->getAwsErrorMessage());
        } catch (Exception $e) {
            LogMessage::error('S3 Delete file Exception :: ', $e->getMessage());
        }

        return $result;
    }
    */

    /**
     * @param        $fileName
     * @param string $confType
     *
     * @return bool
     */
    public static function checkFile($fileName, $confType = 's3')
    {
        if (false === self::_setAwsS3Config($confType)) {
            return false;
        }

        $response = self::$s3->doesObjectExist(self::$awsConfig[$confType]['bucket_name'], $fileName);
        return $response;
    }


    /**
     * @param array $config
     * @param       $deployListResult
     *
     * @return array|bool
     */
    public static function getDeployList(array $config = [], $deployListResult)
    {
        $response = false;
        $deployHistory = [];
        $deployFailHistory = [];

        if (false === self::_setAwsCodedeployConfig()) {
            return $response;
        }

        try {

            $history = self::$codeDeploy->batchGetDeployments([
                'deploymentIds' => $deployListResult
            ]);

            $history = $history->toArray();
            foreach ($history['deploymentsInfo'] as $data) {

                $data['createTime'] = self::_ustToKst($data['createTime']);
                $data['completeTime'] = self::_ustToKst($data['completeTime']);

                if ($data['status'] !== 'Succeeded') {
                    $deployFailHistory[$data['createTime']] = $data;
                    continue;
                }

                $deployHistory[$data['createTime']] = $data;
            }

            krsort($deployHistory);
            krsort($deployFailHistory);
            $response = $deployHistory + $deployFailHistory;

        } catch (CodeDeployException $e) {
            LogMessage::error('Codedeploy Exception :: ', $e->getAwsErrorMessage());
        } catch (\Exception $e) {
            LogMessage::error('Client Exception :: ', $e->getMessage());
        }

        return $response;

        /*
        foreach ($deployListResult as $depId) {

            try {

                $info = self::$codeDeploy->getDeployment([
                    'deploymentId' => $depId
                ]);

                $data = $info->toArray();
                $data = $data['deploymentInfo'];
                $data['createTime'] = self::_ustToKst($data['createTime']);
                $data['completeTime'] = self::_ustToKst($data['completeTime']);
                $deployList[] = $data;

            } catch (CodeDeployException $e) {
                LogMessage::error('Codedeploy Exception :: ', $e->getAwsErrorMessage());
            } catch (\Exception $e) {
                LogMessage::error('Client Exception :: ', $e->getMessage());
            }
        }
        */

    }

    /**
     * 배포 생성
     *
     * see https://docs.aws.amazon.com/ko_kr/aws-sdk-php/v3/api/api-codedeploy-2014-10-06.html#createdeployment
     * @param array $config
     *
     * @return bool
     */
    public static function createCodeDeploy(array $config = [])
    {
        $response = false;

        if (false === self::_setAwsCodedeployConfig()) {
            return $response;
        }

        try {

            $response = self::$codeDeploy->createDeployment([
                'applicationName' => $config['applicationName'], // 애플리케이션
                'deploymentGroupName' => $config['deploymentGroupName'], // 배포그룹
                'fileExistsBehavior' => 'OVERWRITE',
                'revision' => [
                    'revisionType' => 'S3',
                    's3Location' => [
                        'bucket' => $config['bucket'],
                        'bundleType' => 'zip',
                        'key' => $config['fileName'],
                    ]
                ],
                'targetInstances' => [
                    'ec2TagSet' => [
                        'ec2TagSetList' => [
                            [
                                [
                                    'Key' => 'DeployGroup',
                                    'Type' => 'KEY_AND_VALUE',
                                    'Value' => $config['ec2TagName'],
                                ]
                            ],
                        ],
                    ],
                ],
            ]);

            $response = $response->toArray();

        } catch (CodeDeployException $e) {
            LogMessage::error('Codedeploy Exception :: ', $e->getAwsErrorMessage());
        } catch (\Exception $e) {
            LogMessage::error('Client Exception :: ', $e->getMessage());
        }

        return $response;

    }

    /**
     * @param array  $recipients
     * @param string $subject
     * @param string $htmlBody
     * @param string $charSet
     *
     * @return bool
     */
    public static function sendEmail($recipients = [], $subject = '', $htmlBody = '', $charSet = 'UTF-8')
    {
        $result = false;

        try {

            putenv('HOME=/home/ubuntu');

            $profile = 'default';
            $path = '~/.aws/credentials';
            $provider = CredentialProvider::ini($profile, $path);
            $provider = CredentialProvider::memoize($provider);

            $SesClient = new SesClient([
                'profile' => 'default',
                'version' => '2010-12-01',
                'region'  => 'us-east-1',
                'credentials' => $provider,
            ]);

            $sender = 'kimildo78@nntuple.com';

            if (empty($recipients)) {
                throw new \Exception('Recipients is empty!!');
            }

            if (empty($subject)) {
                throw new \Exception('Subject is empty!!');
            }

            if (empty($htmlBody)) {
                throw new \Exception('Body is empty!!');
            }

            $sendOptions = [
                'Destination'      => [
                    'ToAddresses' => $recipients,
                ],
                'ReplyToAddresses' => [$sender],
                'Source'           => $sender,
            ];

            $sendOptions['Message']['Body']['Html'] = [
                'Charset' => $charSet,
                'Data' => $htmlBody,
            ];

            $sendOptions['Message']['Subject'] = [
                'Charset' => $charSet,
                'Data' => $subject,
            ];

            $sendResult = $SesClient->sendEmail($sendOptions);

//            $subject = 'Amazon SES test (PHP용 AWS SDK)';
//            $plaintext_body = 'This email was sent with Amazon SES using the AWS SDK for PHP.' ;
//            $html_body =  '<h1>AWS Amazon Simple Email Service Test Email</h1>'.
//                '<p>This email was sent with <a href="https://aws.amazon.com/ses/">'.
//                'Amazon SES</a> using the <a href="https://aws.amazon.com/sdk-for-php/">'.
//                'PHP용 AWS SDK</a>.</p>';
//            $char_set = 'UTF-8';
//
//            $sendResult = $SesClient->sendEmail([
//                'Destination'      => [
//                    'ToAddresses' => $recipient_emails,
//                ],
//                'ReplyToAddresses' => [$sender_email],
//                'Source'           => $sender_email,
//                'Message'          => [
//                    'Body'    => [
//                        'Html' => [
//                            'Charset' => $char_set,
//                            'Data'    => $html_body,
//                        ],
//                        'Text' => [
//                            'Charset' => $char_set,
//                            'Data'    => $plaintext_body,
//                        ],
//                    ],
//                    'Subject' => [
//                        'Charset' => $char_set,
//                        'Data'    => $subject,
//                    ],
//                ],
//            ]);

            $messageId = $sendResult['MessageId'];
            LogMessage::info('Email sent! Message ID :: ' . $messageId);

            $result = true;

        } catch (AwsException $e) {
            LogMessage::error('Error Send Email (AwsException) :: ' . $e->getAwsErrorCode(), $e->getMessage());
        } catch (\Exception $e) {
            LogMessage::error('Error Send Email :: ' .  $e->getCode(), $e->getMessage());
        }

        return $result;

    }


    /**
     * @param $confType
     *
     * @return S3Client|bool
     */
    private static function _setAwsS3Config($confType)
    {
        $config = include APP_DIR . 'config/' . APP_ENV . '.php';
        $awsConfig = self::$awsConfig = isset($config['settings']['amazon']) ? $config['settings']['amazon'] : false;

        if (empty($confType)) {
            return false;
        }

        if (false === $awsConfig) {
            return false;
        }

        return self::$s3 = new S3Client([
            'version' => $awsConfig[$confType]['version'],
            'region'  => $awsConfig[$confType]['region'],
            'credentials' => [
                'key' => $awsConfig[$confType]['key'],
                'secret' => $awsConfig[$confType]['secret'],
            ]
        ]);
    }

    /**
     * @param string $confType
     *
     * @return CodeDeployClient|bool
     */
    private static function _setAwsCodedeployConfig($confType = 'codedeploy')
    {
        $config = include APP_DIR . 'config/' . APP_ENV . '.php';
        $awsConfig = self::$awsConfig = isset($config['settings']['amazon']) ? $config['settings']['amazon'] : false;

        if (false === $awsConfig) {
            return false;
        }

        return self::$codeDeploy = new CodeDeployClient([
            'version' => $awsConfig[$confType]['version'],
            'region'  => $awsConfig[$confType]['region'],
            'credentials' => [
                'key' => $awsConfig[$confType]['key'],
                'secret' => $awsConfig[$confType]['secret'],
            ]
        ]);
    }

    /**
     * @param $timestamp
     *
     * @return mixed
     * @throws \Exception
     */
    private static function _ustToKst($timestamp)
    {
        $timestamp->add(new \DateInterval('PT9H'));
        $timestamp = $timestamp->format('Y-m-d H:i:s');

        return $timestamp;
    }



}