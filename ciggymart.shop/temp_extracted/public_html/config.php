<?php
session_start();
date_default_timezone_set('Asia/Calcutta');
define('SERVER','localhost');
define('DBUSER','ciggymarts');
define('DBPASSWORD','ciggymart123@');
define('DBNAME','ciggymarts');
define('SITE_URL', 'https://ciggymart.shop');
define('DOMAIN', 'ciggymart.shop');
define('ROOT', $_SERVER['DOCUMENT_ROOT'] . ''); // FOR PHP ROOT LINK
define('PATH_LIBRARIES', ROOT . '/libraries');
define('PATH_JS_LIBRARIES','/js');
define('PATH_CSS_LIBRARIES','/css');
define('PATH_DATA_IMAGE','/data_images');
define('PATH_IMAGE','/images');

//masters path setup
define('PATH_MASTERS', ROOT.'/adminpanel');
define('PATH_ADMIN_LINK', '/adminpanel'); // for html link only
define('PATH_ADMIN_INCLUDE',ROOT.'/adminpanel/include');
define('MASTERS_LINK_CONTROL','/adminpanel/masters');

//branch path setup
define('BRANCH_PATH_MASTERS', ROOT.'/branchpanel');
define('BRANCH_PATH_ADMIN_LINK', '/branchpanel'); // for html link only
define('BRANCH_PATH_ADMIN_INCLUDE',ROOT.'/branchpanel/include');
define('BRANCH_MASTERS_LINK_CONTROL','/branchpanel/masters');
define('PATH_PDF_LINK','/pdfmail');
define("PATH_UPLOAD_IMAGE",'/image_upload');
define('PATH_PDF',ROOT.'/pdfmail');

// for pagination
define('ROWS_PER_PAGE',20);
define('PAGELINK_PER_PAGE',10);

/*__________________________________*/
?>