<?php

function Info_Su() {
    return array(   "depends" => array("ext", "auth"),
                    "provides" => array(),
                    "name" => "Super Administrator",
                    "version" => "1.0.0",
                    "author" => "Sandy Maguire",
                    "category" => CAT_ADMIN,
                    "about" => "Provides automatic chmod-passing for anyone in the superuser group."
                );
}

function Load_Su() {
    function SuCallback(&$result) {
        global $_core;

        if (in_array("wheel", $_core->services["auth"]->groups))
            $result = true;
        return EXT_CLAIM; // locks the extention point
    }

    // ensures mod_su gets priority of chmod.elevate
    service("ext", "overload", "chmod", "elevate", EXT_FIRST, "SuCallback");
}

?>
