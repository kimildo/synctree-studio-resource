<?php

/**
 * Synctree 추상 클래스
 */

namespace abstraction\classes;

use Slim\Http\Request;
use Slim\Http\Response;

abstract class SynctreeAbstract
{
    public abstract function getCommand(Request $request, Response $response);
}