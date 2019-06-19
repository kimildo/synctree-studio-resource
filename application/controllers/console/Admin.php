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
    util\RedisUtil
};

class Admin extends SynctreeConsole
{
    public function __construct(ContainerInterface $ci)
    {
        parent::__construct($ci);
    }

    public function index(Request $request, Response $response)
    {
        try {

            $this->renderer->render($response, 'admin/index.twig', $this->viewData);

        } catch (\Exception $ex) {
            $this->_viewErrorMessage($this->_getErrorMessage($ex));
        }

        return $response;

    }


}