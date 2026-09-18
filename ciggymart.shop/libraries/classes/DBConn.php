<?php
require_once("Conn.php");
require_once("db_migration.php");

class DBConn extends Conn {
    public $link = null;
    public $use_mysqli = false;
    public $query;
    private static $migrated = false;

    function __construct(){
        $this->ConnToServer();
        $this->ConnToDb();
        
        if (!self::$migrated) {
            self::$migrated = true;
            @DBMigration::runMigration($this);
        }
    }

    function ConnToServer() {
        if (function_exists('mysqli_connect')) {
            $this->use_mysqli = true;
            $this->link = @mysqli_connect($this->server, $this->user, $this->pswd, $this->database);
            if (!$this->link) {
                // Try without DB first then select DB
                $this->link = @mysqli_connect($this->server, $this->user, $this->pswd);
            }
        }
        
        if (!$this->link && function_exists('mysql_connect')) {
            $this->use_mysqli = false;
            @mysql_connect($this->server, $this->user, $this->pswd);
        }
    }

    function ConnToDb() {
        if ($this->use_mysqli && $this->link) {
            @mysqli_select_db($this->link, $this->database);
            @mysqli_set_charset($this->link, 'utf8');
        } else if (function_exists('mysql_select_db')) {
            @mysql_select_db($this->database);
        }
    }

    function escape($str) {
        if ($this->use_mysqli && $this->link) {
            return mysqli_real_escape_string($this->link, $str);
        } else if (function_exists('mysql_real_escape_string')) {
            return mysql_real_escape_string($str);
        }
        return addslashes($str);
    }
	
    function query($sql) {
        if ($this->use_mysqli && $this->link) {
            $this->query = @mysqli_query($this->link, $sql);
            return $this->query ? true : false;
        } else {
            if (!$this->query = @mysql_query($sql)) {
                return false;
            } else {
                return true;
            }
        }
    }
	
    function fetch() {
        if ($this->use_mysqli) {
            if ($this->query && ($row = @mysqli_fetch_row($this->query))) {
                return $row;
            }
            return false;
        } else {
            if (@mysql_fetch_row($this->query)) {
                return $this->query;
            } else {
                return false;
            }
        }
    }

    function getLastInsertId() {
        if ($this->use_mysqli && $this->link) {
            return mysqli_insert_id($this->link);
        } else if (function_exists('mysql_insert_id')) {
            return mysql_insert_id();
        }
        return 0;
    }

    function valInsert($tblname1, $tblfield1, $tblvalues1) {
        $val_part = "";
        $sql = "INSERT INTO `$tblname1` ("; 
        $i = 0; 
        while ($i < count($tblfield1)) { 
            $sql = $sql . "`" . $tblfield1[$i] . "`"; 
            $val = isset($tblvalues1[$i]) ? $this->escape($tblvalues1[$i]) : '';
            $val_part = $val_part . "'" . $val . "'"; 
            if (($i + 1) != count($tblfield1)) { 
                $val_part = $val_part . ", "; 
                $sql = $sql . ", "; 
            } 
            $i++; 
        } 
        $sql = $sql . ") VALUES (" . $val_part . ")"; 
        
        return $this->query($sql);
    }

    function updateValue($tblname1, $tblfield1, $tblvalues1, $condition) {
        $sql = "UPDATE `$tblname1` SET "; 
        $i = 0; 
        while ($i < count($tblfield1)) { 
            $val = isset($tblvalues1[$i]) ? $this->escape($tblvalues1[$i]) : '';
            $sql = $sql . "`" . $tblfield1[$i] . "`= '" . $val . "'"; 
            if (($i + 1) != count($tblfield1)) { 
                $sql = $sql . ", "; 
            } 
            $i++; 
        } 
        $sql = $sql . " WHERE " . $condition; 
        
        return $this->query($sql);
    }
	
    function AppendValue($tblname1, $tblfield1, $tblvalues1, $operation, $condition) {
        $sql = "UPDATE `$tblname1` SET "; 
        $sql = $sql . $tblfield1 . "=" . $tblfield1 . $operation . $tblvalues1;
        $sql = $sql . " WHERE " . $condition; 
        $this->query($sql);
    }

    function deleteRecords($tblname, $condition) {
        return $this->query("DELETE FROM $tblname WHERE $condition");
    }
		
    function getUserId($tblname, $fieldname) {
        $res = $this->ExecuteQuery("SELECT MAX($fieldname) AS maxid FROM $tblname");
        return (!empty($res) && isset($res[1]['maxid'])) ? ($res[1]['maxid'] + 1) : 1;
    }
	
