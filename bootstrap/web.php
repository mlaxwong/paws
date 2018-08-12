<?php 
// Requirement

// Running PHP 7+
if (PHP_VERSION_ID < 70000) exit('Paws CMS requires PHP 7.0 or later');

// Check for this early because Paws CMS uses it before the requirements checker gets a chance to run.
if (!extension_loaded('mbstring') || (extension_loaded('mbstring') && ini_get('mbstring.func_overload') != 0)) 
{
    exit('Paws CMS requires the <a href="http://php.net/manual/en/book.mbstring.php" target="_blank">PHP multibyte string</a> extension in order to run. Please talk to your host/IT department about enabling it on your server.');
}

define('APP_TYPE', 'web');
return require __DIR__ . '/bootstrap.php';