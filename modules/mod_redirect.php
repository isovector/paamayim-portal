<?php

function Info_Redirect() {
    return array(   "depends" => array("meta"),
                    "provides" => array(),
                    "name" => "Redirection",
                    "version" => "1.0.0",
                    "author" => "Sandy Maguire",
                    "category" => CAT_FEATURE,
                    "about" => "Allows instant redirection of nodes to other URLs."
                );
}

function Load_Redirect() {
    global $_core;

    $url = meta($_core->cid, "redirect");
    if ($url != null) {
        header("Location: $url");
        die();
    }
}

?>
