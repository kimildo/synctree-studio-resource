<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 2019-02-08
 * Time: 오후 12:41
 */

namespace controllers\console;

use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Container\ContainerInterface;

use libraries\{
    log\LogMessage,
    constant\CommonConst,
    constant\ErrorConst,
    util\CommonUtil,
    util\RedisUtil
};

class Frontend extends SynctreeConsole
{
    public function __construct(ContainerInterface $ci)
    {
        parent::__construct($ci);
    }
    public function index(Request $request, Response $response)
    {
        $params = $request->getAttribute('params');


        $csrfData = $this->_addCsrfToken();
        $rememberEmail = isset($_COOKIE['remember']) ? $_COOKIE['remember'] : null;

        if (isset($params['code']) && !empty($params['code'])) {
            $tempData = RedisUtil::getData($this->redis, $params['code'], CommonConst::REDIS_PARTNERS_SESSION);
        }

        $this->renderer->render($response, 'index.twig', [
            'page_title'     => 'Welcome',
            'page_desc'      => 'Synctree Studio V2.0',
            'share_url'      => CommonUtil::getBaseUrl(),
            'share_image'    => CommonConst::AWS_S3_END_POINT . '/static/img/logo/synctree_logo_s.jpg',
            'dictionary'     => $this->dictionary,
            'csrf'           => $csrfData,
            'extr_page'      => true,
            'SCRIPT_UPDATED' => CommonConst::SCRIPT_UPDATED,
            'CSS_UPDATED'    => CommonConst::CSS_UPDATED,
            'domain'         => CommonUtil::getDomain(),
            'code'           => $params['code'] ?? null,
            'partner_email'  => $tempData['partner_account_email'] ?? null,
            'remember'       => $rememberEmail,
        ]);

        return $response;
    }

    public function document(Request $request, Response $response, $args)
    {

        $this->renderer->render($response, 'document.twig', [
            'app_id'         => $args['app_id'],
            'biz_id'         => $args['biz_id'],
            'page_desc'      => 'Synctree Studio V2.0',
            'share_url'      => CommonUtil::getBaseUrl(),
            'share_image'    => CommonConst::AWS_S3_END_POINT . '/static/img/logo/synctree_logo_s.jpg',
            'dictionary'     => $this->dictionary,
            'extr_page'      => true,
            'SCRIPT_UPDATED' => CommonConst::SCRIPT_UPDATED,
            'CSS_UPDATED'    => CommonConst::CSS_UPDATED,
            'domain'         => CommonUtil::getDomain(),

        ]);

        return $response;
    }

    public function getCsrf(Request $request, Response $response)
    {
        $results = [
            'csrf' => $this->_addCsrfToken()
        ];
        return $response->withJson($results, ErrorConst::SUCCESS_CODE);
    }
}