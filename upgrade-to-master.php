#!/usr/bin/php
<?php
require __DIR__.'/util/globrecursive.php';

if (is_file('vkwf_branch') || is_file('kwf_branch')) {
    die("This script will update from 4.3, update to 4.3 first.\n");
}
if (!is_file('composer.json')) {
    die("composer.json not found.\n");
}

$changed = false;
$c = json_decode(file_get_contents('composer.json'));
foreach ($c->require as $packageName=>$packageVersion) {
    if (substr($packageVersion, 0, 4) == "4.3.") {
        $c->require->$packageName = 'dev-master';
        $changed = true;
    }
}
if (!$changed) {
    die("This script will update from 4.3, update to 4.3 first.\n");
}

file_put_contents('composer.json', json_encode($c, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0) + (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0) ));


$usesMedialelement = false;
$files = array_merge(glob_recursive('*.php'), glob_recursive('*.ini'));
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;

    $c = str_replace("Kwc_Advanced_VideoPlayer_", 'Mediaelement_Kwc_VideoPlayer_', $c);
    $c = str_replace("Kwc_Advanced_AudioPlayer_", 'Mediaelement_Kwc_AudioPlayer_', $c);

    if ($c != $origC) {
        echo "Videoplayer and/or Audioplayer is used, change them to use Kwc_Mediaelement\n";
        file_put_contents($file, $c);
        $usesMedialelement = true;
    }
}
if ($usesMedialelement) {
    $c = json_decode(file_get_contents('composer.json'));
    $updateComposerJson = true;
    foreach ($c->require as $packageName=>$packageVersion) {
        if ($packageName == "koala-framework/kwc-mediaelement") {
            $updateComposerJson = false;
        }
    }
    if ($updateComposerJson) {
        $c->require->{'koala-framework/kwc-mediaelement'} = "1.0.x-dev";
        echo "Added koala-framework/kwc-mediaelement to require composer.json\n";
        file_put_contents('composer.json', json_encode($c, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0) + (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0) ));
    }
}

echo "\n";
echo "run now 'composer update' to update dependencies\n";

