<?php

$app->group('/console', function () {

    $this->get('/selectApp/{app_id:[0-9]+}', 'controllers\console\Apps:selectApp');
    $this->get('/admin', 'controllers\console\Admin:index')->setName('admin');

    $this->group('/apps', function () {

        $this->get('', 'controllers\console\Apps:list')->setName('apps');
        $this->get('/', 'controllers\console\Apps:list')->setName('apps');
        $this->get('/list', 'controllers\console\Apps:list')->setName('apps');
        $this->get('/getPartnerList', 'controllers\console\Apps:getPartnerList')->setName('apps');
        $this->get('/sendEmail', 'controllers\console\Apps:sendEmail')->setName('apps');

        $this->post('/add', 'controllers\console\Apps:add');
        $this->post('/remove', 'controllers\console\Apps:remove');
        $this->post('/remove/{app_id:[0-9]+}', 'controllers\console\Apps:remove');
        $this->post('/modifyCallback', 'controllers\console\Apps:modifyCallback');

        $this->get('/bunit/{app_id:[0-9]+}', 'controllers\console\BizUnit:list')->setName('apps');
        $this->post('/bunit/add', 'controllers\console\BizUnit:add');
        $this->get('/bunit/modify/{app_id:[0-9]+}/{biz_id:[0-9]+}', 'controllers\console\BizUnit:modify')->setName('apps');
        $this->get('/bunit/getParnerList', 'controllers\console\BizUnit:getParnerList')->setName('apps');

        //$this->post('/bunit/remove', 'controllers\console\BizUnit:remove');
        $this->post('/bunit/remove', 'controllers\console\BizUnit:removeEach');
        $this->post('/bunit/modifyCallback', 'controllers\console\BizUnit:modifyCallback');

        $this->post('/bunit/buildCallback', 'controllers\console\BizUnit:buildCallback');

        $this->post('/bunit/test', 'controllers\console\BizUnit:testCallback');
        $this->post('/bunit/getBizParams', 'controllers\console\BizUnit:getBizParams');

        $this->post('/bunit/makeurl', 'controllers\console\BizUnit:makeExportUrl');
        $this->post('/bunit/getArgumentInfo', 'controllers\console\BizUnit:getArgumentInfo');
        $this->post('/bunit/setArgumentInfo', 'controllers\console\BizUnit:setArgumentInfo');
        $this->post('/bunit/delArgumentInfo', 'controllers\console\BizUnit:delArgumentInfo');
        $this->post('/bunit/getSampleCodes', 'controllers\console\BizUnit:getSampleCodes');


        $this->get('/op/list', 'controllers\console\Operator:list')->setName('apps');
        $this->get('/op/modify/{op_id:[0-9]+}', 'controllers\console\Operator:modify')->setName('apps');
        $this->get('/op/add', 'controllers\console\Operator:add')->setName('apps');

        $this->post('/op/remove', 'controllers\console\Operator:remove');
        $this->post('/op/setOperatorBind', 'controllers\console\Operator:setOperatorBind');
        $this->post('/op/bindOperation', 'controllers\console\Operator:bindOperation');
        $this->post('/op/unbindOperation', 'controllers\console\Operator:unbindOperation');
        $this->post('/op/addCallback', 'controllers\console\Operator:addCallback');
        $this->post('/op/modifyCallback', 'controllers\console\Operator:modifyCallback');
        $this->post('/op/test', 'controllers\console\Operator:testCallback');

        //getControllOperators
        $this->get('/op/getControllOperators', 'controllers\console\Containers:getControllOperators');
        $this->post('/op/bindAlter', 'controllers\console\Containers:bindAlter');
        $this->post('/op/unbindAlter', 'controllers\console\Containers:unbindAlter');
        $this->post('/op/modifyAlterCallback', 'controllers\console\Containers:modifyAlterCallback');

        $this->post('/op/setAsyncRange', 'controllers\console\Containers:setAsyncRange');
        $this->post('/op/unsetAsyncRange', 'controllers\console\Containers:unsetAsyncRange');

    });
})
->add(new \middleware\Common($app->getContainer(), false))
->add(new \middleware\CheckLogin($app->getContainer()))
;

// 문서용 데이터
$app->group('/console/apps', function () {
    $this->post('/bunit/buildCallback/{getData}', 'controllers\console\BizUnit:buildCallback');
})->add(new \middleware\Common($app->getContainer(), false));


$app->group('/console/apps/op', function () {
    $this->post('/getOperator', 'controllers\console\Operator:getOperator');
    $this->post('/getOperators', 'controllers\console\Operator:getOperators');
    $this->post('/getOperatorsByIdx', 'controllers\console\Operator:getOperatorsByIdxs');
    $this->post('/secureTest/{code}/{env:[a-z]{3}}', 'controllers\console\SecureTest:secureTest');


})->add(new \middleware\Common($app->getContainer(), false));


$app->group('/{lang:[A-Z]{2}}/console', function () {

    $this->get('', 'controllers\console\Main:index')->setName('main');
    $this->get('/', 'controllers\console\Main:index')->setName('main');
    $this->get('/main', 'controllers\console\Main:index')->setName('main');
    $this->get('/dashboard', 'controllers\console\Main:dashboard')->setName('dashboard');
    $this->get('/admin', 'controllers\console\Admin:index')->setName('admin');

    $this->group('/apps', function () {

        $this->get('', 'controllers\console\Apps:list')->setName('apps');
        $this->get('/', 'controllers\console\Apps:list')->setName('apps');
        $this->get('/list', 'controllers\console\Apps:list')->setName('apps');

        $this->get('/bunit/{app_id:[0-9]+}', 'controllers\console\BizUnit:list')->setName('apps');

        $this->get('/bunit', 'controllers\console\BizUnit:list')->setName('apps');
        $this->get('/bunit/list', 'controllers\console\BizUnit:list')->setName('apps');
        $this->get('/bunit/modify/{app_id:[0-9]+}/{biz_id:[0-9]+}', 'controllers\console\BizUnit:modify')->setName('apps');

        $this->get('/op/list', 'controllers\console\Operator:list')->setName('apps');
        $this->get('/op/modify/{op_id:[0-9]+}', 'controllers\console\Operator:modify')->setName('apps');
        $this->get('/op/add', 'controllers\console\Operator:add')->setName('apps');

    });

})
    ->add(new \middleware\Common($app->getContainer(), false))
    ->add(new \middleware\CheckLogin($app->getContainer()))
;