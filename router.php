<?php
/*
 * Router for PHP's built-in web server (php -S ... -t . router.php).
 *
 * php -S will not resolve a directory index under any folder whose name
 * contains a dot: it treats "ciggymart.shop" as a static file request, so
 * http://localhost/ciggymart.shop/ returns 404 while .../index.php works.
 * This router serves the directory index itself; everything else is handed
 * back to the server unchanged.
 *
 * Only needed for local development - Apache/nginx handle this natively.
 */

$uri  = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$path = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $uri);

if (is_dir($path)) {
    // A directory was requested without a trailing slash: redirect so that
    // relative links inside the page resolve against the right base.
    if (substr($uri, -1) !== '/') {
        header('Location: ' . $uri . '/', true, 301);
        return true;
    }

    foreach (array('index.php', 'index.html', 'index.htm') as $index) {
        $candidate = rtrim($path, '\/') . DIRECTORY_SEPARATOR . $index;
        if (!is_file($candidate)) {
            continue;
        }

        if (substr($index, -4) === '.php') {
            $script = rtrim($uri, '/') . '/' . $index;
            $_SERVER['SCRIPT_FILENAME'] = $candidate;
            $_SERVER['SCRIPT_NAME']     = $script;
            $_SERVER['PHP_SELF']        = $script;
            chdir(dirname($candidate));
            require $candidate;
        } else {
            header('Content-Type: text/html');
            readfile($candidate);
        }
        return true;
    }
}

// Not a directory index - let the built-in server serve it.
return false;
