<?php
/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @author      Axis Core Team <core@axiscommerce.com>
 */

$directories = array(
    '../app/code/Axis/Admin'
);
$allowedExtensions = array('php');
$output = '../var/resources.php';

$allMatches = array();

function scanDirectories(array $directories){
    foreach ($directories as $path) {
        scanDirectory($path);
    }
}

function scanDirectory($path){
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

function uppertodefise($str)
{
    if ($str == strtolower($str)) {
        return $str;
    }
    if ( false === function_exists('lcfirst') ):
        function lcfirst($str)
        { 
            return (string)(strtolower(substr($str, 0, 1)) . substr($str, 1));
        }
    endif;

    $str = lcfirst($str);
    $ret = '';
    foreach (str_split($str) as $key => $char) {
        //echo !$key ? $key : 0 ;
        if ($char != strtolower($char)) {
            $ret .= '-' . strtolower($char);
        } else {
            $ret .= $char;
        }

    }
    return $ret;
}

function scanFile($path)
{
    global $allMatches;
    global $allowedExtensions;
    
    $filename = basename($path);
    if (!in_array(substr($filename, strrpos($filename, '.') + 1), $allowedExtensions)) {
        return;
    }

    if (!strstr($filename, 'Controller')) {
        return;
    }
    
    echo 'PARSING ' . $path . "\n";
    $regex = "/\s(\w*)Action\(\)/";
    $content = file_get_contents($path);
    $matches = array();
    if (preg_match_all($regex, $content, $matches) != 0) {
        $matches[1] = array_filter($matches[1]);

        $controller =  str_replace('Controller.php', '', $filename);
        $words = explode('/', $path);
        $subModule = null;
        foreach ($words as $word) {
            if ($word == 'controllers') {
                $module = strtolower($prev);
            }
            if ($prev == 'controllers' && !strstr($word, 'Controller')) {
                $subModule = strtolower($word);
            }
            $prev = $word;
        }
        $controller = uppertodefise($controller);
        if  (null != $subModule) {
            $controller = uppertodefise($subModule) . '_' . $controller;
        }
        foreach ($matches[1] as $action) {
            
            $allMatches[] = $module . '/' . $controller . '/' . uppertodefise($action);
        }
    }
}

scanDirectories($directories);
sort($allMatches);
$content = "<?php \n Axis::single('admin/acl_resource')\n";
foreach ($allMatches as $string) {
    $content .= '->add("' . $string . "\")\n";
}
file_put_contents($output, $content . "\n;");