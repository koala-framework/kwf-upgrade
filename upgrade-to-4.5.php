#!/usr/bin/php
<?php
require __DIR__.'/util/globrecursive.php';

if (is_file('vkwf_branch') || is_file('kwf_branch')) {
    die("This script will update from 4.4, update to 4.4 first.\n");
}
if (!is_file('composer.json')) {
    die("composer.json not found.\n");
}

$changed = false;
$c = json_decode(file_get_contents('composer.json'));
foreach ($c->require as $packageName=>$packageVersion) {
    if (substr($packageVersion, 0, 4) == "4.4.") {
        $c->require->$packageName = '4.5.x-dev';
        $changed = true;
    }
}
if (!$changed) {
    die("This script will update from 4.4, update to 4.4 first.\n");
}
if (isset($c->extra->{'require-bower'}->jquery)) {
    unset($c->extra->{'require-bower'}->jquery);
}

file_put_contents('composer.json', json_encode($c, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0) + (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0) ));

$files = array_merge(glob_recursive('*.php'), glob_recursive('*.tpl'));
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;

    $c = str_replace("htmlspecialchars", 'Kwf_Util_HtmlSpecialChars::filter', $c);

    if ($c != $origC) {
        file_put_contents($file, $c);
    }
}

echo "\n";
echo "run now 'composer update' to update dependencies\n";

