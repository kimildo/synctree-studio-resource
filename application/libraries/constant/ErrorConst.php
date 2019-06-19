<?php
namespace libraries\constant;


class ErrorConst
{
    const SUCCESS_CODE                      = 200;
    const MOVE_CODE                         = 302;
    const FAIL_CODE                         = 500;

    const SUCCESS_STRING                    = 'success';
    const FAIL_STRING                       = 'fail';
    const SESSION_EXPIRED                   = 'session_expired';

    /** 에러 그룹 */
    const ERROR_GROUP_ADD_BIZ               = 80001;
    const ERROR_GROUP_MOD_BIZ               = 80002;
    const ERROR_GROUP_DEL_BIZ               = 80003;
    const ERROR_GROUP_DEPLOY_BIZ            = 80004;
    const ERROR_GROUP_REDEPLOY_BIZ          = 80005;
    const ERROR_GROUP_ADD_OPS               = 80011;
    const ERROR_GROUP_MOD_OPS               = 80012;
    const ERROR_GROUP_DEL_OPS               = 80013;


    const ERROR_NOT_FOUND_REQUIRE_FIELD     = 90001;
    const ERROR_SIGNIN_FAIL                 = 90002;
    const ERROR_CSRF_FAIL                   = 90003;
    const ERROR_STEP_FAIL                   = 90004;
    const ERROR_NOT_FOUND_FILE              = 90005;
    const ERROR_NOT_VALID_TOKEN             = 90006;
    const ERROR_NOT_EXIST_APP               = 90007;
    const ERROR_SESSION_EXPIRED             = 90008;
    const ERROR_SESSION_EXPIRED_PARTNER     = 90009;
    const ERROR_GEN_CONTROLLER              = 90010;
    const ERROR_GEN_ROUTE                   = 90011;
    const ERROR_FILE_UPLOAD                 = 90012;
    const ERROR_FAIL_DEPLOY                 = 90013;
    const ERROR_ALEADY_DEPLOY               = 90014;
    const ERROR_NOT_EXIST_DEPLOY            = 90015;
    const ERROR_NOT_EXIST_UNBIND_OPS        = 90016;
    const ERROR_NOT_ALLOW_REQ_METHOD        = 90017;
    const ERROR_NOT_EQUAL_PASSWORD          = 90018;
    const ERROR_GEN_DOCS                    = 90019;
    const ERROR_SEND_EMAIL                  = 90020;
    const ERROR_REQUEST_METHOD_NOT_DEFINED  = 90021;
    const ERROR_REQUEST_KEY_NOT_DEFINED     = 90022;
    const ERROR_OPERATOR_SERVER             = 90023;


    /** default RDB error code */
    const RDB_COMMON_SUCCESS_CODE           = 0;
    const RDB_TRANSACTION_COMMON_ERROR_CODE = -2;
    const RDB_UNKNOWN_ERROR_CODE            = -1;
    const RDB_UNAUTHORIZED_ERROR_CODE       = 2;

