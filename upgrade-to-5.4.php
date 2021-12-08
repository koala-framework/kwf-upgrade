#!/usr/bin/php
<?php
require __DIR__.'/util/globrecursive.php';

if (is_file('vkwf_branch') || is_file('kwf_branch')) {
    die("This script will update from 5.3, update to 5.3 first.\n");
}
if (!is_file('composer.json')) {
    die("composer.json not found.\n");
}

$changed = false;
$c = json_decode(file_get_contents('composer.json'));
foreach ($c->require as $packageName=>$packageVersion) {
    if (substr($packageVersion, 0, 4) == "5.3.") {
        $c->require->$packageName = '5.4.x-dev';
        $changed = true;
    }
}
if (!$changed) {
    die("This script will update from 5.3, update to 5.3 first.\n");
}

file_put_contents('composer.json', json_encode($c, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0) + (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0) ));

$files = array_merge(
    glob_recursive('*.php')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $pattern = '/(\$this->|\$acl->).+\'kwf_media_upload_any\'.+\n/';
    $c = preg_replace($pattern, '', $c);
    if ($c != $origC) {
        file_put_contents($file, $c);
        echo "removed usage of 'kwf_media_upload_any' from $file\n";
    }
}

echo "\n";
echo "run now 'composer update' to update dependencies\n";
