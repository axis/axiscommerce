<?php
/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @author      Axis Core Team <core@axiscommerce.com>
 */

$directories = array(
    '../app/code/Axis',
    '../app/design'
);
$allowedExtensions = array(
    'php',
    'phtml'
);
$output = '../var/translates.php';

$allMatches = array();
$dublicate = array();

function scanDirectories(array $directories){
    foreach ($directories as $path) {
        scanDirectory($path);
    }
}

function scanDirectory($path) {
    if (strstr("$path", '/.hg')) {
        echo 'SKIPPING ' . $path . "\n";
    } elseif (is_dir($path)) {
        echo 'SCANNING ' . $path . "\n";
        $dir = dir($path);
        while (false !== ($file = $dir->read())) {
            if ($file != '.' && $file != '..') {
                if (!is_link("$path/$file") && is_dir("$path/$file")) {
                    scanDirectory("$path/$file");
                } else {
                    scanFile("$path/$file");
                }
            }
        }
        $dir->close();
    }
}

function scanFile($path)
{
    global $allMatches;
    global $allowedExtensions;
    global $dublicate;
    
    $filename = basename($path);
    if (!in_array(substr($filename, strrpos($filename, '.') + 1), $allowedExtensions)) {
        return;
    }
//    if (!strstr($path, 'Form')) {
//        return;
//    }
    echo 'PARSING ' . $path . "\n";
    $regex = "/(Axis::|this->)translate\((.*)\)->__\(\n*\s*('.*'|\".*\")/U";
    
    $content = file_get_contents($path);
    $matches = array();
    if (preg_match_all($regex, $content, $matches) != 0) {

        $matches[2] = array_filter($matches[2]);
        $matches[3] = array_filter($matches[3]);

        $i = 0;
        
        foreach ($matches[2] as $model) {
            $text = $matches[3][$i];

            $allMatches[md5($model . '+' . $matches[3][$i])] =
                'Axis::translate(' . $model . ')->__(' . $text
                . ');'
              //  . ");\n//" . $path
            ;
            $i++;
            $before = '';
            if (isset($dublicate[$text][$model])) {
                $before = $dublicate[$text][$model];
            }
            $dublicate[$text][$model] = $before . ' ' . $path;

        }
    }
}

scanDirectories($directories);
sort($allMatches);


$dub = array();
foreach ($dublicate as $text => $item) {
    if (!is_array($item) || count($item) < 2) {
        continue;
    }
    $dub[$text] = $item;
}
print_r($dub);

$content = "<?php \n";
foreach ($allMatches as $string) {
    $content .= $string . "\n";
}
file_put_contents($output, $content . "\n;");