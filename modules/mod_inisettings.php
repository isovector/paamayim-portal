<?php

function Info_IniSettings() {
    return array(   "depends" => array("trust"),
                    "provides" => array("settings"),
                    "name" => "ini Settings",
                    "version" => "1.0.0",
                    "author" => "Sandy Maguire",
                    "category" => CAT_CORE,
                    "about" => "Provides pre-database environment settings.",
                );
}

function Load_IniSettings() {
class IniSettings extends Module {
    private $data = array();
    private $original = array();
    private $loaded = array();

    public function __construct() {
        $this->data = $this->parse(file_get_contents(top() . "/settings/settings.ini"));
        $this->original = $this->data;
    }

    public function __destruct() {
        if ($this->original != $this->data)
            file_put_contents(top() . "/settings/settings.ini", $this->writeout());
    }

    public function get_setting($section, $key) {
        if (array_key_exists($section, $this->data) && array_key_exists($key, $this->data[$section]))
            return $this->data[$section][$key];
        trigger_error("Non-existant meta entry $section:$key", E_USER_WARNING);
        return null;
    }

    public function get_section($section) {
        try {
            return $this->data[$section];
        } catch (Exception $e) {
            return array();
        }
    }

    public function set($proof, $section, $key, $value) {
        $proof = check_proof($proof);
        if (!$proof) return false;

        $pred = $proof["class"] == "*";
        $pred = $pred || $proof["module"] == $section;
        $pred = $pred || substr($section, 0, strlen($proof["module"]) + 1) == $proof["module"] . ".";
        $pred = $pred || $proof["class"] == $section;
        $pred = $pred || substr($section, 0, strlen($proof["class"]) + 1) == $proof["class"] . ".";
        if ($pred) {
            if (!array_key_exists($section, $this->data))
                $this->data[$section] = array();
            $this->data[$section][$key] = $value;
            return true;
        }
        return false;
    }

    public function dump() {
        return $this->data;
    }

    private function parse($data) {
        $ret = array();
        $cur = null;
        $data = str_replace("\r", "\n", $data);
        $data = explode("\n", $data);
        foreach ($data as $line) {
		$line = trim($line);
            if (preg_match('|^\[([0-9a-zA-Z ._-]*)\]$|', $line, $matches)) {
                $cur = $matches[1];
                if (!array_key_exists($cur, $ret))
                    $ret[$cur] = array();
            } elseif (preg_match('/^(#|;|\/\/).*/', $line)); // comment - do nothing
            elseif (preg_match('|^([0-9a-zA-Z._-]*)=(.*)|', $line, $matches)) {
                $ret[$cur][$matches[1]] = $matches[2];
            } elseif ($line == null);
            else {echo $line;
                trigger_error("JUNK IN STETINGS.INI", E_USER_NOTICE);
}
        }
        return $ret;
    }

    private function writeout() {
        $out = null;
        foreach ($this->data as $sect => $internal) {
            $out .= "\n[$sect]\n";
            foreach ($internal as $key => $val)
                $out .= "$key=$val\n";
        }
        return trim($out);
    }
}

// from php.net: drvali at hotmail dot com
// doesn't seem to work properly
function array_merge_replace_recursive() {
    $params = func_get_args();
    $return = array_shift($params);
    foreach ($params as $array)
        foreach ($array as $key => $value)
            if (is_numeric ($key) && !in_array($value, $return))
                if (is_array($value))
                    $return[] = array_merge_replace_recursive ($return[$$key], $value);
                else
                    $return[] = $value;
            else
                if (isset ($return[$key]) && is_array($value) && is_array($return[$key]))
                    $return[$key] = array_merge_replace_recursive($return[$$key], $value);
                else
                    $return[$key] = $value;
    return $return;
}
}

?>
