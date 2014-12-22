<?php

function parse_chmod($chmod, $tresult = true, $fresult = false) {
    $bits = str_split($chmod, 3);
    $sets = str_split("ugo");
    $perms = str_split("rwd");
    
    $result = array();
    for ($s = 0; $s < 3; $s++) {
        $bits[$s] = str_split($bits[$s]);
        for ($p = 0; $p < 3; $p++)
            $result[$sets[$s] . $perms[$p]] = ($bits[$s][$p] == $perms[$p] || $bits[$s][$p] == '+')
                ? $tresult : $fresult;
        $result[$sets[$s]] = array($perms[0] => $result[$sets[$s] . $perms[0]],
            $perms[1] => $result[$sets[$s] . $perms[1]], $perms[2] => $result[$sets[$s] . $perms[2]]);
    }

    return $result;
}

#TODO(sandy): percolate this up and replace it with real_check_chmod()
function check_chmod($cid, $perms) {
    global $_core;

    $f = db("SELECT `chmod`, `owner`, `group` FROM `%%%data` WHERE `cid`='%d'", DB_ROW, $cid);
    return real_check_chmod($perms, $f["chmod"], $f["owner"], $f["group"]);
}

function real_check_chmod($perms, $chmod, $owner, $group, $aswho = null, $aswhogroups = null) {
    global $_core;

    $chmod = parse_chmod($chmod);

    if ($aswho == null)
        $aswho = $_core->services["auth"]->uid;
    if ($aswhogroups == null)
        $aswhogroups = $_core->services["auth"]->groups;

    $s = "o";
    if ($owner == $aswho)
        $s = "u";
    else if (in_array($group, $aswhogroups))
        $s = "g";

    $result = true;

    foreach (str_split($perms) as $perm)
        if (!$chmod[$s][$perm]) $result = false;

    extendable("chmod", "elevate", $result);
    return $result;
}

function build_chmod($array) {
    $sets = str_split("ugo");
    $perms = str_split("rwd");

    $result = "";
    foreach ($sets as $s)
        foreach ($perms as $p)
            $result .= (@isset($array[$s.$p]) && $array[$s.$p] == $p) ? $p : "-";
    return $result;
}

?>
