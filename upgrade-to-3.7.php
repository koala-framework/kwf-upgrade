#!/usr/bin/php
<?php
require __DIR__.'/util/globrecursive.php';

$file = is_file('vkwf_branch') ? 'vkwf_branch' : 'kwf_branch';
if (!file_exists($file)) die("Execute this script in app root.\n");
if (trim(file_get_contents($file)) != '3.6') die("This script will update from 3.6, update to 3.6 first.\n");

file_put_contents($file, "3.7\n");
echo "Changed $file to 3.7\n";

function replaceFiles($files, $from, $to) {
    foreach ($files as $f) {
        $content = file_get_contents($f);
        if (strpos($content, $from)) {
            file_put_contents($f, str_replace($from, $to, $content));
            echo "Change $f: $from -> $to\n";
        }
    }
}

function updateConfig() {
    $c = file_get_contents('config.ini');
    $c = preg_replace('#^preview\.responsive#m', 'kwc.responsive', $c);
    $c = str_replace('kwc.fbAppData', 'fbAppData', $c);
    file_put_contents('config.ini', $c);
}
function createMediaMetaCacheFolder()
{
    if (!is_dir('cache/mediameta')) {
        mkdir('cache/mediameta');
        file_put_contents('cache/mediameta/.gitignore', "*\n!.gitignore\n");
        system("git add cache/mediameta/.gitignore");
        echo "folder \"cache/mediameta\" created\n";
    }
}

$files = glob_recursive('Component.php');
$files = array_merge($files, glob_recursive('config.ini'));
replaceFiles($files, 'Kwc_Columns_Component', 'Kwc_Legacy_Columns_Component');
replaceFiles($files, 'Kwc_ColumnsResponsive_Component', 'Kwc_Columns_Component');
replaceFiles($files, 'Kwc_Advanced_DyamicContent_Component', 'Kwc_Advanced_DynamicContent_Component');
updateConfig();
createMediaMetaCacheFolder();

