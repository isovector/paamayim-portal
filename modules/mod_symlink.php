<?php

function Info_Symlink() {
    return array(   "depends" => array("ext", "tpl", "db"),
                    "provides" => array(),
                    "name" => "Symbolic Links",
                    "version" => "1.0.0",
                    "author" => "Sandy Maguire",
                    "category" => CAT_ACCESS,
                    "about" => "Rewrites generic cid-node links ( /?node={cid} ) with symbolic links ( /{symlink} )."
                );
}

function Load_Symlink() {
    function SymlinkCallback() {
        ob_start("SymlinkObCallback");
    }

    function SymlinkObCallback($buffer) {
        $data = db("SELECT `cid`, `sym` FROM `%%%data` ORDER BY `cid` DESC", DB_ALL);
        foreach ($data as $row)
            $buffer = str_replace("\"/?node={$row['cid']}\"", "\"/{$row['sym']}\"", $buffer);
        return $buffer;
    }

    function SymlinkEndCallback() {
        ob_end_flush();
    }

    service("ext", "overload", "tpl", "before_out", EXT_LAST, "SymlinkCallback");
    service("ext", "overload", "tpl", "after_out", EXT_FIRST, "SymlinkEndCallback");
}

?>
