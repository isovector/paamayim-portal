<?php

function Info_Pipe() {
    return array(   "depends" => array(),
                    "provides" => array("msg"),
                    "name" => "Messaging Pipe",
                    "version" => "1.0.0",
                    "author" => "Sandy Maguire",
                    "category" => CAT_CORE,
                    "about" => "Provides the under-level messaging capabilities between modules."
                );
}

function Load_Pipe() {
class Pipe extends Module {
    private $pipes = array();
    private $services = array();

    public function __construct($core) {
        $this->services = &$core->services;
    }

    public function &open_pipe($proof, $service, $name, $callback = null) {
        $proof = check_proof($proof);
        if (!$proof || $this->core->services[$service]->module != $proof["class"]) 
            return false;

        if (@isset($this->pipes["$service $name"])) 
            return $this->pipes["$service $name"]["data"];

        $this->pipes["$service $name"] = array("data" => array(), "callback" => is_callable($callback) ? $callback : null);
        send_pipe($service, $name, "open", array("provider" => $this));

        return $this->pipes["$service $name"]["data"];
    }

    public function close_pipe($proof, $service, $name) {
        $proof = check_proof($proof);
        if (!$proof || $this->core->services[$service]->module != $proof["class"] || !isset($this->pipes["$service $name"])) 
            return false;

        send_pipe($service, $name, "close");
        unset($this->pipes["$service $name"]);

        return true;
    }
    
    public function send_pipe($proof, $service, $name, $message, $payload = array()) {
        $proof = check_proof($proof);
        if (!$proof || !isset($this->pipes["$service $name"]) || !is_array($payload)) 
            return false;
        
        $payload["from"] = $proof;
        $pipe = &$this->pipes["$service $name"];
        $out = MSG_KEEP;

        if ($pipe["callback"] != null)
            $out = call_user_func($pipe["callback"], $message, $payload);
        if ($out == MSG_KEEP)
            $this->pipes["$service $name"]["data"][] = array("message" => $message, "payload" => $payload);

        return true;
    }

    public function call($service, $method, $args) {
        if (array_key_exists($service, $this->services))
            return call_user_func_array(array($this->services[$service], $method), $args);
        return false;
    }
}
}

?>
