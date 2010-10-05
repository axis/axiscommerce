<?php
/**
 * @copyright  matera.ttp@gmail.com
 * Script looks for unused methods
 */

function rglob($pattern, $flags = 0, $path = '') {
    if (!$path && ($dir = dirname($pattern)) != '.') {
        if ($dir == '\\' || $dir == '/') $dir = '';
        return rglob(basename($pattern), $flags, $dir . '/');
    }
    $paths = glob($path . '*', GLOB_ONLYDIR | GLOB_NOSORT);
    $files = glob($path . $pattern, $flags);
    foreach ($paths as $p)  {
        $files = array_merge($files, rglob($pattern, $flags, $p . '/'));
    }
    return $files;
}

function getMethods($file)
{
    $content = file_get_contents($file);
    $pattern = '/function +(\w+)\(/s';
    preg_match_all($pattern, $content, $matches);
    return $matches[1];
}

function matchCount($content, $method)
{
    $search_pattern = "/$method\(/";
    preg_match_all($search_pattern, $content, $matches);
    $count = count($matches[0]);
    
    preg_match_all("/'$method'/", $content, $matches);// service call
    $count += count($matches[0]);
    return $count;
}

function skipMethod($method)
{
    if (strpos($method, '__') === 0)
        return true;
    if (preg_match('/Action$/', $method))
        return true;
    return false;
}

$dirs = array('../app', '../library/Axis', '../scripts');

$files = array();
$filesUsage = array();
foreach ($dirs as $dir) {
    $files = array_merge($files, rglob($dir . '/*.php'));
    $filesUsage = array_merge($filesUsage, rglob($dir . '/*.php'));
    $filesUsage = array_merge($filesUsage, rglob($dir . '/*.phtml'));
}

$methods = array();
$methodToFile = array();
foreach ($files as $file) {
    $_methods = getMethods($file);
    foreach ($_methods as $method) {
        $methods[$method] = 0;
        if (!isset($methodToFile[$method]))
            $methodToFile[$method] = array();
        $methodToFile[$method][] = $file;
    }
}

echo "Methods: " . count($methods) . "\n";

/*
 * Looking for unused methods
 */

foreach ($filesUsage as $file) {
    $content = file_get_contents($file);
    foreach ($methods as $method => $cnt) {
        $matches = matchCount($content, $method);
        $methods[$method] += $matches;
    }
}

echo "\n----------------UNUSED-------------------\n";
$unused = 0;
foreach ($methods as $method => $cnt) {
    if (skipMethod($method))
        continue;
    if ($cnt == 1) {
        ++$unused;
        echo "\n$method\n";
        echo implode("\n", $methodToFile[$method]) . "\n";
    }
}
echo "Total unused: $unused\n";