<?php
namespace libraries\constant;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class CommonConst
{
    const DEFAULT_CHAR_SET = 'UTF-8';

    /*
     * secure file info
     */
    const SECURE_CONFIG_FILE_NAME = 'config.ini';

    /*
     * http timeout info
     */
    const HTTP_CONNECT_TIMEOUT = 5;
    const HTTP_RESPONSE_TIMEOUT = 10;

    /*
     * redis db info
     */
    const REDIS_SESSION = 0;

    /*
     * redis db for secure protocol command
     */
    const REDIS_SECURE_PROTOCOL_COMMAND = 11;

    /*
     * redis db for message session
     */
    const REDIS_MESSAGE_SESSION = 12;
    const REDIS_DEPLOY_SESSION = 2;
    const REDIS_CLIENT_SESSION = 3;
    const REDIS_BIZ_RES_SESSION = 4;
	
    /*
     * redis db partners login
     */
    const REDIS_PARTNERS_SESSION = 1;

    /*
     * redis secure key
     */
    const SECURE_REDIS_KEY = 'redis_key';

    /*
     * redis secure key
     */
    const SECURE_REDIS_PASSWORD = 'redis_password';

    /*
     * aes secure key
     */
    const SECURE_AES_KEY = 'aes_key';
    
    /*
     * redis key prefix
     */
    const REDIS_KEY_PREFIX = '';

    /*
     * redis session expire time info
     */
    const REDIS_SESSION_EXPIRE_TIME_SEC_90  = 90;               // sec
    const REDIS_SESSION_EXPIRE_TIME_MIN     = 60;               // sec
    const REDIS_SESSION_EXPIRE_TIME_MIN_3   = (60*3);           // sec
    const REDIS_SESSION_EXPIRE_TIME_MIN_5   = (60*5);           // sec
    const REDIS_SESSION_EXPIRE_TIME_MIN_10  = (60*10);          // sec
    const REDIS_SESSION_EXPIRE_TIME_MIN_15  = (60*15);          // sec
    const REDIS_SESSION_EXPIRE_TIME_MIN_30  = (60*30);          // sec
    const REDIS_SESSION_EXPIRE_TIME_MIN_60  = (60*60);          // sec
    const REDIS_SESSION_EXPIRE_TIME_DAY_1   = ((60*60*24)*1);   // sec
    const REDIS_SESSION_EXPIRE_TIME_DAY_2   = ((60*60*24)*2);   // sec
    const REDIS_SESSION_EXPIRE_TIME_DAY_7   = ((60*60*24)*7);   // sec

    /**
     * Redis Keys
     */
    const CLIENT_APP_LIST_REDIS_KEY = 'client_app_list_';
    const CLIENT_BIZ_LIST_REDIS_KEY = 'client_biz_list_';
    const CLIENT_OPS_LIST_REDIS_KEY = 'client_ops_list_';

    const CLIENT_APPS_REDIS_KEY             = 'client_apps_';
    const CLIENT_BIZ_REDIS_KEY              = 'client_biz_';
    const CLIENT_BIZ_BUILD_REDIS_KEY        = 'client_biz_build_';
    const CLIENT_OPS_REDIS_KEY              = 'client_ops_';
    const CLIENT_RELAY_REDIS_KEY            = 'client_relay_';
    const CLIENT_BIND_OPS_LIST_REDIS_KEY    = 'client_bind_ops_list_';

    const BIZ_RESPONSE_CACHE                = 'biz_response_cache_';
    const BIZ_DOCS_CACHE                    = 'biz_docs_cache_';


    const DEPLOYMENT_REDIS_KEY      = 'deployment_';

    /**
     * javscript & css updated date
     */
    const SCRIPT_UPDATED    = '2019052901';
    const CSS_UPDATED       = '2019052801';

    /**
     * default page title
     * default page description
     */
    const DEFAULT_PAGE_TITLE = 'Title';
    const DEFAULT_PAGE_DESC  = 'Title';
    const AWS_S3_END_POINT = 'https://synctreem-public.s3.ap-northeast-2.amazonaws.com';


    /**
     *
     */
    const GET_COMMAND_URL = '/secure/getCommand';
    const DOCS_URL = '/docs';


