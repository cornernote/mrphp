<?php
/**
 * Entry script
 */
 
error_reporting(E_ALL);
ini_set('display_errors', 0);
set_time_limit(60 * 10);
date_default_timezone_set('Australia/Sydney');
$errorEmails = array('errors@mailinator.com');

// catch errors
function shutdown()
{
    $error = error_get_last();
    if ($error) {
        $message = 'Error (' . $error['type'] . ') ' . $error['message'] . ' in file ' . $error['file'] . ' on line ' . $error['line'] . '.';
        echo $message;
        foreach ($errorEmails as $errorEmail) 
            mail($errorEmail, 'Error', $message);
        exit;
    }
}

register_shutdown_function('shutdown');

// try run
try {

    // defines
    defined('APP_PATH') or define('APP_PATH', __DIR__);
    defined('DATA_PATH') or define('DATA_PATH', dirname(APP_PATH) . '/data');

    // requires
    require_once(dirname(APP_PATH) . '/vendor/autoload.php');

    // run the app
    Application::createInstance(require('config.php'))->process();

} catch (Exception $e) {
    // catch exceptions
    $message = 'Exception (' . $e->getCode() . ') ' . $e->getMessage() . ' in file ' . $e->getFile() . ' on line ' . $e->getLine() . '.';
    echo $message;
    foreach ($errorEmails as $errorEmail) 
        mail($errorEmail, 'Error', $message);
    exit;
}
