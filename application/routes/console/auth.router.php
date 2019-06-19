<?php

$app->group('/auth', function () {
    $this->get('', 'controllers\console\Auth:getSigninData')->setName('signin');
    $this->get('/', 'controllers\console\Auth:getSigninData')->setName('signin');
    $this->get('/signin', 'controllers\console\Auth:getSigninData')->setName('signin');
    $this->post('/signinCallback', 'controllers\console\Auth:signinCallback');
    $this->get('/signout', 'controllers\console\Auth:signout')->setName('signout');
	$this->post('/forget', 'controllers\console\Auth:forgetPassword')->setName('forgot');
    $this->get('/partner/{key}', 'controllers\console\Auth:partnersSignin')->setName('PartnersSignin');
})->add(new \middleware\Common($app->getContainer(), false))
;

$app->group('/{lang:[A-Z]{2}}/auth', function () {
    $this->get('', 'controllers\console\Auth:getSigninData')->setName('signin');
    $this->get('/', 'controllers\console\Auth:getSigninData')->setName('signin');
    $this->get('/signin', 'controllers\console\Auth:getSigninData')->setName('signin');
    $this->post('/signinCallback', 'controllers\console\Auth:signinCallback');
    $this->get('/signout', 'controllers\console\Auth:signout')->setName('signout');
	$this->post('/forget', 'controllers\console\Auth:forgetPassword')->setName('forgot');
	$this->get('/partner/{key}', 'controllers\console\Auth:partnersSignin')->setName('PartnersSignin');
})->add(new \middleware\Common($app->getContainer(), false))
;




