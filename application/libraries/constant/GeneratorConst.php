<?php
/**
 * 파일 제네레이터를 위한 상수
 *
 * @author kimildo
 *
 */

namespace libraries\constant;

class GeneratorConst
{
    const GEN_FILE_PREFIX = 'Generate';
    const GEN_USR_DIRECTORY = '/generated/usr/';
    const GEN_CONTOLLER_USR_ROUTE_DIRECTORY = 'controllers\\generated\\usr\\';

    const PATH_RULE = [
        'CONTROLLER' => [
            'PATH'   => 'application/controllers' . self::GEN_USR_DIRECTORY,
            'SUBFIX' => 'Controller.php',
        ],
        'ROUTER'     => [
            'PATH'   => 'application/routes' . self::GEN_USR_DIRECTORY,
            'SUBFIX' => '.router.php',
        ],
        'HTML'       => [
            'PATH'   => 'application/views' . self::GEN_USR_DIRECTORY,
            'SUBFIX' => '.docs.twig',
        ],
        'JSON'       => [
            'PATH'   => 'application/templates/usr/',
            'SUBFIX' => '.json',
        ],
        'CONSTANT'       => [
            'PATH'   => 'application/libraries/constant/',
            'SUBFIX' => '.php',
        ],
    ];

    const GET_COMMAND_URL = CommonConst::GET_COMMAND_URL;
    const GEN_MAIN_METHOD_NAME = 'main';
    const GEN_DOCS_METHOD_NAME = 'docs';
    const GEN_SAMPLECODES_METHOD_NAME = 'getSampleCodes';

    const GEN_SUB_METHOD_PREFIX = '_sub';
    const GEN_ROUTE_PREFIX = '/Gen';
    const GEN_DOCS_FOLDER_NAME = 'docs';
}