<?php

    if (PHP_SAPI == 'cli-server') {
        // To help the built-in PHP dev server, check if the request was actually for
        // something which should probably be served as a static file
        $url = parse_url($_SERVER['REQUEST_URI']);
        $file = __DIR__ . $url['path'];
        if (is_file($file)) {
            return false;
        }
    }

    /**
     * Include common setup
     */
    include __DIR__ . '/common.php';

    /**
     * Start PHP session
     */
    if ( ! isset($_SESSION)) {
        session_start();
    }

    /**
     * Instantiate Slim Framework
     * Loads the setting for current environment
     */
    $config = include APP_DIR . 'config/' . APP_ENV . '.php';
    $app = new \Slim\App($config);

    /**
     * Set up dependencies
     */
    require APP_DIR . 'dependencies/containers.php';

    /*
     * this middleware will add 'lang' container with lang slug (ex: fr) and create global variable 'lang' in twig
       environment
     */
    $app->add($container['languagePack']);

    // 고객사 url일 경우에는 콘솔에 접근금지
    $host = $_SERVER['HTTP_HOST'];
    preg_match("/([a-z0-9-]*\.)*studio.synctreengine.com/i", $host, $match);
    $domain = (is_array($match) && isset($match[1])) ? $match[1] : 'prod.';

    switch ($domain) {
        case 'local.':
        case 'dev.':
        case 'stg.':
        case 'prod.' :
        case 'poc.' :
            foreach (glob(APP_DIR . '/routes/*.router.php') as $route) { include_once $route; }
            foreach (glob(APP_DIR . '/routes/console/*.router.php') as $route) { include_once $route; }
            foreach (glob(APP_DIR . '/routes/internal/*.router.php') as $route) { include_once $route; }
            break;
    }

    foreach (glob(APP_DIR . '/routes/generated/usr/*/*.router.php') as $route) {
        include_once $route;
    }


    /**
     * Run app
     */
    try {
        $app->run();
    } catch (\Slim\Exception\MethodNotAllowedException $e) {
        \libraries\log\LogMessage::error($e->getMessage());
    } catch (\Slim\Exception\NotFoundException $e) {
        \libraries\log\LogMessage::error($e->getMessage());
    } catch (\Exception $e) {
        \libraries\log\LogMessage::error($e->getMessage());
    }
