<?php
/**
 * Includes the composer autoloader
 */
include __DIR__ . '/../vendor/autoload.php';

/**
 * Define the base directory of the whole project
 */
define('BASE_DIR', realpath(__DIR__ . '/../'));

/**
 * Define the base directory of the current application
 */
define('APP_DIR', BASE_DIR . '/application/');


/**
 * Define Application environment
 *
 * For development and production
 */
define('APP_ENV_PRODUCTION',                'production');
define('APP_ENV_STAGING',                   'staging');
define('APP_ENV_DEVELOPMENT',               'development');
define('APP_ENV_DEVELOPMENT_LOCAL',         'development_local');
define('APP_ENV_DEVELOPMENT_LOCAL_KIMILDO', 'development_local_kimildo');

/**
 * For service environment to development and production
 */
define('APP_ENV_SERVICE_PRODUCTION', 'production');
define('APP_ENV_SERVICE_DEVELOPMENT', '');


/**
 * Define the current environment
 */
if (!defined('APP_ENV')) {
    define('APP_ENV', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : APP_ENV_DEVELOPMENT_LOCAL);
}

/**
 * Define the service environment of current
 */
if (!defined('APP_ENV_SERVICE')) {
    if (in_array(APP_ENV, [APP_ENV_PRODUCTION, APP_ENV_STAGING])) {
        define('APP_ENV_SERVICE', APP_ENV_SERVICE_PRODUCTION);
    } else {
        define('APP_ENV_SERVICE', APP_ENV_SERVICE_DEVELOPMENT);
    }
}

/**
 * Set the default timezone
 */
date_default_timezone_set('Asia/Seoul');


/**
 * An example of a project-specific implementation.
 *
 * After registering this autoload function with SPL, the following line
 * would cause the function to attempt to load the \Foo\Bar\Baz\Qux class
 * from /path/to/project/src/Baz/Qux.php:
 *
 *      new \Foo\Bar\Baz\Qux;
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
spl_autoload_register(function ($class) {

    // project-specific namespace prefix
    $prefix = '';

    // base directory for the namespace prefix
    $baseDir = APP_DIR;
    
    // does the class use the namespace prefix?
    $len = strlen($prefix);
//    if (strncmp($prefix, $class, $len) !== 0) {
//        // no, move to the next registered autoloader
//        return;
//    }

    // get the relative class name
    $relativeClass = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

// notices handler
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting, so ignore it
        return;
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
});
