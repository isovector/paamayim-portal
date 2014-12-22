<?php

function Info_Widget() {
    return array(   "depends" => array("ext", "tpl", "db"),
                    "provides" => array(),
                    "name" => "Widget Layer",
                    "version" => "1.0.0",
                    "author" => "Sandy Maguire",
                    "category" => CAT_WIDGET,
                    "about" => "Lays widgets into special widget marks on pages."
                );
}

function Load_Widget() {
    global $AVAILABLE_WIDGETS;
    $AVAILABLE_WIDGETS = array();
    foreach (glob(top() . "/templates/sockets/widgets/*.tpl") as $file) {
            preg_match("/([^.\/]*)\.tpl$/", $file, $matches);
            $AVAILABLE_WIDGETS[strtoupper($matches[1])] = $matches[1];
    }

    function WidgetCallback(&$temp) {
        global $AVAILABLE_WIDGETS;

        foreach ($temp as &$line)
            if (preg_match("/\[WIDGET:([A-Z0-9.-]+)(\/([a-z]+)=([^\]\/]+)(\/([a-z]+)=([^\]\/]+)(\/([a-z]+)=([^\]\/]+)(\/([a-z]+)=([^\]\/]+)(\/([a-z]+)=([^\]\/]+)(\/([a-z]+)=([^\]\/]+))?)?)?)?)?)?\]/", $line, $matches)) {
                $rip = $matches;

                array_shift($rip); array_shift($rip);
                $out = array();
                while (count($rip) != 0) {
                    array_shift($rip);
                    $out[] = "\"" . array_shift($rip) . "\" => \"" . array_shift($rip) . "\"";
                }
                $line = str_replace($matches[0], "\n@ if (!isset(\$WIDGETS)) \$WIDGETS = array(); \$WIDGETS[\"{$matches[1]}\"] = array(" . implode(", ", $out) . ");\n@ include sockets/widgets/{$AVAILABLE_WIDGETS[$matches[1]]}.tpl\n", $line);
            }
    }

    service("ext", "overload", "tpl", "recursion", 0, "WidgetCallback");
}

?>
