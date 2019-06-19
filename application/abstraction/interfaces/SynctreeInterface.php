<?php

namespace abstraction\interfacess;

use Slim\Http\Request;
use Slim\Http\Response;

interface SynctreeInterface
{
    public function getCommand(Request $request, Response $response);
}