    /** RDB */
    const ERROR_UNKNOWN_CODE                      = 0;
    const ERROR_RDB_NO_DATA_EXIST                 = 50000;
    const ERROR_RDB_AUTH_FAIL                     = 50001;
    const ERROR_RDB_APP_AUTH_FAIL                 = 50005;
    const ERROR_RDB_BIZ_AUTH_FAIL                 = 50006;
    const ERROR_RDB_ADD_APP_DUP                   = 50010;
    const ERROR_RDB_APP_ACC_AUTH_FAIL             = 50011;
    const ERROR_RDB_ADD_BIZ_DUP                   = 50012;
    const ERROR_RDB_BIZ_ACC_FAIL                  = 50013;
    const ERROR_RDB_OP_ACC_FAIL                   = 50014;
    const ERROR_RDB_OP_MODIFY_FAIL_SETED_PARAM    = 50016;
    const ERROR_RDB_NOT_EXIST_PARAM               = 50017;
    const ERROR_RDB_NOT_EXIST_TARGET_PARAM        = 50018;
    const ERROR_RDB_BIZ_NOT_REFER_OPS             = 50019;
    const ERROR_RDB_NOT_EXIST_ARGUMENT            = 50020;
    const ERROR_RDB_NOT_EXIST_BUILD               = 50021;
    const ERROR_RDB_ALEADY_EXIST_DEPLOY_KEY       = 50022;
    const ERROR_RDB_NOT_EXIST_PARTNER_ACCOUNT     = 50023;
    const ERROR_RDB_ALEADY_EXIST_TEAM_DOMAIN      = 50024;
    const ERROR_RDB_INVALID_INVITE_PERMISSION     = 50025;
    const ERROR_RDB_ALEADY_EXIST_EMAIL            = 50026;
    const ERROR_RDB_BIZ_REMOVE_FAIL_HAS_DEPLOY    = 50027;
    const ERROR_RDB_ALEADY_EXIST_OP               = 50028;
    const ERROR_RDB_CANNOT_BE_SELETED_PARAMETER   = 50029;
    const ERROR_RDB_EXIST_OPERATOR_IN_CONTROL     = 50030;
    const ERROR_RDB_INVALID_RELAY_SUB_PRAMETER_PATH         = 50033;
    const ERROR_RDB_CANNOT_DEL_REFERENCED_PARAM             = 50034;
    const ERROR_RDB_CANNOT_DEL_REFERENCED_PARAMETER_PATH    = 50035;
    const ERROR_RDB_INVALID_SUB_PRAMETER_PATH               = 50036;

