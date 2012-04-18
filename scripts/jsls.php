<?php
/**
 * Axis
 *
 * This file is part of Axis.
 *
 * Axis is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Axis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Axis.  If not, see <http://www.gnu.org/licenses/>.
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 * JavaScript localization search
 * This script will find all language sensitive statements, that are thanslated via js function l() and output them to $output
 *
 * @category    Axis
 * @package     Axis_Core
 * @author      Axis Core Team <core@axiscommerce.com>
 */
$directories = array(
    'D:/www/htdocs/axiscommerce.com/public_html/axis/js/axis/admin',
    'D:/www/htdocs/axiscommerce.com/public_html/axis/app/design/admin/default'
);
$allowedExtensions = array('phtml', 'js');
$output = 'D:/core.js';
$module = 'core';

$allMatches = array();

function scanDirectories(array $directories){
    foreach ($directories as $path) {
        scanDirectory($path);
    }
}

function scanDirectory($path){
    if (strstr("$path", '/.svn')) {
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

function scanFile($path) {
    global $allMatches;
    global $allowedExtensions;
    
    $filename = basename($path);
    if (!in_array(substr($filename, strrpos($filename, '.') + 1), $allowedExtensions)) {
        return;
    }
    
    echo 'PARSING ' . $path . "\n";
    $regex = "/([\"][^\"|.]+[\"]).l\(\)|([\'][^'|.]+[\']).l\(/";
    $content = file_get_contents($path);
    $matches = array();
    if (preg_match_all($regex, $content, $matches) != 0) {
        $matches[1] = array_filter($matches[1]);
        $matches[2] = array_filter($matches[2]);
        foreach ($matches[1] as $string) {
            $string = str_replace('"', "'", $string);
            $allMatches[$string] = $string;
        }
        foreach ($matches[2] as $string) {
            $allMatches[$string] = $string;
        }
    }
}

scanDirectories($directories);

$content = "Locale.module('{$module}', {\n";
foreach ($allMatches as $string) {
    $content .= '    ' . $string . ': ' . $string . ",\n";
}
if (count($allMatches)) {
    $content = substr($content, 0, -2);
}
file_put_contents($output, $content . "\n});");