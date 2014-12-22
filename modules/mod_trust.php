<?php

function Info_Trust() {
    return array(   "depends" => array(),
                    "provides" => array("trust"),
                    "name" => "Trust Platform",
                    "version" => "1.0.0",
                    "author" => "Sandy Maguire",
                    "category" => CAT_CORE,
                    "about" => "Proves per-basis the identity of modules invoking trusted services."
                );
}

function Load_Trust() {
final class Trust extends Module {
    private $proofs = array();

    public function get_proof($file, $class = null) {
        $debug = debug_backtrace(false);
        $uniq = md5(print_r($debug, true));
        $debug = $debug[1];

        if ($debug["file"] == $file && $debug["class"] == $class)
            if (strpos(str_replace("//", null, str_replace("\\", "/", $debug["file"])), "/admin/plugins/") !== false)
                return build_proof($uniq, $file, "*");
            else
                return build_proof($uniq, $file, $class);

        trigger_error(str_replace(array("mod_", ".php"), "", basename($file)) . ":$class failed its trust proof check", E_USER_NOTICE);
        return false;
    }

    private function build_proof($uniq, $file, $class) {
        $id = md5(md5(time() % mt_rand()) . $uniq);
        $proofs[$id] = array("module" => str_replace(array("mod_", ".php"), "", basename($file)), "class" => strtolower($class));
        return $id;
    }

    public function check_proof($proof) {
        if ($proof == false || strlen($proof) != 32)
            return false;
        $result = @$this->proofs[$proof]; 
        unset($this->proofs[$proof]);
        return $result;
    }
}
}

?>
