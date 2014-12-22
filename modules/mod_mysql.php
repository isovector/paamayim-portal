<?php

function Info_Mysql() {
    return array(   "depends" => array("settings"),
                    "provides" => array("db", "meta", "users", "groups"),
                    "category" => CAT_ACCESS,
                    "name" => "MySQL",
                    "version" => "1.0.0",
                    "author" => "Sandy Maguire",
                    "about" => "Handles a MySQL connection. Provides a data, user, group and metadata interface to the connection."
                );
}

function Load_Mysql() {
class Mysql extends Module {
    private $connection;
    private $prefix;
    public $lastid;

    public function __construct() {
        $this->prefix = setting("db", "prefix");
        $this->connection = mysql_connect(setting("db", "server"), setting("db", "user"), setting("db", "password"));
        mysql_select_db(setting("db", "dbname"), $this->connection);
    }

    public function __destruct() {\
        mysql_close($this->connection);
    }

    public function query($query, $selecttype = DB_VAL, $parse = array()) {
        if ($selecttype == null)
            $selecttype = DB_VAL;

        $query = str_replace("%%%", $this->prefix, $query);
        foreach ($parse as &$item)
            $item = mysql_real_escape_string($item);
        $query = vsprintf($query, $parse);
        $result = mysql_query($query, $this->connection) or die(mysql_error());
//echo $query . "<br>";
        $temp = explode(" ", $query);
        if (strtoupper($temp[0]) == "SELECT") {
            $output = array();
            while ($row = mysql_fetch_assoc($result))
                $output[] = $row;
            if ($selecttype == DB_ALL) return $output;
            else if ($selecttype == DB_ROW) return $output[0];

            $col = array();
            $keys = @array_keys($output[0]);
            if ($selecttype == DB_COL) {
                foreach ($output as $row)
                    $col[] = @$row[@$keys[0]];
                return $col;
            } else if ($selecttype == DB_VAL)
                return !isset($output[0][@$keys[0]]) ? false : $output[0][@$keys[0]];
            else trigger_error("Invalid db select type", E_USER_WARNING);
        }

        $this->lastid = mysql_insert_id($this->connection);

        return $result;
    }

    public function has_metadata($cid, $name) {
        return mysql_num_rows(mysql_query(sprintf("SELECT `value` FROM `%s%s` WHERE `cid`='%d' AND `name`='%s'", $this->prefix, setting("mysql.meta", "table"), $cid, $name), $this->connection));
    }

    public function get_meta($cid, $name) {
        $val = $this->query("SELECT `value` FROM `%s%s` WHERE `cid`='%d' AND `name`='%s'", DB_VAL, array($this->prefix, setting("mysql.meta", "table"), $cid, $name));
        
        return $val;
    }

    public function user_name($uid) {
        return $this->query("SELECT `name` FROM `%s%s` WHERE `uid`='%d'", DB_VAL, array($this->prefix, setting("mysql.users", "table"), $uid));
    }

    public function get_users() {
        return $this->query("SELECT `name`, `uid` FROM `%s%s` ORDER BY `name` ASC", DB_ALL, array($this->prefix, setting("mysql.users", "table")));        
    }

    public function get_uid($name) {
        return $this->query("SELECT `uid` FROM `%s%s` WHERE `name`='%s'", DB_VAL, array($this->prefix, setting("mysql.users", "table"), $name));
    }


    public function verify($name, $pass) {
        $table = setting("mysql.users", "table");
        return mysql_num_rows(mysql_query("SELECT * FROM `{$this->prefix}$table` WHERE `name`='$name' AND `pass`='$pass'", $this->connection));
    }

    public function get_groups($name) {
        return explode(",", $this->query("SELECT `group` FROM `%s%s` WHERE `name`='%s'", DB_VAL, array($this->prefix, 
            setting("mysql.users", "table"), $name)));
    }

    public function get_group_names() {
        return $this->query("SELECT `name`, `group` FROM `%s%s` ORDER BY `name` ASC", DB_ALL, array($this->prefix, setting("mysql.groups", "table")));        
    }

    public function group_name($gid) {
        return $this->query("SELECT `name` FROM `%s%s` WHERE `group`='%s'", DB_VAL, array($this->prefix, setting("mysql.groups", "table"), $gid));
    }

#FIXME: this should combine with all of the groups or something
    public function allowed($gid) {
        return explode(",", $this->query("SELECT `permissions` FROM `%s%s` WHERE `group`='%s'", DB_VAL, array($this->prefix, setting("mysql.groups", "table"), $gid)));
    }
}
}

?>
