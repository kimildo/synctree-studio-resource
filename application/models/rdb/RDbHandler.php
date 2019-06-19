<?php

namespace models\rdb;

use Slim\PDO\Database as PDO;
use models\rdb\RdbMgr;

use libraries\constant\ErrorConst;
use libraries\util\CommonUtil;
use libraries\log\LogMessage;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class RDbHandler
{
    /** rdb secure key */
    const RDB_SALT = 'rdb_salt';
    const RDB_KEY_PASSWORD = 'rdb_key_password';

    const PROCEDURE_RETURN_VAR          = '@po_int_return';

    /** AUTH  */
    const PROCEDURE_LOGIN               = 'usp_login (?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_LOGOUT              = 'usp_op_logout (?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_GET_TEAM            = 'usp_get_team (?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';

    /** Partner */
    const PROCEDURE_LINK_OP_TO_PATNER   = 'usp_connect_partner_and_operation (?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_GET_PARTNER_LIST     = 'usp_get_list_partner (?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_GET_PARTNER_INFO     = 'usp_get_partner_account (?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_SET_PARTNER_INFO     = 'usp_activate_partner  (?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';


    /** App */
    const PROCEDURE_GET_APP_LIST        = 'usp_get_list_application (?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_ADD_APP             = 'usp_add_application (?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_MOD_APP             = 'usp_mod_application (?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';

    /** Biz Ops */
    const PROCEDURE_GET_BIZ_LIST          = 'usp_get_list_biz_ops (?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_GET_BIZ               = 'usp_get_biz_ops (?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_ADD_BIZ               = 'usp_add_biz_ops (?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_MOD_BIZ               = 'usp_mod_biz_ops (?, ?, ?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_MOD_BIZ_V2            = 'usp_mod_biz_ops_v2 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_MOD_BIZ_V3            = 'usp_mod_biz_ops_v3 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_DEL_BIZ               = 'usp_del_biz_ops (?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_SET_BIZ_PARAMS        = 'usp_set_biz_ops_parameter (?, ?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_GET_BIZ_PARAMS        = 'usp_get_list_biz_ops_parameter (?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_GET_BIZ_PARAMS_IN     = 'usp_get_list_biz_ops_parameter_in (?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_GET_BIZ_FOR_REFERENCE = 'usp_get_list_biz_ops_refer_to_operation (?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';

    /** Deploy */
    const PROCEDURE_GET_BIZ_DEPLOY_LIST   = 'usp_get_list_biz_ops_deployment (?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_SET_BIZ_DEPLOY        = 'usp_add_biz_ops_deployment (?, ?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_GET_BIZ_BUILD_LIST   = 'usp_get_list_biz_ops_version (?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_ADD_BIZ_BUILD         = 'usp_add_biz_ops_version (?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';

    /** Operations */
    const PROCEDURE_GET_OPS_LIST            = 'usp_get_list_operation (?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_GET_OPS                 = 'usp_get_operation (?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';

    /** deprecated  */
    const PROCEDURE_ADD_OPS                 = 'usp_add_operation (?, ?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_MOD_OPS                 = 'usp_mod_operation (?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_ADD_OPS_V2              = 'usp_add_operation_v2 (?, ?, ?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_MOD_OPS_V2              = 'usp_mod_operation_v2 (?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    /** deprecated  */

    const PROCEDURE_ADD_OPS_V3              = 'usp_add_operation_v3 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_ADD_OPS_V4              = 'usp_add_operation_v4 (?, ?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_MOD_OPS_V3              = 'usp_mod_operation_v3 (?, ?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_MOD_OPS_V4              = 'usp_mod_operation_v4 (?, ?, ?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';

    const PROCEDURE_DEL_OPS                 = 'usp_del_operation (?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_SET_OPS_PARAMS          = 'usp_set_operation_parameter (?, ?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_GET_OPS_PARAMS          = 'usp_get_list_operation_parameter (?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_BIND_OPS                = 'usp_bind_operation (?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_BIND_OPS_V2             = 'usp_bind_operation_v2 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';

    const PROCEDURE_UNBIND_OPS              = 'usp_unbind_operation (?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_UNBIND_OPSS             = 'usp_unbind_operations (?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';

    const PROCEDURE_GET_BIND_OPS            = 'usp_get_list_binding_operation (?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_OPERATION_FOR_REFERENCE = 'usp_get_list_operation_for_reference (?, ?, ?, ?, ?, ?,' . self::PROCEDURE_RETURN_VAR . ')';

    /** argument */
    const PROCEDURE_GET_ARGUMENT_LIST   = 'usp_get_list_argument (?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_SET_ARGUMENT        = 'usp_set_argument (?, ?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_DEL_ARGUMENT        = 'usp_del_argument (?, ?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';

    /** Controll alt */
    const PROCEDURE_ADD_ALT             = 'usp_add_control_alt (?, ?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_ADD_ALT_V2          = 'usp_add_control_alt_v2 (?, ?, ?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_DEL_ALT             = 'usp_del_control_alt (?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_MOD_ALT             = 'usp_mod_control_alt (?, ?, ?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_MOD_ALT_V2          = 'usp_mod_control_alt_v2 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_MOD_ALT_OP          = 'usp_mod_operation_control_container_info  (?, ?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_SET_ASYNC           = 'usp_set_async_range (?, ?, ?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';
    const PROCEDURE_UNSET_ASYNC         = 'usp_del_async_range (?, ?, ?, ?, ?, ' . self::PROCEDURE_RETURN_VAR . ')';


    static private $dt;
    static private $salt;
    static private $ipAddress;
    static private $keyPassword;

    /**
     * set Default Vars
     * @throws \Exception
     */
    private static function _setInitVars()
    {
        self::$dt           = date('Y-m-d H:i:s');
        self::$salt         = CommonUtil::getSecureConfig(self::RDB_SALT);
        self::$keyPassword  = CommonUtil::getSecureConfig(self::RDB_KEY_PASSWORD);
        self::$ipAddress    = CommonUtil::getUserIP();
    }

    /**
     * 로그인 수행
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeAdminLogin(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['email'], PDO::PARAM_STR],
            [2, $parameter['password'], PDO::PARAM_STR],
            [3, self::$salt, PDO::PARAM_STR],
            [4, self::$dt, PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_LOGIN);
    }

    /**
     * 관리자 로그아웃
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeAdminLogout(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['admin_id'], PDO::PARAM_STR],
            [2, self::$dt, PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_LOGOUT);
    }

    /**
     * 팀 속성을 조회합니다.
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeGetTeamInfo(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],   PDO::PARAM_INT],
            [2, $parameter['team_id'],      PDO::PARAM_INT],
            [3, self::$dt,                  PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_GET_TEAM);
    }



    /**
     * 어플리케이션 리스트
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeGetAppList(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],   PDO::PARAM_INT],
            [2, $parameter['team_id'],      PDO::PARAM_INT],
            [3, $parameter['archive_flag'], PDO::PARAM_INT],
            [4, self::$dt,                  PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_GET_APP_LIST);
    }

    /**
     * 어플리케이션 추가
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeAddApp(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],       PDO::PARAM_INT],
            [2, $parameter['team_id'],          PDO::PARAM_INT],
            [3, $parameter['app_name'],         PDO::PARAM_STR],
            [4, $parameter['app_type'],         PDO::PARAM_INT],
            [5, $parameter['app_desc'],         PDO::PARAM_STR],
            [6, self::$dt,                      PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_ADD_APP);
    }

    /**
     * 어플리케이션 수정
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeModifyApp(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],       PDO::PARAM_INT],
            [2, $parameter['team_id'],          PDO::PARAM_INT],
            [3, $parameter['app_id'],           PDO::PARAM_STR],
            [4, $parameter['app_name'],         PDO::PARAM_INT],
            [5, $parameter['app_desc'],         PDO::PARAM_STR],
            [6, $parameter['archive_flag'],     PDO::PARAM_INT],
            [7, self::$dt,                      PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_MOD_APP);
    }

    /**
     * 비즈옵스 리스트
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeGetBizList(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],       PDO::PARAM_INT],
            [2, $parameter['team_id'],          PDO::PARAM_INT],
            [3, $parameter['application_id'],   PDO::PARAM_INT],
            [4, self::$dt,                      PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_GET_BIZ_LIST);
    }

    /**
     * 비즈옵스 속성
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeGetBiz(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],       PDO::PARAM_INT],
            [2, $parameter['team_id'],          PDO::PARAM_INT],
            [3, $parameter['application_id'],   PDO::PARAM_INT],
            [4, $parameter['biz_ops_id'],       PDO::PARAM_INT],
            [5, self::$dt,                      PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_GET_BIZ);
    }


    /**
     * 비즈옵스 추가
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeAddBiz(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],           PDO::PARAM_INT],
            [2, $parameter['team_id'],              PDO::PARAM_INT],
            [3, $parameter['application_id'],       PDO::PARAM_INT],
            [4, $parameter['biz_ops_name'],         PDO::PARAM_STR],
            [5, $parameter['biz_ops_description'],  PDO::PARAM_STR],
            [6, self::$dt,                          PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_ADD_BIZ);
    }


    /**
     * 비즈옵스 수정
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeModifyBiz(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],           PDO::PARAM_INT],
            [2, $parameter['team_id'],              PDO::PARAM_INT],
            [3, $parameter['application_id'],       PDO::PARAM_INT],
            [4, $parameter['biz_ops_id'],           PDO::PARAM_INT],
            [5, $parameter['biz_ops_name'],         PDO::PARAM_STR],
            [6, $parameter['biz_ops_description'],  PDO::PARAM_STR],
            [7, $parameter['actor_alias'],          PDO::PARAM_STR],
            [8, $parameter['protocol_type_code'],   PDO::PARAM_INT],
            [9, $parameter['request_method_code'],  PDO::PARAM_INT],
            [10, $parameter['cache_flag'],          PDO::PARAM_INT],
            [11, $parameter['cache_expire_time'],   PDO::PARAM_INT],
            [12, self::$dt,                         PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_MOD_BIZ_V3);
    }

    /**
     * 비즈옵스 파라미터 저장
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeSetBizParams(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],           PDO::PARAM_INT],
            [2, $parameter['team_id'],              PDO::PARAM_INT],
            [3, $parameter['application_id'],       PDO::PARAM_INT],
            [4, $parameter['biz_ops_id'],           PDO::PARAM_INT],
            [5, $parameter['protocol_type_code'],   PDO::PARAM_INT],
            [6, $parameter['request_method_code'],  PDO::PARAM_INT],
            [7, $parameter['parameters'],           PDO::PARAM_STR],
            [8, self::$dt,                          PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_SET_BIZ_PARAMS);
    }

    /**
     * 비즈옵스 파라미터 리스트
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeGetBizParams(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],           PDO::PARAM_INT],
            [2, $parameter['team_id'],              PDO::PARAM_INT],
            [3, $parameter['application_id'],       PDO::PARAM_INT],
            [4, $parameter['biz_ops_id'],           PDO::PARAM_INT],
            [5, self::$dt,                          PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_GET_BIZ_PARAMS);
    }

    /**
     * 비즈옵스 IN 파라미터 리스트
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeGetBizReqParams(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],           PDO::PARAM_INT],
            [2, $parameter['team_id'],              PDO::PARAM_INT],
            [3, $parameter['application_id'],       PDO::PARAM_INT],
            [4, $parameter['biz_ops_id'],           PDO::PARAM_INT],
            [5, self::$dt,                          PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_GET_BIZ_PARAMS_IN);
    }

    /**
     * 비즈옵스 삭제
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeDelBizParams(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],           PDO::PARAM_INT],
            [2, $parameter['team_id'],              PDO::PARAM_INT],
            [3, $parameter['application_id'],       PDO::PARAM_INT],
            [4, $parameter['biz_ops_id'],           PDO::PARAM_INT],
            [5, self::$dt,                          PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_DEL_BIZ);
    }

    /**
     * 특정 오퍼레이션을 참조하는 비즈 옵스 목록을 조회합니다.
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeGetBizReferOperation(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],   PDO::PARAM_INT],
            [2, $parameter['team_id'],      PDO::PARAM_INT],
            [3, $parameter['operation_id'], PDO::PARAM_INT],
            [4, self::$dt,                  PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_GET_BIZ_FOR_REFERENCE);
    }

    /**
     * 비즈 옵스 배포 목록을 조회합니다.
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeGetBizDeployList(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],           PDO::PARAM_INT],
            [2, $parameter['team_id'],              PDO::PARAM_INT],
            [3, $parameter['application_id'],       PDO::PARAM_INT],
            [4, $parameter['biz_ops_id'],           PDO::PARAM_INT],
            [5, $parameter['environment_code'],     PDO::PARAM_INT],
            [6, self::$dt,                          PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_GET_BIZ_DEPLOY_LIST);
    }

    /**
     * 비즈 옵스를 빌드한 버전 목록을 조회합니다.
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeGetBizBuildList(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],           PDO::PARAM_INT],
            [2, $parameter['team_id'],              PDO::PARAM_INT],
            [3, $parameter['application_id'],       PDO::PARAM_INT],
            [4, $parameter['biz_ops_id'],           PDO::PARAM_INT],
            [5, self::$dt,                          PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_GET_BIZ_BUILD_LIST);
    }

    /**
     * 비즈 옵스를 배포합니다.
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeSetBizDeploy(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],           PDO::PARAM_INT],
            [2, $parameter['team_id'],              PDO::PARAM_INT],
            [3, $parameter['application_id'],       PDO::PARAM_INT],
            [4, $parameter['biz_ops_id'],           PDO::PARAM_INT],
            [5, $parameter['biz_ops_version_id'],   PDO::PARAM_STR],
            [6, $parameter['environment_code'],     PDO::PARAM_INT],
            [7, $parameter['deployment_key'],       PDO::PARAM_STR],
            [8, self::$dt,                          PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_SET_BIZ_DEPLOY);
    }

    /**
     * 현재 구성으로 비즈 옵스를 빌드하고 비즈 옵스 버전을 생성
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeAddBizBuild(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],           PDO::PARAM_INT],
            [2, $parameter['team_id'],              PDO::PARAM_INT],
            [3, $parameter['application_id'],       PDO::PARAM_INT],
            [4, $parameter['biz_ops_id'],           PDO::PARAM_INT],
            [5, self::$dt,                          PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_ADD_BIZ_BUILD);
    }

    /**
     * 오퍼레이션 추가
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeAddOps(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],                PDO::PARAM_INT],
            [2, $parameter['team_id'],                   PDO::PARAM_INT],
            [3, $parameter['header_transfer_type_code'], PDO::PARAM_INT],
            [4, $parameter['operation_namespace_name'],  PDO::PARAM_STR],
            [5, $parameter['operation_name'],            PDO::PARAM_STR],
            [6, $parameter['operation_description'],     PDO::PARAM_STR],
            [7, $parameter['auth_type_code'],            PDO::PARAM_INT],
            [8, self::$dt,                               PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_ADD_OPS_V4);
    }

    /**
     * 오퍼레이션 파라미터 셋
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeSetOpsParams(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],           PDO::PARAM_INT],
            [2, $parameter['team_id'],              PDO::PARAM_INT],
            [3, $parameter['operation_id'],         PDO::PARAM_INT],
            [4, $parameter['protocol_type_code'],   PDO::PARAM_INT],
            [5, $parameter['request_method_code'],  PDO::PARAM_INT],
            [6, $parameter['parameters'],           PDO::PARAM_STR],
            [7, $parameter['target_urls'],          PDO::PARAM_STR],
            [8, self::$dt,                          PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_SET_OPS_PARAMS);
    }


    /**
     * 팀에 속한 오퍼레이션 목록을 조회
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeGetOpsList(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],   PDO::PARAM_INT],
            [2, $parameter['team_id'],      PDO::PARAM_INT],
            [3, self::$dt,                  PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_GET_OPS_LIST);
    }

    /**
     * 오퍼레이션 속성 조회
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeGetOpt(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],   PDO::PARAM_INT],
            [2, $parameter['team_id'],      PDO::PARAM_INT],
            [3, $parameter['operation_id'], PDO::PARAM_INT],
            [4, self::$dt,                  PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_GET_OPS, true, true);
    }

    /**
     * 오퍼레이션 파라미터 목록을 조회합니다.
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeGetOptParams(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],   PDO::PARAM_INT],
            [2, $parameter['team_id'],      PDO::PARAM_INT],
            [3, $parameter['operation_id'], PDO::PARAM_INT],
            [4, self::$dt,                  PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_GET_OPS_PARAMS);
    }

    /**
     * 오퍼레이션 삭제
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeRemoveOpt(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],   PDO::PARAM_INT],
            [2, $parameter['team_id'],      PDO::PARAM_INT],
            [3, $parameter['op_id'], PDO::PARAM_INT],
            [4, self::$dt,                  PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_DEL_OPS);
    }

    /**
     * 오퍼레이션 수정
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeModifyOpt(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],                PDO::PARAM_INT],
            [2, $parameter['team_id'],                   PDO::PARAM_INT],
            [3, $parameter['operation_id'],              PDO::PARAM_INT],
            [4, $parameter['header_transfer_type_code'], PDO::PARAM_INT],
            [5, $parameter['operation_namespace_name'],  PDO::PARAM_STR],
            [6, $parameter['operation_name'],            PDO::PARAM_STR],
            [7, $parameter['operation_description'],     PDO::PARAM_STR],
            [8, $parameter['auth_type_code'],            PDO::PARAM_INT],
            [9, self::$dt,                               PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_MOD_OPS_V4);
    }

    /**
     * 비즈 옵스에 오퍼레이션을 연결합니다.
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeBindOpt(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],               PDO::PARAM_INT],
            [2, $parameter['team_id'],                  PDO::PARAM_INT],
            [3, $parameter['application_id'],           PDO::PARAM_INT],
            [4, $parameter['biz_ops_id'],               PDO::PARAM_INT],
            [5, $parameter['binding_seq'],              PDO::PARAM_INT],
            [6, $parameter['operation_id'],             PDO::PARAM_INT],
            [7, $parameter['control_container_code'],   PDO::PARAM_INT],
            [8, $parameter['control_container_info'],   PDO::PARAM_STR],
            [9, $parameter['auth_keys'],                PDO::PARAM_STR],
            [10, self::$dt,                             PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_BIND_OPS_V2);
    }

    /**
     * 비즈 옵스에서 오퍼레이션 연결을 해제합니다.
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeUnbindOpt(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],       PDO::PARAM_INT],
            [2, $parameter['team_id'],          PDO::PARAM_INT],
            [3, $parameter['application_id'],   PDO::PARAM_INT],
            [4, $parameter['biz_ops_id'],       PDO::PARAM_INT],
            [5, $parameter['binding_seq'], PDO::PARAM_INT],
            [6, $parameter['operation_id'],     PDO::PARAM_INT],
            [7, self::$dt,                      PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_UNBIND_OPS);
    }

    /**
     * 비즈 옵스에서 오퍼레이션 연결을 해제합니다. (멀티)
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeUnbindOpts(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],       PDO::PARAM_INT],
            [2, $parameter['team_id'],          PDO::PARAM_INT],
            [3, $parameter['application_id'],   PDO::PARAM_INT],
            [4, $parameter['biz_ops_id'],       PDO::PARAM_INT],
            [5, $parameter['operations'],       PDO::PARAM_STR],
            [6, self::$dt,                      PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_UNBIND_OPSS);
    }

    //usp_unbind_operations


    /**
     * 비즈 옵스에 포함된 오퍼레이션 목록을 조회합니다.
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeGetbindOptList(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],       PDO::PARAM_INT],
            [2, $parameter['team_id'],          PDO::PARAM_INT],
            [3, $parameter['application_id'],   PDO::PARAM_INT],
            [4, $parameter['biz_ops_id'],       PDO::PARAM_INT],
            [5, self::$dt,                      PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_GET_BIND_OPS, true, true);
    }


    /**
     * 전달 인자 목록을 조회합니다.
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeGetArgumentList(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],       PDO::PARAM_INT],
            [2, $parameter['team_id'],          PDO::PARAM_INT],
            [3, $parameter['application_id'],   PDO::PARAM_INT],
            [4, $parameter['biz_ops_id'],       PDO::PARAM_INT],
            [5, $parameter['binding_seq'],      PDO::PARAM_INT],
            [6, $parameter['operation_id'],     PDO::PARAM_INT],
            [7, self::$dt,                      PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_GET_ARGUMENT_LIST);
    }

    /**
     * 오퍼레이터 파라미터에 대한 전달 인자 (argument) 값을 설정합니다.
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeSetArgument(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, (int)$parameter['account_id'],       PDO::PARAM_INT],
            [2, (int)$parameter['team_id'],          PDO::PARAM_INT],
            [3, (int)$parameter['application_id'],   PDO::PARAM_INT],
            [4, (int)$parameter['biz_ops_id'],       PDO::PARAM_INT],
            [5, (int)$parameter['binding_seq'], PDO::PARAM_INT],
            [6, (int)$parameter['operation_id'],     PDO::PARAM_INT],
            [7, $parameter['arguments'],             PDO::PARAM_STR],
            [8, self::$dt,                           PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_SET_ARGUMENT);
    }

    /**
     * 전달 인자를 삭제합니다.
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeDelArgument(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, (int)$parameter['account_id'],       PDO::PARAM_INT],
            [2, (int)$parameter['team_id'],          PDO::PARAM_INT],
            [3, (int)$parameter['application_id'],   PDO::PARAM_INT],
            [4, (int)$parameter['biz_ops_id'],       PDO::PARAM_INT],
            [5, (int)$parameter['binding_seq'], PDO::PARAM_INT],
            [6, (int)$parameter['operation_id'],     PDO::PARAM_INT],
            [7, (int)$parameter['parameter_id'],     PDO::PARAM_INT],
            [8, self::$dt,                           PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_DEL_ARGUMENT);
    }

    /**
     * relay 받을 후보 파라미터가 포함된 오퍼레이션 목록을 조회합니다.
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeGetOperationForReferenceList(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],       PDO::PARAM_INT],
            [2, $parameter['team_id'],          PDO::PARAM_INT],
            [3, $parameter['application_id'],   PDO::PARAM_INT],
            [4, $parameter['biz_ops_id'],       PDO::PARAM_INT],
            [5, $parameter['binding_seq'], PDO::PARAM_INT],
            [6, self::$dt,                      PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_OPERATION_FOR_REFERENCE);
    }


    /**
     * 파트너에게 오퍼레이션을 연결합니다.
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeLinkOpToPartner(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],               PDO::PARAM_INT],
            [2, $parameter['team_id'],                  PDO::PARAM_INT],
            [3, $parameter['partner_account_email'],    PDO::PARAM_STR],
            [4, $parameter['op_id'],                    PDO::PARAM_INT],
            [5, $parameter['expire_date'],              PDO::PARAM_STR],
            [6, $parameter['biz_id'],                   PDO::PARAM_INT],
            [7, self::$dt,                              PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_LINK_OP_TO_PATNER);
    }

    /**
     * 파트너 목록을 조회합니다.
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeGetPartnerList(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],               PDO::PARAM_INT],
            [2, $parameter['team_id'],                  PDO::PARAM_INT],
            [3, self::$dt,                              PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_GET_PARTNER_LIST);
    }

    /**
     * 파트너 계정 조회
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeGetPartnerInfo(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['partner_direct_access_key'], PDO::PARAM_STR],
            [2, self::$dt,                               PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_GET_PARTNER_INFO);
    }

    /**
     * 파트너 계정을 활성화합니다.
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeSetPartnerInfo(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['partner_direct_access_key'], PDO::PARAM_STR],
            [2, $parameter['account_email'], PDO::PARAM_STR],
            [3, $parameter['passphrase'], PDO::PARAM_STR],
            [4, self::$salt, PDO::PARAM_STR],
            [5, self::$dt, PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_SET_PARTNER_INFO);
    }

    /**
     * alternative 컨트롤을 추가합니다.
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeAddContainerAlt(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, (int)$parameter['account_id'],       PDO::PARAM_INT],
            [2, (int)$parameter['team_id'],          PDO::PARAM_INT],
            [3, (int)$parameter['application_id'],   PDO::PARAM_INT],
            [4, (int)$parameter['biz_ops_id'],       PDO::PARAM_INT],
            [5, (int)$parameter['binding_seq'],      PDO::PARAM_INT],
            [6, (int)$parameter['parameter_id'],     PDO::PARAM_INT],
            [7, $parameter['sub_parameter_path'],    PDO::PARAM_STR],
            [8, $parameter['alt_description'],       PDO::PARAM_STR],
            [9, self::$dt,                           PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_ADD_ALT_V2);
    }

    /**
     * alternative 컨트롤을 삭제합니다.
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeDelContainerAlt(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, (int)$parameter['account_id'],       PDO::PARAM_INT],
            [2, (int)$parameter['team_id'],          PDO::PARAM_INT],
            [3, (int)$parameter['application_id'],   PDO::PARAM_INT],
            [4, (int)$parameter['biz_ops_id'],       PDO::PARAM_INT],
            [5, (int)$parameter['control_alt_id'],   PDO::PARAM_INT],
            [6, self::$dt,                           PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_DEL_ALT);
    }


    /**
     * alternative 컨트롤 속성을 수정합니다.
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeModifyContainerAlt(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, (int)$parameter['account_id'],       PDO::PARAM_INT],
            [2, (int)$parameter['team_id'],          PDO::PARAM_INT],
            [3, (int)$parameter['application_id'],   PDO::PARAM_INT],
            [4, (int)$parameter['biz_ops_id'],       PDO::PARAM_INT],
            [5, (int)$parameter['control_alt_id'],   PDO::PARAM_INT],
            [6, (int)$parameter['binding_seq'],      PDO::PARAM_INT],
            [7, (int)$parameter['parameter_id'],     PDO::PARAM_INT],
            [8, $parameter['sub_parameter_path'],    PDO::PARAM_STR],
            [9, $parameter['alt_description'],       PDO::PARAM_STR],
            [10, self::$dt,                          PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_MOD_ALT_V2);
    }

    /**
     * 제어 컨테이너에 속한 오퍼레이션의 제어 컨테이너 정보를 수정합니다.
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeModifyContainerOperation(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],               PDO::PARAM_INT],
            [2, $parameter['team_id'],                  PDO::PARAM_INT],
            [3, $parameter['application_id'],           PDO::PARAM_INT],
            [4, $parameter['biz_ops_id'],               PDO::PARAM_INT],
            [5, $parameter['binding_seq'],              PDO::PARAM_INT],
            [6, $parameter['operation_id'],             PDO::PARAM_INT],
            [7, $parameter['control_container_info'],   PDO::PARAM_STR],
            [8, self::$dt,                              PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_MOD_ALT_OP);
    }

    /**
     * Async. 처리할 오퍼레이션 범위를 설정합니다.
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeSetAsyncRange(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],        PDO::PARAM_INT],
            [2, $parameter['team_id'],           PDO::PARAM_INT],
            [3, $parameter['application_id'],    PDO::PARAM_INT],
            [4, $parameter['biz_ops_id'],        PDO::PARAM_INT],
            [5, $parameter['first_binding_seq'], PDO::PARAM_INT],
            [6, $parameter['last_binding_seq'],  PDO::PARAM_INT],
            [7, self::$dt,                       PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_SET_ASYNC);
    }


    /**
     * Async. 처리할 오퍼레이션 범위를 삭제
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param                    $parameter
     *
     * @return array
     * @throws \Exception
     */
    public static function executeUnsetAsyncRange(RdbMgr $rdb, $parameter)
    {
        self::_setInitVars();

        $params = [
            [1, $parameter['account_id'],        PDO::PARAM_INT],
            [2, $parameter['team_id'],           PDO::PARAM_INT],
            [3, $parameter['application_id'],    PDO::PARAM_INT],
            [4, $parameter['biz_ops_id'],        PDO::PARAM_INT],
            [5, self::$dt,                       PDO::PARAM_STR],
        ];

        return self::_callProcedure($rdb, $params, self::PROCEDURE_UNSET_ASYNC);
    }














    /**
     * 프로시저 호출 메서드
     *
     * @param \models\rdb\RdbMgr $rdb
     * @param array              $params
     * @param                    $procedure
     * @param bool               $isReturnRows
     * @param bool               $multiRowset
     *
     * @return array
     * @throws \Exception
     */
    private static function _callProcedure(RdbMgr $rdb, $params = [], $procedure, $isReturnRows = true, $multiRowset = false)
    {

        try {

            LogMessage::debug('db Params::' . json_encode($params, JSON_UNESCAPED_UNICODE));

            $resultSet  = $rdb->executeProcedure($procedure, $params, $isReturnRows, $multiRowset);
            $resultCode = $rdb->executeQuery('SELECT ' . self::PROCEDURE_RETURN_VAR);
            return [
                'data'       => $resultSet,
                'returnCode' => $resultCode[self::PROCEDURE_RETURN_VAR],
                'message'    => null,
            ];

        } catch (\Exception $ex) {
            LogMessage::error('RDB Error');
            throw $ex;
        }

    }

    /**
     * @param \models\rdb\RdbMgr $rdb
     * @param array              $params
     * @param array              $procedure
     * @param bool               $isReturnRows
     * @param bool               $multiRowset
     *
     * @return array
     */
    private static function _callProcedures(RdbMgr $rdb, $params = [], $procedure = [], $isReturnRows = true, $multiRowset = false)
    {
        try {
            $rdb->startTransaction();
            for ($i = 0; $i < count($procedure); $i++) {
                $resultSet = $rdb->executeProcedure($procedure[$i], $params[$i], ($i === (count($procedure) - 1) ? $isReturnRows : false),
                    ($i === (count($procedure) - 1) ? $multiRowset : false));

                $resultCode = $rdb->executeQuery('SELECT ' . self::PROCEDURE_RETURN_VAR);
                if ($resultCode[self::PROCEDURE_RETURN_VAR] !== 0) {
                    try {
                        $rdb->rollbackTransaction();
                    } catch (\Exception $e) {
                        return [
                            'data'       => null,
                            'returnCode' => ErrorConst::RDB_TRANSACTION_COMMON_ERROR_CODE,
                            'message'    => self::_getResultMessage(ErrorConst::RDB_TRANSACTION_COMMON_ERROR_CODE),
                        ];
                    }

                    return [
                        'data'       => $resultSet,
                        'returnCode' => $resultCode[self::PROCEDURE_RETURN_VAR],
                        'message'    => self::_getResultMessage($resultCode[self::PROCEDURE_RETURN_VAR]),
                    ];
                    break;
                }
            }
            $rdb->commitTransaction();

            return [
                'data'       => $resultSet,
                'returnCode' => $resultCode[self::PROCEDURE_RETURN_VAR],
                'message'    => self::_getResultMessage($resultCode[self::PROCEDURE_RETURN_VAR]),
            ];
        } catch (\Exception $e) {
            return [
                'data'       => null,
                'returnCode' => ErrorConst::RDB_TRANSACTION_COMMON_ERROR_CODE,
                'message'    => self::_getResultMessage(ErrorConst::RDB_TRANSACTION_COMMON_ERROR_CODE),
            ];
        }
    }

    /**
     * 에러 반환
     *
     * @param $resultCode
     *
     * @return null|string
     */
    private static function _getResultMessage($resultCode)
    {
        switch ($resultCode) {
            case ErrorConst::RDB_TRANSACTION_COMMON_ERROR_CODE :
                $resultText = 'Database Error (Transaction. Excetion Error)';
                break;
            case ErrorConst::RDB_COMMON_SUCCESS_CODE :
                $resultText = null;
                break;
            case ErrorConst::RDB_UNKNOWN_ERROR_CODE :
            case ErrorConst::RDB_UNAUTHORIZED_ERROR_CODE :
                $resultText = 'Database Error (No Permission. Excetion Error)';
                break;
            default :
                $resultText = 'Database Error (' . $resultCode . ')';
        }

        return $resultText;
    }
}
