<?php

$app->group('/console', function () {

    $this->group('/deploy', function () {

        $this->post('', 'controllers\console\Deploy:deployment')->setName('deploy');
        $this->post('/', 'controllers\console\Deploy:deployment')->setName('deploy');
        $this->post('/getDeployList', 'controllers\console\Deploy:getDeployList')->setName('getDeployList');
        $this->post('/reDeploy', 'controllers\console\Deploy:reDeploy')->setName('reDeploy');

    });

})
->add(new \middleware\Common($app->getContainer(), false))
->add(new \middleware\CheckLogin($app->getContainer()))
;

$app->group('/{lang:[A-Z]{2}}/console', function () {

//	$this->get('', 'controllers\console\Main:index')->setName('main');
//    $this->get('/', 'controllers\console\Main:index')->setName('main');
//    $this->get('/main', 'controllers\console\Main:index')->setName('main');
//	$this->get('/dashboard', 'controllers\console\Main:dashboard')->setName('dashboard');
//
//	$this->group('/apps', function () {
//		$this->get('', 'controllers\console\Apps:list')->setName('apps');
//		$this->get('/', 'controllers\console\Apps:list')->setName('apps');
//		$this->get('/list', 'controllers\console\Apps:list')->setName('apps');
//
//
//		$this->get('/bunit/{app_id}', 'controllers\console\BizUnit:list')->setName('biz_unit');
//
//
//		$this->get('/bunit', 'controllers\console\BizUnit:list')->setName('biz_unit');
//		$this->get('/bunit/list', 'controllers\console\BizUnit:list')->setName('biz_unit');
//		$this->get('/bunit/modify/{app_id}/{biz_id}', 'controllers\console\BizUnit:modify')->setName('biz_unit');
//
//		$this->get('/op', 'controllers\console\Operator:list')->setName('op');
//		$this->get('/op/modify/{app_id}/{op_id}', 'controllers\console\Operator:modify')->setName('op');
//		$this->get('/op/add', 'controllers\console\Operator:add')->setName('op');
//
//	});

})
->add(new \middleware\Common($app->getContainer(), false))
->add(new \middleware\CheckLogin($app->getContainer()))
;
