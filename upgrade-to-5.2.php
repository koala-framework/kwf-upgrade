#!/usr/bin/php
<?php

require __DIR__.'/util/globrecursive.php';

if (is_file('vkwf_branch') || is_file('kwf_branch')) {
    die("This script will update from 5.1, update to 5.1 first.\n");
}
if (!is_file('composer.json')) {
    die("composer.json not found.\n");
}

$changed = false;
$c = json_decode(file_get_contents('composer.json'));
foreach ($c->require as $packageName=>$packageVersion) {
    if (substr($packageVersion, 0, 4) == "5.1.") {
        $c->require->$packageName = '5.2.x-dev';
        $changed = true;
    }
}
if (!$changed) {
    die("This script will update from 5.1, update to 5.1 first.\n");
}

file_put_contents('composer.json', json_encode($c, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0) + (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0) ));

$files = array_merge(
    glob_recursive('*.js')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('KWF_BASE_URL+', '', $c);
    if ($c != $origC) {
        file_put_contents($file, $c);
    }
}

$files = array_merge(
    glob_recursive('*.jsx')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('${KWF_BASE_URL}', '', $c);
    if ($c != $origC) {
        file_put_contents($file, $c);
    }
}

$file = 'symfony/config/config.yml';
if (is_file($file)) {
    $c = file_get_contents($file);
    $c .= <<<YML

kwf:
    model:
        normalizer_camel_case: false

YML;
    file_put_contents($file, $c);
}

echo "\n";
echo "run now 'composer update' to update dependencies\n";

