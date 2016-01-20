#!/usr/bin/php
<?php
require __DIR__.'/util/globrecursive.php';

if (is_file('vkwf_branch') || is_file('kwf_branch')) {
    die("This script will update from 4.1, update to 4.1 first.\n");
}
if (!is_file('composer.json')) {
    die("composer.json not found.\n");
}

$changed = false;
$c = json_decode(file_get_contents('composer.json'));
foreach ($c->require as $packageName=>$packageVersion) {
    if (substr($packageVersion, 0, 4) == "4.1.") {
        $c->require->$packageName = 'dev-master';
        $changed = true;
    }
}
if (!$changed) {
    die("This script will update from 4.1, update to 4.1 first.\n");
}

echo "\n";
echo "run now 'composer update' to update dependencies\n";

