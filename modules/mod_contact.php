<?php

function Info_Contact() {
    return array(   "depends" => array("db", "meta", "msg"),
                    "provides" => array(),
                    "name" => "Contact",
                    "version" => "1.0.0",
                    "author" => "Sandy Maguire",
                    "category" => CAT_WIDGET,
                    "about" => "Provides appropriate bindings for contact forms when loaded via mod:contact."
                );
}

function Load_Contact() {
    global $_core;

    service("tpl", "socket", "contact", "sockets/contact.tpl");
}

?>