    /**
     * G 1 GET (query)
     * P 2 POST
     * C 3 GET (Clean URL)
     * U 4 PUT
     * D 5 DELETE
     */
    const REQ_METHOD_GET_CODE          = 1;
    const REQ_METHOD_POST_CODE         = 2;
    const REQ_METHOD_GET_CLEANURL_CODE = 3;
    const REQ_METHOD_PUT_CODE          = 4;
    const REQ_METHOD_DEL_CODE          = 5;

    const REQ_METHOD_GET_STR          = 'G';
    const REQ_METHOD_POST_STR         = 'P';
    const REQ_METHOD_GET_CLEANURL_STR = 'C';
    const REQ_METHOD_PUT_STR          = 'U';
    const REQ_METHOD_DEL_STR          = 'D';

    const REQ_METHOD_GET          = 'GET';
    const REQ_METHOD_POST         = 'POST';
    const REQ_METHOD_PUT          = 'PUT';
    const REQ_METHOD_DEL          = 'DELETE';
    const REQ_METHOD_PATCH        = 'PATCH';

    const REQ_METHOD_STR_TO_CODE = [
        self::REQ_METHOD_GET_STR          => self::REQ_METHOD_GET_CODE,
        self::REQ_METHOD_POST_STR         => self::REQ_METHOD_POST_CODE,
        self::REQ_METHOD_GET_CLEANURL_STR => self::REQ_METHOD_GET_CLEANURL_CODE,
        self::REQ_METHOD_PUT_STR          => self::REQ_METHOD_PUT_CODE,
        self::REQ_METHOD_DEL_STR          => self::REQ_METHOD_DEL_CODE,
    ];

    const REQ_METHOD_CODE_TO_STR = [
        self::REQ_METHOD_GET_CODE          => self::REQ_METHOD_GET_STR,
        self::REQ_METHOD_POST_CODE         => self::REQ_METHOD_POST_STR,
        self::REQ_METHOD_GET_CLEANURL_CODE => self::REQ_METHOD_GET_CLEANURL_STR,
        self::REQ_METHOD_PUT_CODE          => self::REQ_METHOD_PUT_STR,
        self::REQ_METHOD_DEL_CODE          => self::REQ_METHOD_DEL_STR,
    ];

    const PROTOCOL_TYPE_SECURE = 1;
    const PROTOCOL_TYPE_SIMPLE_HTTP = 2;

    const DIRECTION_IN_CODE = 1;
    const DIRECTION_OUT_CODE = 2;

    const PARAMS_NO_REQUIRED_CODE = 0;
    const PARAMS_REQUIRED_CODE = 1;

    const ENVIRONMENT_CODE_MOK = 1;
    const ENVIRONMENT_CODE_DEV = 2;
    const ENVIRONMENT_CODE_UAT = 3;
    const ENVIRONMENT_CODE_PRD = 4;

    const APPS_FILE_NAME     = 'apps';
    const BIZUNIT_FILE_NAME  = 'bunit';
    const OPERATOR_FILE_NAME = 'operator';

    const VAR_TYPE_INTEGER = 'INT';
    const VAR_TYPE_INTEGER_TEXT = 'INTEGER';
    const VAR_TYPE_INTEGER_CODE = 1;

    const VAR_TYPE_STRING = 'STR';
    const VAR_TYPE_STRING_TEXT = 'STRING';
    const VAR_TYPE_STRING_CODE = 2;

    const VAR_TYPE_BOOLEAN = 'BOL';
    const VAR_TYPE_BOOLEAN_TEXT = 'BOOLEAN';
    const VAR_TYPE_BOOLEAN_CODE = 3;

    const VAR_TYPE_DATE = 'DAT';
    const VAR_TYPE_DATE_TEXT = 'DATE';
    const VAR_TYPE_DATE_CODE = 4;

    const VAR_TYPE_JSON = 'JSN';
    const VAR_TYPE_JSON_TEXT = 'JSON';
    const VAR_TYPE_JSON_CODE = 5;
	
    const VAR_TYPE_RELAY_DATA = 'RDATA';
    const VAR_TYPE_RELAY_DATA_TEXT = 'Relay Data';

