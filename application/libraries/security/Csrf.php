<?php
namespace libraries\security;

use Slim\Csrf\Guard;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Csrf
{
    public static function addCsrfToken(Guard $csrf, $byPassValue = [])
    {
        $csrf->validateStorage();

        /*
         * generate new tokens
         */
        $keyPair = $csrf->generateToken();

        $byPassValue['csrf_name'] = $keyPair['csrf_name'];
        $byPassValue['csrf_value'] = $keyPair['csrf_value'];

        return $byPassValue;
    }

    public static function checkCsrf(Guard $csrf, $params)
    {
        if (!isset($params['csrf_name']) || !isset($params['csrf_value'])) {
            return false;
        }

        $csrf->validateStorage();
        
        /*
         * validate tokens
         */
        $result = $csrf->validateToken($params['csrf_name'], $params['csrf_value']);

        if (true !== $result) {
            return false;
        }

        return $result;
    }
}
