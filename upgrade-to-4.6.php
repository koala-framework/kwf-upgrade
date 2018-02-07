#!/usr/bin/php
<?php
require __DIR__.'/util/globrecursive.php';

if (is_file('vkwf_branch') || is_file('kwf_branch')) {
    die("This script will update from 4.5, update to 4.5 first.\n");
}
if (!is_file('composer.json')) {
    die("composer.json not found.\n");
}

$changed = false;
$c = json_decode(file_get_contents('composer.json'));
foreach ($c->require as $packageName=>$packageVersion) {
    if (substr($packageVersion, 0, 4) == "4.5.") {
        $c->require->$packageName = '4.6.x-dev';
        $changed = true;
    }
}
if (!$changed) {
    die("This script will update from 4.5, update to 4.5 first.\n");
}

file_put_contents('composer.json', json_encode($c, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0) + (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0) ));

echo "\n";
echo "run now 'composer update' to update dependencies\n";

