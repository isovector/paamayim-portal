<?php
include "includes/enum.inc.php";
include "includes/extern.inc.php";
include "includes/func.inc.php";

class Core {    public $modules = array();    public $services = array();
    public $cid = null;
    public $sym = null;

    private $path = array();

    public function __construct() {        session_start();

        $this->path = array(realpath(dirname($_SERVER["DOCUMENT_ROOT"] . $_SERVER["SCRIPT_NAME"])));
        if (realpath($this->path[count($this->path) - 1]) != dirname(__FILE__)) {
            while (($me = realpath($this->path[count($this->path) - 1] . "/..")) != dirname(__FILE__))
                $this->path[] = $me;
            $this->path[] = $me;
        }
        $this->services["meta"] = "unloaded";        require_once("modules/module.php");        require_once("modules/factory.php");        $factory = new Factory($this);        $this->modules["factory"] = $factory;        $this->services["factory"] = $factory;
    }

    public function is_provided($service) {        return array_key_exists($service, $this->services) && $this->services[$service] != "unloaded";    }

    public function top() {
        return $this->path[count($this->path) - 1];
    }

    public function bottom() {
        return $this->path[0];
    }

    public function depth() {
        return count($this->path);
    }

    public function get_files($c) {
        if (is_numeric($c))
            return $this->get_files($this->path[$c]);
        else if ($c == "top")
            return $this->get_files($this->top());
        else if ($c == "bottom")
            return $this->get_files($this->bottom());

        $result = array();
        $files = glob($c . "/*");
        foreach ($files as $file)
            $result[basename($file)] = $file;

        return $result;
    }}

$_core = new Core();global $_core;

service("factory", "autoload");
service("tpl", "assign", "nav", db("SELECT `name`, `sym`, `cid` FROM `%%%data` WHERE `parent`='0' ORDER BY `sortorder` ASC", DB_ALL));

?>
