<?php

class Factory extends Module {
    private $available = array("modules" => array(), "services" => array());
    private $preferred = array();
    public $about = array("factory" => array(  
                    "depends" => array(),
                    "provides" => array("factory"),
                    "name" => "Factory",
                    "version" => "1.0.0",
                    "author" => "Sandy Maguire",
                    "category" => CAT_CORE,
                    "about" => "Loads modules into the core system. Absolutely fundamental - do not remove.",
                    "loaded" => true,
                    "method" => "core"
    ));

    public function __construct($core) {
        $this->core = $core;
        $this->module = "factory";

        foreach (glob(top() . "/modules/mod_*.php") as $file) {
            preg_match("/mod_([^.]*)\.php/", $file, $matches);
            include_once $file;
            $this->available["modules"][] = $matches[1];
            $module = $matches[1];
            $data = call_user_func("Info_$module");
            $this->about[$module] = $data;
            $this->about[$module]["loaded"] = false;
            foreach ($data["provides"] as $provided) {
                if (!array_key_exists($provided, $this->available["services"]))
                    $this->available["services"][$provided] = array();
                $this->available["services"][$provided][] = $matches[1];
            }
        }

        if (!array_key_exists("conf", $this->available["services"]))
            trigger_error("Service conf is not provided by any modules", E_USER_ERROR);

        $this->load($this->available["services"]["settings"][0], "factory");
        $this->load_service("msg", "factory");
        //$this->load($this->available["services"]["conf"][0], "factory");
    }

    public function load($module, $method = "") {
        if (array_key_exists($module, $this->core->modules) || $module == "")
            return;
        try {
            extendable("factory", "preload", $module);
            include_once top() . "/modules/mod_$module.php";
            call_user_func("Load_$module");
            $data = call_user_func("Info_$module");
            $this->about[$module]["loaded"] = true;
            $this->about[$module]["method"] = $method;
            extendable("factory", "depends", $data["depends"]);
            foreach ($data["depends"] as $depends)
                $this->load_service($depends, "depended");
            $modref = ucwords($module);
            $instance = false;
            if (class_exists($modref)) {
                $new = new $modref($this->core);
                $new->module = $module;
                $new->core = $this->core;
                foreach ($data["depends"] as $depends)
                    $new->depends[$depends] = $this->core->services[$depends];
                extendable("factory", "built", $new);
                $this->core->modules[$module] = $new;
                $instance = true;
            } else
                $this->core->modules[$module] = "non-object module";
            if ($this->core)
                foreach ($data["provides"] as $service)
                    if ($this->core->is_provided($service))
                        trigger_error("mod_$module provides $service which is already implimented by mod_" . $this->core->services[$service]->module, E_USER_NOTICE);
                    elseif ($instance)
                        $this->core->services[$service] = $new;
            return true;
        } catch (Exception $e) {
            trigger_error("Unable to register service " . $service . " (mod_" . $module .")", E_USER_WARNING);
            return false;
        }
    }

    public function load_service($service, $method = "") {
        extendable("factory", "load_service", $service);
        $type = "preferred";
        if (!$this->core->is_provided($service))
            if ($this->is_available($service)) {
                if (@setting("factory.preferred", $service))
                    $load = setting("factory.preferred", $service);
                else {
                    $load = $this->available["services"][$service][0];
                    $type = "guess";
                    if (count($this->available["services"][$service]) > 1)
                        trigger_error("Service $service is required as a dependency but several modules provide it (and factory has no preference) - using mod_$load", E_USER_NOTICE);
                }
                $this->load($load, $method . " " . $type);
            } else
                trigger_error("Service $service is required but it is unavailable", E_USER_ERROR);
    }

    public function autoload() {
        foreach (explode(",", setting("factory.autoload", "services")) as $service)
            if ($service != null)
                $this->load_service($service, "autoload");
        foreach (explode(",", setting("factory.autoload", "modules")) as $module)
            if ($module != null)
                $this->load($module, "autoload");
    }

    public function is_available($service) {
        return array_key_exists($service, $this->available["services"]);
    }

    public function module_available($module) {
        return array_key_exists($module, $this->about);
    }
}

?>
