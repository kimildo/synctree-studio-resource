<?php

$app->group('/partner', function () {
    $this->get('/signup/{key}', 'controllers\console\Partners:signup')->setName('PartnersSignUp');
    $this->post('/operatorModifyCallback', 'controllers\console\Partners:operatorModifyCallback');
    $this->post('/passwordSetCallback', 'controllers\console\Partners:passwordSetCallback');
})->add(new \middleware\Common($app->getContainer(), false))
;

$app->group('/{lang:[A-Z]{2}}/partner', function () {

    $this->get('/signup/{key}', 'controllers\console\Partners:signup')->setName('PartnersSignUp');
//	$this->post('/signupCallback', 'controllers\console\Partners:signupCallback');
//	$this->get('/signin/{key}', 'controllers\console\Partners:signin')->setName('PartnersSignIn');
//	$this->post('/signinCallback', 'controllers\console\Partners:signinCallback');
//	$this->get('/bunit', 'controllers\console\Partners:bunit')->setName('PartnersBizUnit');

})->add(new \middleware\Common($app->getContainer(), false))
;