    function fatch($table, $condition) {
        return $this->ExecuteQuery("SELECT * FROM $table WHERE $condition");
    }
	
    function checkLogin($table, $useridfield, $userID, $passfield, $password) {
        $safeUser = $this->escape($userID);
        $safePass = $this->escape($password);
        $res = $this->ExecuteQuery("SELECT * FROM $table WHERE $useridfield='$safeUser' AND $passfield='$safePass'");
        return $res;
    }
	
    function fetchAll($table) {
        return $this->ExecuteQuery("SELECT * FROM $table");
    }

    function InsertQuery($userQuery) {
        return $this->query($userQuery);
    }

    function ExecuteQuery($userQuery) {
        $obj = array();
        
        if ($this->use_mysqli && $this->link) {
            $result = @mysqli_query($this->link, $userQuery);
            if (!$result || is_bool($result)) {
                return $obj;
            }
            $x = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                $obj[$x] = $row;
                $x++;
            }
            @mysqli_free_result($result);
            return $obj;
        } else {
            $result = @mysql_query($userQuery);
            if (!$result || is_bool($result)) {
                return $obj;
            }
            $num_fields = @mysql_num_fields($result); 
            $x = 1;
            while ($row = @mysql_fetch_array($result)) {  
                for ($j = 0; $j < $num_fields; $j++) {
                    $name = @mysql_field_name($result, $j);
                    $obj[$x][$name] = $row[$name];
                }
                $x++;
            }
            return $obj;
        }
    }
 	
    function valid_email($str) {
        return filter_var($str, FILTER_VALIDATE_EMAIL) ? true : false;
    }
	
    function normalise($string) {
        return str_replace("'", "", $string);	
    }
		
    function couponnumber($tbl, $field, $prefix, $type = 'capalnum', $num = 8) {		
        while (1) {
            $tid = $this->random_string($type, $num);
            $tid = $prefix . $tid;	 
            $res = $this->ExecuteQuery("SELECT $field FROM $tbl WHERE $field = '" . $this->escape($tid) . "'");     
            if (count($res) == 0) {
                break;
            }
        }
        return $tid;
    }
		
    function random_string($type = 'alnum', $len = 8) {					
        switch ($type) {
            case 'alnum':
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'capalnum':
                $pool = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
                break;
            case 'numeric':
                $pool = '0123456789';
                break;
            case 'nozero':
                $pool = '123456789';
                break;
            case 'unique':
                return md5(uniqid(mt_rand(), true));
            default:
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        $str = '';
        for ($i = 0; $i < $len; $i++) {
            $str .= substr($pool, mt_rand(0, strlen($pool) - 1), 1);
        }
        return $str;
    }

    function isEmailIdExist($emailId) {
        $result = $this->ExecuteQuery("SELECT * FROM member WHERE email = '" . $this->escape($emailId) . "'");      
        return (count($result) > 0);
    }

    function checkexistence($uid, $tbl, $fld) {	
        $sql = "SELECT $fld FROM $tbl WHERE $fld='" . $this->escape($uid) . "'";
        $obj2 = $this->ExecuteQuery($sql);	
        return (count($obj2) == 0);
    }	

    function checkcategory($uid, $tbl, $fld1, $fld2, $val2) {	
        $sql = "SELECT $fld1 FROM $tbl WHERE $fld1='" . $this->escape($uid) . "' AND $fld2='" . $this->escape($val2) . "'";
        $obj2 = $this->ExecuteQuery($sql);	
        return (count($obj2) == 0);
    }	

    function checksameid($uid, $tbl, $fld1, $fld2, $val2) {	
        $sql = "SELECT $fld1 FROM $tbl WHERE $fld1='" . $this->escape($uid) . "' AND $fld2!='" . $this->escape($val2) . "'";
        $obj2 = $this->ExecuteQuery($sql);	
        return (count($obj2) == 0);
    }

    function Orderby($tblname, $columnname, $sorttype) {
        $sql = 'SELECT * FROM ' . $tblname . ' ORDER BY BINARY ' . $columnname . ' ' . $sorttype;
        return $this->ExecuteQuery($sql);
    }	

    function getAcademicSession() {
        return $this->ExecuteQuery("SELECT session FROM academic_session WHERE status=1");
    }

    function getSession() {
        return $this->ExecuteQuery("SELECT SESSION AS 'session' FROM academic_session WHERE STATUS=1");
    }
}
?>