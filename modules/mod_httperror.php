<?php

function Info_Httperror() {
    return array(   "depends" => array("msg"),
                    "provides" => array("httpe"),
                    "name" => "HTTP Error",
                    "version" => "1.0.0",
                    "author" => "Sandy Maguire",
                    "category" => CAT_ACCESS,
                    "about" => "Pushes custom error pages to the user."
                );
}

function Load_Httperror() {
class Httperror extends Module {
    public function error($eid) {
        if ($eid == 404) {
            header("HTTP/1.0 404 Not Found"); 
            header("Status: 404 Not Found"); 
        }
        extendable("httpe", "fill", $eid);
        extendable("httpe", "redirect", $eid);
        if ($eid == 403)
            $eid = "admin/" . $eid;
        service("tpl", "display", "{$eid}.tpl");
        die();
    }
}
}

?>
