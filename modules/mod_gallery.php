<?php

function Info_Gallery() {
    return array(   "depends" => array("db", "meta", "msg"),
                    "provides" => array(),
                    "name" => "Gallery",
                    "version" => "1.0.0",
                    "author" => "Sandy Maguire",
                    "category" => CAT_WIDGET,
                    "about" => "Provides appropriate bindings for galleries when loaded via mod:gallery."
                );
}

function Show_Gallery($gallery, $page, $perrow, $perpage) {
    service("tpl", "assign", "pages", ceil(count(db("SELECT `iid` FROM `%%%gallery_data` WHERE `gallery`='%s'", 
        DB_ALL, $gallery)) / $perpage));
    service("tpl", "assign", "gallery", $gallery);
    service("tpl", "assign", "perrow", $perrow);
    service("tpl", "assign", "rows", $perpage / $perrow);
    service("tpl", "assign", "tdwidth", 100 / $perrow);
    service("tpl", "assign", "curpage", $page);
    service("tpl", "assign", "perpage", $perpage);
    if (strpos($_SERVER["REQUEST_URI"], "&") !== false)
        service("tpl", "assign", "here", substr($_SERVER["REQUEST_URI"], 0, strpos($_SERVER["REQUEST_URI"], "&")));
    else
        service("tpl", "assign", "here", $_SERVER["REQUEST_URI"]);
    service("tpl", "assign", "earlier", db("SELECT * FROM `%%%gallery_data` WHERE `gallery`='%s' LIMIT 0, %d", 
        DB_ALL, $gallery, $page * $perpage));
    service("tpl", "assign", "later", db("SELECT * FROM `%%%gallery_data` WHERE `gallery`='%s' LIMIT %d, 9999", 
        DB_ALL, $gallery, $page * $perpage + $perpage));
    service("tpl", "assign", "images", db("SELECT * FROM `%%%gallery_data` WHERE `gallery`='%s' LIMIT %d, %d", 
        DB_ALL, $gallery, $page * $perpage, $perpage));
    service("tpl", "socket", "gallery", "sockets/gallery.tpl");
}

function Load_Gallery() {
    global $_core;

    if (!isset($_GET["page"]) || !is_numeric($_GET["page"]))
        $_GET["page"] = 0;
    Show_Gallery(meta($_core->cid, "gallery"), $_GET["page"], 5, 20);
}

?>
