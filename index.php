<?php
error_reporting(E_ALL);
include "core.php";

if (!isset($_GET["node"]) && !isset($_GET["sym"])) {
    $cid = 1;
    $sym = "";
} else if (isset($_GET["node"])) {
    if (is_numeric($_GET["node"])) {
        $cid = $_GET["node"];
        $sym = @db("SELECT `sym` FROM `%%%data` WHERE `cid`='%s'", DB_VAL, $cid);
        if (empty($sym))
            service("httpe", "error", 404);
    } else
        service("httpe", "error", 404);
} else if (isset($_GET["sym"])) {
    $sym = $_GET["sym"];
    $cid = @db("SELECT `cid` FROM `%%%data` WHERE `sym`='%s'", DB_VAL, $sym);
    if ($cid == null)
        service("httpe", "error", 404);
}

$_core->cid = $cid;
$_core->sym = $sym;
db("UPDATE `%%%data` SET `views`=`views`+'1' WHERE `sym`='%s'", $sym);

foreach (explode(",", db("SELECT `load` FROM `%%%data` WHERE `sym`='%s'", $sym)) as $load)
    if ($load == "") continue;
    else if (substr($load, 0, 4) == "mod:")
        service("factory", "load", substr($load, 4));
    else if (substr($load, 0, 4) == "srv:")
        service("factory", "load_service", substr($load, 4));

service("tpl", "assign", "cid", $cid);
service("tpl", "assign", "sym", $sym);service("tpl", "assign", "title", db("SELECT `title` FROM `%%%data` WHERE `sym`='%s'", $sym));service("tpl", "assign", "data", db("SELECT `data` FROM `%%%data` WHERE `sym`='%s'", $sym));extendable("index", "exposure", $sym, $cid);
service("tpl", "display", db("SELECT `template` FROM `%%%data` WHERE `sym`='%s'", $sym));

?>