    const ERROR_DESCRIPTION_ARRAY = [
        self::ERROR_UNKNOWN_CODE               => '일시적인 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.',
        self::ERROR_NOT_FOUND_REQUIRE_FIELD    => '필수 파라미터가 존재하지 않습니다.',
        self::ERROR_SIGNIN_FAIL                => '로그인에 실패하였습니다.',
        self::ERROR_CSRF_FAIL                  => 'Failed to check csrf',
        self::ERROR_STEP_FAIL                  => '잘못된 요청입니다.',
        self::ERROR_NOT_FOUND_FILE             => '파일이 존재하지 않습니다.',
        self::ERROR_NOT_VALID_TOKEN            => '잘못된 토큰입니다.',
        self::ERROR_GEN_CONTROLLER             => '컨트롤러 파일 생성 오류',
        self::ERROR_GEN_ROUTE                  => '라우터 파일 생성 오류',
        self::ERROR_NOT_EXIST_APP              => '앱이 존재하지 않습니다. 앱을 추가하세요.',
        self::ERROR_FILE_UPLOAD                => '파일 업로드에 실패하였습니다.',
        self::ERROR_FAIL_DEPLOY                => '배포 요청에 실패하였습니다. 배포가 이미 진행중거나 알 수 없는 에러입니다.',
        self::ERROR_ALEADY_DEPLOY              => '이미 배포요청 진행중 입니다. 잠시 후 다시 시도해주세요.',
        self::ERROR_NOT_EXIST_DEPLOY           => '해당 ID는 배포된 이력이 없거나 배포 실패한 요청입니다.',
        self::ERROR_NOT_EQUAL_PASSWORD         => '패스워드가 같지 않습니다.',
        self::ERROR_NOT_EXIST_UNBIND_OPS       => '연결 해제할 오퍼레이션이 없습니다.',
        self::ERROR_SESSION_EXPIRED            => '세션이 만료되었습니다.',
        self::ERROR_SESSION_EXPIRED_PARTNER    => '파트너 세션이 만료되었습니다.',
        self::ERROR_NOT_ALLOW_REQ_METHOD       => '허용되지 않은 요청타입 입니다.',
        self::ERROR_GEN_DOCS                   => 'DOC 파일 생성 오류',
        self::ERROR_SEND_EMAIL                 => '이메일 발송에 실패하였습니다.',
        self::ERROR_REQUEST_METHOD_NOT_DEFINED => '비즈옵스의 리퀘스트 메소드가 정의 되어있지 않습니다.',
        self::ERROR_REQUEST_KEY_NOT_DEFINED    => '비즈옵스의 리퀘스트 변수가 정의 되어있지 않습니다.',
        self::ERROR_OPERATOR_SERVER            => '오퍼레이터의 서버에 문제가 있습니다.',



        self::ERROR_RDB_NO_DATA_EXIST               => '데이터가 존재하지 않습니다.',
        self::ERROR_RDB_AUTH_FAIL                   => '계정이 존재하지 않거나 패스워드가 일치하지 않습니다.',
        self::ERROR_RDB_APP_AUTH_FAIL               => '권한이 없습니다.',
        self::ERROR_RDB_ADD_APP_DUP                 => '이미 등록된 애플리케이션 이름입니다. 애플리케이션 이름을 변경해주세요.',
        self::ERROR_RDB_BIZ_AUTH_FAIL               => '권한이 없습니다.',
        self::ERROR_RDB_APP_ACC_AUTH_FAIL           => '애플리케이션에 대한 권한이 없습니다.',
        self::ERROR_RDB_ADD_BIZ_DUP                 => '이미 등록된 비즈 옵스 이름입니다.',
        self::ERROR_RDB_BIZ_ACC_FAIL                => '비즈 옵스가 없거나 비즈 옵스에 대한 권한이 없습니다.',
        self::ERROR_RDB_OP_ACC_FAIL                 => '오퍼레이션이 없거나 오퍼레이션에 대한 권한이 없습니다.',
        self::ERROR_RDB_NOT_EXIST_PARAM             => '릴레이할 원본 파라미터가 존재하지 않습니다.',
        self::ERROR_RDB_NOT_EXIST_TARGET_PARAM      => '전달 인자를 설정할 대상 파라미터가 존재하지 않습니다.',
        self::ERROR_RDB_BIZ_NOT_REFER_OPS           => '비즈 옵스가 오퍼레이션을 참조하고 있지 않습니다.',
        self::ERROR_RDB_NOT_EXIST_ARGUMENT          => '전달 인자가 없거나 삭제 권한이 없습니다.',
        self::ERROR_RDB_NOT_EXIST_BUILD             => '배포할 비즈 옵스 빌드가 없습니다.',
        self::ERROR_RDB_ALEADY_EXIST_DEPLOY_KEY     => '이미 등록된 배포 키입니다.',
        self::ERROR_RDB_NOT_EXIST_PARTNER_ACCOUNT   => '활성화할 파트너 계정을 찾을 수 없습니다.',
        self::ERROR_RDB_ALEADY_EXIST_TEAM_DOMAIN    => '이미 존재하는 팀 도메인입니다.',
        self::ERROR_RDB_INVALID_INVITE_PERMISSION   => '초대 받는 사용자가 내 권한보다 상위 권한을 가질 수 없습니다.',
        self::ERROR_RDB_ALEADY_EXIST_EMAIL          => '이미 등록된 이메일 주소입니다.',
        self::ERROR_RDB_BIZ_REMOVE_FAIL_HAS_DEPLOY  => '배포 기록이 있는 비즈 옵스는 삭제할 수 없습니다.',
        self::ERROR_RDB_ALEADY_EXIST_OP             => '이미 등록된 오퍼레이션 이름입니다.',
        self::ERROR_RDB_OP_MODIFY_FAIL_SETED_PARAM  => '전달 인자가 설정된 파라미터를 삭제할 수 없습니다.',
        self::ERROR_RDB_CANNOT_BE_SELETED_PARAMETER => '선택할 수 없는 파라미터입니다.',
        self::ERROR_RDB_EXIST_OPERATOR_IN_CONTROL   => '컨트롤에 속한 오퍼레이션이 있습니다.',
        self::ERROR_RDB_INVALID_RELAY_SUB_PRAMETER_PATH      => 'relay_sub_parameter_path 가 옳지 않습니다.',
        self::ERROR_RDB_CANNOT_DEL_REFERENCED_PARAM          => 'arguments.relay_sub_parameter_path에 의해 참조된 sub parameter는 삭제할 수 없습니다.',
        self::ERROR_RDB_CANNOT_DEL_REFERENCED_PARAMETER_PATH => 'control_alt.sub_parameter_path에 의해 참조된 sub parameter는 삭제할 수 없습니다.',
        self::ERROR_RDB_INVALID_SUB_PRAMETER_PATH            => 'sub_parameter_path 가 옳지 않습니다.',

    ];
}