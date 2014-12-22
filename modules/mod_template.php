<?php

function Info_Template() {
    return array(   "depends" => array("ext", "settings"),
                    "provides" => array("tpl"),
                    "name" => "Template",
                    "version" => "1.0.0",
                    "author" => "Sandy Maguire",
                    "category" => CAT_WIDGET,
                    "about" => "Displays templates, adding a shorthand for php and allowing for template and runtime includes."
                );
}

function Load_Template() {
class Template extends Module {
    private $assigned = array();
    private $sockets = array();
    private $sbox;

    public function display($tpl) {
        // this is how you are supposed to match emails
        $preg_email =   "/((\\\"[^\\\"\\f\\n\\r\\t\\b]+\\\")|([A-Za-z0-9_][A-Za-z0-9_\\!\\#\\$\\%\\&\\'\\*\\+\\-\\~\\/\\=\\?" .
                        "\\^\\`\\|\\{\\}]*(\\.[A-Za-z0-9_\\!\\#\\$\\%\\&\\'\\*\\+\\-\\~\\/\\=\\?\\^\\`\\|\\{\\}]*)*))@((\\[(" .
                        "((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\\.((2" .
                        "5[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\\])|(((" .
                        "25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\\.((25[" .
                        "0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-" .
                        "z0-9])(([A-Za-z0-9\\-])*([A-Za-z0-9]))?(\\.(?=[A-Za-z0-9\\-]))?)+[A-Za-z]+))/D";

        extendable("tpl", "file", $tpl);
        $temp = explode("\n", file_get_contents(top() . "/templates/$tpl"));
        $temp = str_replace("&", "&amp;", $temp);
        $temp = preg_replace($preg_email, "\\1&#64;\\5", $temp);
        extendable("tpl", "source", $temp);

        $newone = array();
        for ($i = 0; $i < setting("tpl", "recursionDepth"); $i++) {
            extendable("tpl", "recursion", $temp);
            
            // performs includes and sockets
            for ($l = 0; $l < count($temp); $l++)
                if (preg_match("/^\s*@\s*(include|socket) ([^\n]+)$/", $temp[$l], $matches)) {
                    if ($matches[1] == "socket" && !isset($this->sockets[trim($matches[2])]))
                        continue;
                    
                    if ($matches[1] == "socket") {
                        if (!isset($this->sockets[trim($matches[2])]))
                            continue;
                        foreach ($this->sockets[trim($matches[2])] as $socket) {
                            $path = top() . "/templates/{$socket}";
                            $file = explode("\n", file_get_contents($path));
                            $file = str_replace("&", "&amp;", $file);
                            array_unshift($file, "<!--include-->\n");
                            $newone = array_merge($newone, $file);
                        }
                    } else {
                        $path = top() . "/templates/" . trim($matches[2]);
                        $file = explode("\n", file_get_contents($path));
                        $file = str_replace("&", "&amp;", $file);
    #FIXME: this email thingy breaks when including sockets? same as above too!
    #       or inline functions or something
    //                    $file = preg_replace($preg_email, "\\1&#64;\\5", $file);
                        $newone = array_merge($newone, $file);
                    }
                } else
                    $newone[] = $temp[$l];
            $temp = explode("\n", implode("\n", $newone));
            $newone = array();
        }
        
        // turns "@value@" into a php-evaluated value
        $temp = preg_replace("/@([^@]*)@/", "<?php echo \\1; ?>", $temp);
        // turns "@line" into a line of php
        $temp = preg_replace("|^\s*@([^\n]*)|", "<?php \\1 ?>", $temp);
        $temp = str_replace("&#64;", "@", $temp);
        $temp = str_replace("&amp;", "&", $temp);
        file_put_contents(top() . "/templates/{$tpl}_eval.php", implode("\n", $temp));
        $this->sbox = $tpl;
        $this->sandbox();
        unlink(top() . "/templates/{$tpl}_eval.php");
    }

    private function sandbox() {
        extract($this->assigned,  EXTR_SKIP);
        extendable("tpl", "before_out");
        require top() . "/templates/{$this->sbox}_eval.php";
        extendable("tpl", "after_out");
    }

    public function assign($name, $val) {
        $this->assigned[$name] = $val;
    }

    public function socket($name, $value) {
        $this->sockets[$name][] = $value;
    }
}
}

?>