    const VAR_TYPE_ARRAY = 'ARY';
    const VAR_TYPE_ARRAY_TEXT = 'ARRAY';

    const VAR_TYPE_STR_TO_CODE = [
        self::VAR_TYPE_INTEGER => self::VAR_TYPE_INTEGER_CODE,
        self::VAR_TYPE_STRING  => self::VAR_TYPE_STRING_CODE,
        self::VAR_TYPE_BOOLEAN => self::VAR_TYPE_BOOLEAN_CODE,
        self::VAR_TYPE_DATE    => self::VAR_TYPE_DATE_CODE,
        self::VAR_TYPE_JSON    => self::VAR_TYPE_JSON_CODE,
    ];

    const VAR_TYPE_CODE_TO_STR = [
        self::VAR_TYPE_INTEGER_CODE => self::VAR_TYPE_INTEGER,
        self::VAR_TYPE_STRING_CODE  => self::VAR_TYPE_STRING,
        self::VAR_TYPE_BOOLEAN_CODE => self::VAR_TYPE_BOOLEAN,
        self::VAR_TYPE_DATE_CODE    => self::VAR_TYPE_DATE,
        self::VAR_TYPE_JSON_CODE    => self::VAR_TYPE_JSON,
    ];

    const VAR_TYPE_NULL = '';
    const VAR_TYPE_NULL_VAL = '--select--';
    const REQUEST_VAR_TYPES = [
        self::VAR_TYPE_NULL    => self::VAR_TYPE_NULL_VAL,
        self::VAR_TYPE_INTEGER => self::VAR_TYPE_INTEGER_TEXT,
        self::VAR_TYPE_STRING  => self::VAR_TYPE_STRING_TEXT,
        self::VAR_TYPE_BOOLEAN => self::VAR_TYPE_BOOLEAN_TEXT,
        self::VAR_TYPE_JSON    => self::VAR_TYPE_JSON_TEXT,
        self::VAR_TYPE_DATE    => self::VAR_TYPE_DATE_TEXT,
        //self::VAR_TYPE_RELAY_DATA => self::VAR_TYPE_RELAY_DATA_TEXT
    ];
	
	const RESPONSE_VAR_TYPES = [
        self::VAR_TYPE_NULL    => self::VAR_TYPE_NULL_VAL,
        self::VAR_TYPE_INTEGER => self::VAR_TYPE_INTEGER_TEXT,
        self::VAR_TYPE_STRING  => self::VAR_TYPE_STRING_TEXT,
        self::VAR_TYPE_BOOLEAN => self::VAR_TYPE_BOOLEAN_TEXT,
        self::VAR_TYPE_JSON    => self::VAR_TYPE_JSON_TEXT,
        //self::VAR_TYPE_ARRAY   => self::VAR_TYPE_ARRAY_TEXT,
        self::VAR_TYPE_DATE    => self::VAR_TYPE_DATE_TEXT
    ];

	/** Sample Source */
    const SAMPLE_SOURCE_TYPE_CURL_CODE      = 1;
    const SAMPLE_SOURCE_TYPE_JQUERY_CODE    = 2;
    const SAMPLE_SOURCE_TYPE_RUBY_CODE      = 3;
    const SAMPLE_SOURCE_TYPE_PYTHON_CODE    = 4;
    const SAMPLE_SOURCE_TYPE_NODE_CODE      = 5;
    const SAMPLE_SOURCE_TYPE_PHP_CODE       = 6;
    const SAMPLE_SOURCE_TYPE_GO_CODE        = 7;

	const SAMPLE_SOURCE_TYPE_CURL   = 'cURL';
    const SAMPLE_SOURCE_TYPE_JQUERY = 'jQuery';
    const SAMPLE_SOURCE_TYPE_RUBY   = 'Ruby';
    const SAMPLE_SOURCE_TYPE_PYTHON = 'Python Requests';
    const SAMPLE_SOURCE_TYPE_NODE   = 'Node';
    const SAMPLE_SOURCE_TYPE_PHP    = 'PHP';
    const SAMPLE_SOURCE_TYPE_GO     = 'Go';

