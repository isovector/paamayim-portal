<?php

$file = "bar.png";
$splash = "splash.png";

$direction = @isset($_GET["d"]) ? $_GET["d"] : "-y";
$good =     (@isset($_GET["G"]) && is_numeric($_GET["G"]) ? $_GET["G"] : 0xFF0000);
$bad =      (@isset($_GET["B"]) && is_numeric($_GET["B"]) ? $_GET["B"] : 0xFFFFFF);
$current =  (@isset($_GET["c"]) && is_numeric($_GET["c"]) ? $_GET["c"] : 0);
$goal =     (@isset($_GET["g"]) && is_numeric($_GET["g"]) ? $_GET["g"] : 0);

if ($current >= $goal)
    $img = imagecreatefrompng($splash);
else
    $img = imagecreatefrompng($file);

imagealphablending($img, true);
imagesavealpha($img, true); 
list($width, $height) = getimagesize($file);

function blitable($img, $x, $y) {
    return imagecolorat($img, $x, $y) == 0xFF00FF;
}

$lines = array();
if ($direction == "x" || $direction == "+x") {
    for ($x = 0; $x < $width; $x++)
        for ($y = 0; $y < $height; $y++)
            if (blitable($img, $x, $y)) {
                $lines[] = array($x, -1);
                break;
            }
} else if ($direction == "-x") {
    for ($x = $width - 1; $x >= 0; $x--)
        for ($y = 0; $y < $height; $y++)
            if (blitable($img, $x, $y)) {
                $lines[] = array($x, -1);
                break;
            }
} else if ($direction == "y" || $direction == "+y") {
    for ($y = 0; $y < $height; $y++)
        for ($x = 0; $x < $width; $x++)
            if (blitable($img, $x, $y)) {
                $lines[] = array(-1, $y);
                break;
            }
} else if ($direction == "-y") {
    for ($y = $height - 1; $y >= 0; $y--) 
        for ($x = 0; $x < $width; $x++)
            if (blitable($img, $x, $y)) {
                $lines[] = array(-1, $y);
                break;
            }
} else goto end;

$limit = floor($current * count($lines) / $goal);
$count = 0;

foreach ($lines as $line) {
    list($x, $y) = $line;

    if ($x == -1)
        for ($x = 0; $x < $width; $x++)
            if (imagecolorat($img, $x, $y) == 0xFF00FF)
                imagesetpixel($img, $x, $y, $count < $limit ? $good : $bad);
    if ($y == -1)
        for ($y = 0; $y < $height; $y++)
            if (imagecolorat($img, $x, $y) == 0xFF00FF)
                imagesetpixel($img, $x, $y, $count < $limit ? $good : $bad);
    $count++;
}

end:
header("Content-type: image/png");
imagepng($img);
die();

?>