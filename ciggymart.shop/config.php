<?php
session_start();
date_default_timezone_set('Asia/Calcutta');
define('SERVER','localhost');
define('DBUSER','root');
define('DBPASSWORD','');
define('DBNAME','ciggymarts');
/*
 * Work out the public base URL from the request instead of hard-coding it, so
 * the same config.php runs locally and on the live domain with no edit.
 * Falls back to the localhost values when there is no web request (CLI/cron).
 */
$_scheme = 'http';
if ((!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) {
    $_scheme = 'https';
}
$_host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/[^A-Za-z0-9.\-:_]/', '', $_SERVER['HTTP_HOST']) : '';

// Path of this folder relative to the document root ('' when it IS the root).
// chr(92) is a backslash - Windows paths are normalised to forward slashes.
$_basePath = '';
if (!empty($_SERVER['DOCUMENT_ROOT'])) {
    $_realDocRoot = realpath($_SERVER['DOCUMENT_ROOT']);
    if ($_realDocRoot !== false) {
        $_docRoot = rtrim(str_replace(chr(92), '/', $_realDocRoot), '/');
        $_here    = rtrim(str_replace(chr(92), '/', dirname(__FILE__)), '/');
        if ($_docRoot !== '' && strpos($_here, $_docRoot) === 0) {
            $_basePath = substr($_here, strlen($_docRoot));
        }
    }
}

if ($_host !== '') {
    define('SITE_URL', $_scheme . '://' . $_host . $_basePath);
    define('DOMAIN', preg_replace('/:\d+$/', '', $_host));
} else {
    define('SITE_URL', 'http://localhost/ciggymart.shop');
    define('DOMAIN', 'localhost');
}

/*
 * Never print PHP notices/warnings into the response on a live site: several
 * endpoints return bare strings or JSON that JavaScript parses, and stray
 * warning HTML breaks them. Errors still go to the server error log.
 */
$_isLocal = in_array(DOMAIN, array('localhost', '127.0.0.1', '::1'), true)
            || substr(DOMAIN, -6) === '.local'
            || substr(DOMAIN, -5) === '.test';
if (!$_isLocal) {
    @ini_set('display_errors', '0');
    @ini_set('log_errors', '1');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT);
}
define('ROOT', dirname(__FILE__)); // FOR PHP ROOT LINK
define('PATH_LIBRARIES', ROOT . '/libraries');
define('PATH_JS_LIBRARIES', SITE_URL.'/js');
define('PATH_CSS_LIBRARIES', SITE_URL.'/css');
define('PATH_DATA_IMAGE', SITE_URL.'/data_images');
define('PATH_IMAGE', SITE_URL.'/images');

//masters path setup
define('PATH_MASTERS', ROOT.'/adminpanel');
define('PATH_ADMIN_LINK', SITE_URL.'/adminpanel'); // for html link only
define('PATH_ADMIN_INCLUDE',ROOT.'/adminpanel/include');
define('MASTERS_LINK_CONTROL', SITE_URL.'/adminpanel/masters');

//branch path setup
define('BRANCH_PATH_MASTERS', ROOT.'/branchpanel');
define('BRANCH_PATH_ADMIN_LINK', SITE_URL.'/branchpanel'); // for html link only
define('BRANCH_PATH_ADMIN_INCLUDE',ROOT.'/branchpanel/include');
define('BRANCH_MASTERS_LINK_CONTROL', SITE_URL.'/branchpanel/masters');
define('PATH_PDF_LINK', SITE_URL.'/pdfmail');
define("PATH_UPLOAD_IMAGE", SITE_URL.'/image_upload');
define('PATH_PDF',ROOT.'/pdfmail');

// for pagination
define('ROWS_PER_PAGE',20);
define('PAGELINK_PER_PAGE',10);

// Restore APIs removed in PHP 7/8 (mysql_*, magic quotes). Inert on PHP 5.x.
require_once(PATH_LIBRARIES . '/classes/php_legacy_compat.php');

/*__________________________________*/
?>