<?php

function Info_Stories() {
    return array(   "depends" => array("ext", "tpl", "db", "meta", "settings", "httpe"),
                    "provides" => array(),
                    "name" => "Stories",
                    "version" => "1.0.0",
                    "author" => "Sandy Maguire",
                    "category" => CAT_CUSTOM,
                    "about" => "Externalizes latest-stories, errors and metadata to templates."
                );
}

function Load_Stories() {
    function StoriesCallback(&$sym, &$cid) {
        $parent = db("SELECT `cid` FROM `%%%data` WHERE `sym`='news'", DB_VAL);
        service("tpl", "assign", "news", db("SELECT `name`, `title`, `sym`, `cid`, `data` FROM `%%%data` WHERE `parent`='{$parent}' ORDER BY `views` DESC LIMIT 10", 
            DB_ALL));
        $parent = db("SELECT `cid` FROM `%%%data` WHERE `sym`='equip-tour'", DB_VAL);
        service("tpl", "assign", "equipment", db("SELECT `name`, `title`, `sym`, `cid`, `data` FROM `%%%data` WHERE `parent`='{$parent}' ORDER BY `views` DESC LIMIT 10", 
            DB_ALL));
        service("tpl", "assign", "article", meta($cid, "title"));
        service("tpl", "assign", "subtitle", meta($cid, "subtitle"));
    }

    function StoriesErrorCallback(&$eid) {
        service("tpl", "assign", "title", "{$eid} - " . ($eid == 404 ? "Page Not Found" : "Not Authorized"));
        service("tpl", "assign", "article", "HTTP $eid");
        service("tpl", "assign", "subtitle", $eid == 404 ? "Page Not Found" : "Not Authorized");
    }

    service("ext", "overload", "index", "exposure", 0, "StoriesCallback");
    service("ext", "overload", "httpe", "fill", 0, "StoriesErrorCallback");
}

?>
