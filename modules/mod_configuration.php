<?php

function Info_Configuration() {
    return array(   "depends" =>    array("trust"),
                    "provides" =>   array("conf"),
                    "name" =>       "Configuration",
                    "version" =>    "1.0.0",
                    "author" =>     "Sandy Maguire",
                    "category" =>   CAT_CORE,
                    "about" =>      "UNSTABLE: DO NOT LOAD - Parses configuration directives and provides them to other modules."
                );
}

function Load_Configuration() {
define("SRV_CONF_FILTER_GROUPS_ONLY", true);
define("SRV_CONF_FILTER_NO_GROUPS", false);

class Configuration extends Module {
    private $raw = array();
    private $rawptr;
    private $parsed = array();
    private $sortname;
    private $sorttype;

    public function __construct($core) {
echo "<pre>";
        for ($i = 0; $i < $core->services["pathinfo"]->depth(); $i++)
            if (array_key_exists("essence.conf", ($files = $core->services["pathinfo"]->get_files($i))))
                $this->parsed[] = $this->parse(file_get_contents($files["essence.conf"]));
        
        print_r($this->parsed);
    }

    private function parse($data) {
        $this->raw = array_map("trim", explode("\n", $data));
        $this->rawptr = 0;

        return $this->do_parse();
    }

    private function do_parse() {
        $results = array();
        $ptr = &$this->rawptr;

        while ($this->rawptr != count($this->raw)) {
            $here = $this->raw[$this->rawptr];
            if (preg_match("/^<([A-Za-z0-9_.-]+)( ([^>]*))?>$/", $here, $matches)) {
                $res = new ConfigGroup($here);
                $ptr++;
                $res->directives = $this->do_parse();
                $results[] = $res;
            } else if (preg_match("/^<\/([A-Za-z0-9_.-]+)>$/", $here, $matches))
                return $results;
            else if (substr($here, 0, 1) == ';' || $here == null);
            else $results[] = new ConfigDirective($here);

            $ptr++;
        }

        return $results;
    }

    public function get_root() {
        return $this->parsed;
    }

    public function filter_groups($name, $directives = null, $sorttype = SRV_CONF_FILTER_GROUPS_ONLY) {
        if ($directives == null) $directives = $this->get_root();

        $this->sortname = $name;
        $this->sorttype = $sorttype;

        return array_filter($directives, array($this, "filter_groups_predicate"));
    }

    private function filter_groups_predicate($dir) {
        return is_a($dir, "ConfigGroup") == $this->sorttype && $dir->name == $this->sortname;
    }
}

final class ConfigGroup {
    public $name;
    public $args = array();
    public $directives = array();

    public function __construct($here) {
        $here = str_replace(array("<", ">"), null, $here);
        $dir = split_directive($here);
        $this->name = $dir["name"];
        $this->args = $dir["args"];
    }
}

final class ConfigDirective {
    public $name;
    public $args = array();

    public function __construct($here) {
        $dir = split_directive($here);
        $this->name = $dir["name"];
        $this->args = $dir["args"];
    }
}


function split_directive($here) {
    $bits = explode(" ", $here);
    return array("name" => array_shift($bits), "args" => $bits);
}
}

?>
