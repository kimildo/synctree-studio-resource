<?php

/**
 * 공통으로 쓰는 라우터
 */
$app->group('/', function () {
//    $this->get('', 'controllers\console\Auth:signin')->setName('signin');
    $this->get('/', 'controllers\console\Frontend:index')->setName('main');
    $this->get('docs/{app_id:[0-9]+}/{biz_id:[0-9]+}', 'controllers\console\Frontend:document')->setName('Document');
})->add(new \middleware\Common($app->getContainer(), false))
;

$app->group('/{lang:[A-Z]{2}}', function () {
//    $this->get('/', 'controllers\console\Auth:signin')->setName('signin');
//    $this->get('', 'controllers\console\Auth:signin')->setName('signin');\
    $this->get('', 'controllers\console\Frontend:index')->setName('main');
    $this->get('/', 'controllers\console\Frontend:index')->setName('main');
})->add(new \middleware\Common($app->getContainer(), false))
;