    const SAMPLE_SOURCE_TYPE = [
        self::SAMPLE_SOURCE_TYPE_CURL_CODE   => self::SAMPLE_SOURCE_TYPE_CURL,
        self::SAMPLE_SOURCE_TYPE_JQUERY_CODE => self::SAMPLE_SOURCE_TYPE_JQUERY,
        //self::SAMPLE_SOURCE_TYPE_RUBY_CODE   => self::SAMPLE_SOURCE_TYPE_RUBY,
        //self::SAMPLE_SOURCE_TYPE_PYTHON_CODE => self::SAMPLE_SOURCE_TYPE_PYTHON,
        self::SAMPLE_SOURCE_TYPE_NODE_CODE   => self::SAMPLE_SOURCE_TYPE_NODE,
        self::SAMPLE_SOURCE_TYPE_PHP_CODE    => self::SAMPLE_SOURCE_TYPE_PHP,
        //self::SAMPLE_SOURCE_TYPE_GO_CODE     => self::SAMPLE_SOURCE_TYPE_GO,
    ];

    /** 배포할 환경 / 1=Mock Up, 2=DEV, 3=UAT, 4=Product */
    const DEPLOY_TARGET_MOK = 1;
    const DEPLOY_TARGET_DEV = 2;
    const DEPLOY_TARGET_UAT = 3;
    const DEPLOY_TARGET_PRD = 4;

    /** 계정 타입 */
    const ACCOUNT_SUSER = 0;
    const ACCOUNT_OWNER = 10;
    const ACCOUNT_PARTNER = 20;

    /** 계정 상태 */
    //1=inactive, 2=active, 3=deactivated
    const ACCOUNT_STATUS_INACTIVE = 1;
    const ACCOUNT_STATUS_ACTIVE = 2;
    const ACCOUNT_STATUS_DEACTIVE = 3;

    /** Header Contents-Type */
    const HTTP_HEADER_CONTENTS_TYPE_FORM_DATA_CODE              = 1;
    const HTTP_HEADER_CONTENTS_TYPE_JSON_CODE                   = 2;
    const HTTP_HEADER_CONTENTS_TYPE_WWW_FORM_URLENCODED_CODE    = 3;
    const HTTP_HEADER_CONTENTS_TYPE_XML_CODE                    = 4;

    const HTTP_HEADER_CONTENTS_TYPE_FORM_DATA_STR           = 'form-data (non-header)';
    const HTTP_HEADER_CONTENTS_TYPE_JSON_STR                = 'application/json';
    const HTTP_HEADER_CONTENTS_TYPE_WWW_FORM_URLENCODED_STR = 'application/x-www-form-urlencoded';
    const HTTP_HEADER_CONTENTS_TYPE_XML_STR                 = 'application/xml';

    const HTTP_HEADER_CONTENTS_TYPE = [
        self::HTTP_HEADER_CONTENTS_TYPE_FORM_DATA_CODE           => self::HTTP_HEADER_CONTENTS_TYPE_FORM_DATA_STR,
        self::HTTP_HEADER_CONTENTS_TYPE_JSON_CODE                => self::HTTP_HEADER_CONTENTS_TYPE_JSON_STR,
        self::HTTP_HEADER_CONTENTS_TYPE_WWW_FORM_URLENCODED_CODE => self::HTTP_HEADER_CONTENTS_TYPE_WWW_FORM_URLENCODED_STR,
        self::HTTP_HEADER_CONTENTS_TYPE_XML_CODE                 => self::HTTP_HEADER_CONTENTS_TYPE_XML_STR,
    ];

    /** API Auth Type */
    const API_AUTH_NO_ATUH = 0;
    const API_AUTH_BASIC = 1;
    const API_AUTH_BEARER_TOKEN = 2;

    /** 컨테이터의 타입 코드 */
    const CONTAINER_TYPE_NONE  = 0;
    const CONTAINER_TYPE_ALT   = 1;
    const CONTAINER_TYPE_LOOP  = 2;
    const CONTAINER_TYPE_ASYNC = 3;

    /** 컨트롤의 연산자 목록 */
    const CONTROLL_OPERATORS = [
        1 => '==',
        2 => '>',
        3 => '<',
        4 => '>=',
        5 => '<=',
        6 => '<>',
    ];

    const BUNIT_TEST_HEADER = 'Synctree-Studio-Test';

}