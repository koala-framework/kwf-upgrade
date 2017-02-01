#!/usr/bin/php
<?php
require __DIR__.'/util/globrecursive.php';

if (is_file('vkwf_branch') || is_file('kwf_branch')) {
    die("This script will update from 4.2, update to 4.2 first.\n");
}
if (!is_file('composer.json')) {
    die("composer.json not found.\n");
}

$changed = false;
$c = json_decode(file_get_contents('composer.json'));
foreach ($c->require as $packageName=>$packageVersion) {
    if (substr($packageVersion, 0, 4) == "4.2.") {
        $c->require->$packageName = '4.3.x-dev';
        $changed = true;
    }
}
if (!$changed) {
    die("This script will update from 4.2, update to 4.2 first.\n");
}

$usesTabsComponent = false;
$files = array_merge(
    glob_recursive('*.php'),
    glob_recursive('*.ini')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('Kwc_Tabs_Component', 'Kwc_Legacy_Tabs_Component', $c);
    $c = str_replace('KwfTabs', 'KwfLegacyTabs', $c);
    if ($c != $origC) {
        $usesTabsComponent = true;
        echo "Changed Kwc_Tabs_Component references in $file\n";
        file_put_contents($file, $c);
    }
}

file_put_contents('composer.json', json_encode($c, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0) + (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0) ));

echo "\n";
echo "run now 'composer update' to update dependencies\n";

