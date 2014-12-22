<?php

function Info_Extend() {
    return array(   "depends" => array(),
                    "provides" => array("ext"),
                    "name" => "Extend",
                    "version" => "1.0.0",
                    "author" => "Sandy Maguire",
                    "category" => CAT_CORE,
                    "about" => "Allows modules to add custom code to predetermined extension points in other modules."
                );
}

function Load_Extend() {
class Extend extends Module {
    private $extensions = array();

    public function call($module, $name, &$p1, &$p2, &$p3, &$p4, &$p5, &$p6, &$p7, &$p8, &$p9, &$p10) {
        if (array_key_exists($module, $this->extensions) && array_key_exists($name, $this->extensions[$module])) {
            ksort($this->extensions[$module][$name]);
            foreach ($this->extensions[$module][$name] as $callback) {
                $result = @$callback($p1, $p2, $p3, $p4, $p5, $p6, $p7, $p8, $p9, $p10);
                if ($result == EXT_CLAIM) break;
            }
        }
    }

    public function overload($module, $name, $priority, $callback) {
        if (!array_key_exists($module, $this->extensions))
            $this->extensions[$module] = array();
        if (!array_key_exists($name, $this->extensions[$module]))
            $this->extensions[$module][$name] = array();
        if (!isset($this->extensions[$module][$name][$priority]))
            $this->extensions[$module][$name][$priority] = $callback;
        else
            $this->overload($module, $name, (int)($priority + 1), $callback);
    }
}
}

?>
