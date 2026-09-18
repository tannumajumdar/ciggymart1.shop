<?php
/*
 * Compatibility shim: the removed ext/mysql (mysql_*) API on top of mysqli.
 *
 * This codebase targets PHP 5.6 (see .htaccess), but is run here on PHP 8.x
 * where the mysql_* functions no longer exist. Every definition below is
 * guarded by function_exists(), so on a real PHP 5.6 host this file is inert.
 */

if (!function_exists('mysql_connect')) {

    // Holds the most recent connection, mirroring ext/mysql's implicit link.
    $GLOBALS['__mysql_compat_link'] = null;

    /**
     * Returns the link to operate on: the one passed in, else the last one
     * opened, else a new one built from the config.php constants.
     */
    function __mysql_compat_link($link = null) {
        if ($link instanceof mysqli) {
            return $link;
        }
        if ($GLOBALS['__mysql_compat_link'] instanceof mysqli) {
            return $GLOBALS['__mysql_compat_link'];
        }
        if (defined('SERVER') && defined('DBUSER')) {
            $new = @mysqli_connect(SERVER, DBUSER, DBPASSWORD, defined('DBNAME') ? DBNAME : null);
            if ($new) {
                $GLOBALS['__mysql_compat_link'] = $new;
                @mysqli_set_charset($new, 'utf8');
                return $new;
            }
        }
        return null;
    }

    function mysql_connect($server = null, $username = null, $password = null, $new_link = false, $flags = 0) {
        $link = @mysqli_connect(
            $server !== null ? $server : (defined('SERVER') ? SERVER : 'localhost'),
            $username !== null ? $username : (defined('DBUSER') ? DBUSER : 'root'),
            $password !== null ? $password : (defined('DBPASSWORD') ? DBPASSWORD : '')
        );
        if (!$link) {
            return false;
        }
        $GLOBALS['__mysql_compat_link'] = $link;
        @mysqli_set_charset($link, 'utf8');
        // ext/mysql callers commonly assume the configured DB is already active.
        if (defined('DBNAME')) {
            @mysqli_select_db($link, DBNAME);
        }
        return $link;
    }

    function mysql_pconnect($server = null, $username = null, $password = null) {
        return mysql_connect($server, $username, $password);
    }

    function mysql_select_db($database, $link = null) {
        $link = __mysql_compat_link($link);
        return $link ? @mysqli_select_db($link, $database) : false;
    }

    /** Note the argument order: ext/mysql takes (query, link), mysqli takes (link, query). */
    function mysql_query($query, $link = null) {
        $link = __mysql_compat_link($link);
        return $link ? @mysqli_query($link, $query) : false;
    }

    function mysql_unbuffered_query($query, $link = null) {
        return mysql_query($query, $link);
    }

    function mysql_fetch_array($result, $result_type = MYSQLI_BOTH) {
        return ($result instanceof mysqli_result) ? mysqli_fetch_array($result, $result_type) : false;
    }

    function mysql_fetch_assoc($result) {
        return ($result instanceof mysqli_result) ? mysqli_fetch_assoc($result) : false;
    }

    function mysql_fetch_row($result) {
        return ($result instanceof mysqli_result) ? mysqli_fetch_row($result) : false;
    }

    function mysql_fetch_object($result) {
        return ($result instanceof mysqli_result) ? mysqli_fetch_object($result) : false;
    }

    function mysql_num_rows($result) {
        return ($result instanceof mysqli_result) ? mysqli_num_rows($result) : 0;
    }

    function mysql_num_fields($result) {
        return ($result instanceof mysqli_result) ? mysqli_num_fields($result) : 0;
    }

    function mysql_field_name($result, $field_offset) {
        if (!($result instanceof mysqli_result)) {
            return false;
        }
        $field = @mysqli_fetch_field_direct($result, $field_offset);
        return $field ? $field->name : false;
    }

    function mysql_result($result, $row, $field = 0) {
        if (!($result instanceof mysqli_result)) {
            return false;
        }
        if (!@mysqli_data_seek($result, $row)) {
            return false;
        }
        $data = mysqli_fetch_array($result, MYSQLI_BOTH);
        return isset($data[$field]) ? $data[$field] : false;
    }

    function mysql_data_seek($result, $row) {
        return ($result instanceof mysqli_result) ? @mysqli_data_seek($result, $row) : false;
    }

    function mysql_free_result($result) {
        if ($result instanceof mysqli_result) {
            @mysqli_free_result($result);
        }
        return true;
    }

    function mysql_real_escape_string($string, $link = null) {
        $link = __mysql_compat_link($link);
        return $link ? mysqli_real_escape_string($link, (string) $string) : addslashes((string) $string);
    }

    function mysql_escape_string($string) {
        return mysql_real_escape_string($string);
    }

    function mysql_insert_id($link = null) {
        $link = __mysql_compat_link($link);
        return $link ? mysqli_insert_id($link) : 0;
    }

    function mysql_affected_rows($link = null) {
        $link = __mysql_compat_link($link);
        return $link ? mysqli_affected_rows($link) : 0;
    }

    function mysql_error($link = null) {
        $link = __mysql_compat_link($link);
        return $link ? mysqli_error($link) : '';
    }

    function mysql_errno($link = null) {
        $link = __mysql_compat_link($link);
        return $link ? mysqli_errno($link) : 0;
    }

    /** Callers sometimes pass a result here by mistake; ignore anything but a link. */
    function mysql_close($link = null) {
        if ($link instanceof mysqli_result) {
            return true;
        }
        if ($link instanceof mysqli) {
            if ($GLOBALS['__mysql_compat_link'] === $link) {
                $GLOBALS['__mysql_compat_link'] = null;
            }
            return @mysqli_close($link);
        }
        if ($link === null && $GLOBALS['__mysql_compat_link'] instanceof mysqli) {
            @mysqli_close($GLOBALS['__mysql_compat_link']);
            $GLOBALS['__mysql_compat_link'] = null;
        }
        return true;
    }

    function mysql_set_charset($charset, $link = null) {
        $link = __mysql_compat_link($link);
        return $link ? @mysqli_set_charset($link, $charset) : false;
    }

    function mysql_ping($link = null) {
        $link = __mysql_compat_link($link);
        return $link ? true : false;
    }
}

/*
 * Magic quotes were disabled in PHP 5.3 and removed in 5.4, so these always
 * reported "off"; the functions themselves were removed in PHP 8. The vendored
 * dompdf and PHPMailer copies still call them, so keep no-op equivalents.
 */
if (!function_exists('get_magic_quotes_runtime')) {
    function get_magic_quotes_runtime() { return false; }
}
if (!function_exists('get_magic_quotes_gpc')) {
    function get_magic_quotes_gpc() { return false; }
}
if (!function_exists('set_magic_quotes_runtime')) {
    function set_magic_quotes_runtime($enable) { return true; }
}
