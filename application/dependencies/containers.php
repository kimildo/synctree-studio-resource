<?php

// DIC configuration
$container = $app->getContainer();

// redis mgr
$container['redis'] = function (\Psr\Container\ContainerInterface $c) {
    return new \models\redis\RedisMgr($c->get('settings')['redis']);
};


// rdb mgr
$container['rdb'] = function (\Psr\Container\ContainerInterface $c) {
    return new \models\rdb\RdbMgr($c->get('settings')['rdb']);
};

// Slim CSRF
//$container['csrf'] = function (\Psr\Container\ContainerInterface $c) {
$container['csrf'] = function () {
    $guard = new \Slim\Csrf\Guard();
    $guard->setFailureCallable(function (\Slim\Http\Request $request, \Slim\Http\Response $response, callable $next) {
        $request = $request->withAttribute('csrf_result', false);

        return $next($request, $response);
    });

    return $guard;
};

// view renderer
$container['renderer'] = function (\Psr\Container\ContainerInterface $c) {

    $settings = $c->get('settings')['renderer'];

    $view = new Slim\Views\Twig($settings['template_path'], [
        'cache' => $settings['cache'],
        'debug' => (APP_ENV !== 'production'),
    ]);

    //20180122 debug 옵션이 true면 extension 추가 by 최민
    if ($view->getEnvironment()) {
        $view->addExtension(new \Twig_Extension_Debug());
    }
    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $c->get('request')->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($c->get('router'), $basePath));

    return $view;
};

// monolog
$container['logger'] = function (\Psr\Container\ContainerInterface $c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($c->get('settings')->get('name'));
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));

    return $logger;
};

// exception handler
//$container['errorHandler'] = function ($container) {
//    return function ($request, $response, \Exception $exception) use ($container) {
//        $logs  = "[exception::" . $exception->getFile() . "(line:" . $exception->getLine() . ")]";
//        $logs .= $exception->getMessage();
//        $container['logger']->error($logs);
//
//        $response->getBody()->rewind();
//
//        $container['renderer']->render($response, 'header.twig', [
//            'page_title' => '안내 메시지',
//            'tracking_id' => \libraries\util\CommonUtil::getTrackingID()
//        ]);
//        $container['renderer']->render($response, 'message.twig', [
//            'message' => '일시적인 오류가 발생하였습니다. 잠시 후 다시 시도하여 주시기 바랍니다.',
//            'popup_close' => true
//        ]);
//        $container['renderer']->render($response, 'footer.twig');
//        return $response;
//    };
//};
//
//// php error handler
//$container['phpErrorHandler'] = function ($container) {
//    return function ($request, $response, \Error $error) use ($container) {
//        $logs  = "[exception::" . $error->getFile() . "(line:" . $error->getLine() . ")]";
//        $logs .= $error->getMessage();
//        $container['logger']->error($logs);
//
//        $response->getBody()->rewind();
//
//        $container['renderer']->render($response, 'header.twig', [
//            'page_title' => '안내 메시지',
//            'tracking_id' => \libraries\util\CommonUtil::getTrackingID()
//        ]);
//        $container['renderer']->render($response, 'message.twig', [
//            'message' => '일시적인 오류가 발생하였습니다. 잠시 후 다시 시도하여 주시기 바랍니다.',
//            'popup_close' => true
//        ]);
//        $container['renderer']->render($response, 'footer.twig');
//        return $response;
//    };
//};

// 404 not found error handler
$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {


        $uri = $request->getUri()->getPath();
        $container['logger']->error('[404] uri:: ' . $uri);

        $response->getBody()->rewind();

        $container['renderer']->render($response, 'header.twig', [
            'page_title'  => '페이지를 찾을 수 없습니다.',
            'tracking_id' => \libraries\util\CommonUtil::getTrackingID()
        ]);
        $container['renderer']->render($response, 'message.twig', [
            'message' => '페이지를 찾을 수 없습니다.'
        ]);
        $container['renderer']->render($response, 'footer.twig');

        return $response;
    };
};

// multi language
// @todo Need Change See https://helgesverre.com/blog/i18n-slim-framework-translation-twig/
$container['languagePack'] = function (\Psr\Container\ContainerInterface $c) {
    //This parameter must be is instance of TWIG Environment! /!\ (no require)
    $twigEnvironment = $c->get('renderer');
    $settings = $c->get('settings')->get('language');
    $availableLang = $settings['availableLang'];
    $defaultLang = $settings['defaultLang'];

    return new \libraries\lang\Multilanguage([
        'availableLang' => $availableLang,
        'defaultLang'   => $defaultLang,
        'twig'          => $twigEnvironment->getEnvironment(),
        'container'     => $c,
        'langFolder'    => '../application/lang/'
    ]);
};

// flash message
$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};
