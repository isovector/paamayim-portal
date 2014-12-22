<?php

function service($service, $method) {
    global $_core;
    if (!isset($_core) || !is_object($_core->services["msg"])) {
        trigger_error("Unable to call $service:$method", E_USER_WARNING);
        return;
    }

    $args = func_get_args();
    array_shift($args);
    array_shift($args);
    return $_core->services["msg"]->call($service, $method, $args);
}

// doesn't call service() to easy debug_backtrace()
function get_proof($file, $class) {
    global $_core;
    return $_core->services["trust"]->get_proof($file, $class);
}

function setting($section, $key) {
    return service("settings", "get_setting", $section, $key);
}

function meta($cid, $name) {
    return service("meta", "get_meta", $cid, $name);
}

function top() {
    global $_core;
    if (!isset($_core))
        return realpath(dirname(__FILE__) . "/..");
    return $_core->top();
}

function bottom() {
    global $_core;
    return $_core->bottom();
}

function db($query, $type = DB_VAL) {
    $data = func_get_args();
    array_shift($data);
    if (substr($type, 0, 3) == "DB_")
        array_shift($data);
    else
        $type = null;
    return service("db", "query", $query, $type, $data);
}

function extendable($module, $name, &$p1 = null, &$p2 = null, &$p3 = null, &$p4 = null, &$p5 = null, &$p6 = null, &$p7 = null, &$p8 = null, &$p9 = null, &$p10 = null) {
    global $_core;
    if (@isset($_core->services["ext"]))
        $_core->services["ext"]->call($module, $name, $p1, $p2, $p3, $p4, $p5, $p6, $p7, $p8, $p9, $p10);
}

?>
