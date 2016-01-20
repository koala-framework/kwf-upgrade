#!/usr/bin/php
<?php
require __DIR__.'/util/globrecursive.php';
require __DIR__.'/util/deleteCacheFolder.php';

if (is_file('vkwf_branch') || is_file('kwf_branch')) {
    die("This script will update from 4.0, update to 4.0 first.\n");
}
if (!is_file('composer.json')) {
    die("composer.json not found.\n");
}

$changed = false;
$c = json_decode(file_get_contents('composer.json'));
foreach ($c->require as $packageName=>$packageVersion) {
    if (substr($packageVersion, 0, 4) == "4.0.") {
        $c->require->$packageName = '4.1.x-dev';
        $changed = true;
    }
}
if (!$changed) {
    die("This script will update from 4.0, update to 4.0 first.\n");
}
file_put_contents('composer.json', json_encode($c, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0) + (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0) ));

if (!is_dir('cache/assetdeps')) {
    mkdir('cache/assetdeps');
    file_put_contents('cache/assetdeps/.gitignore', "*\n!.gitignore\n");
    system("git add cache/assetdeps/.gitignore");
    echo "folder \"cache/assetdeps\" created\n";
}

deleteCacheFolder('cache/scss');
deleteCacheFolder('cache/componentassets');


//try common location for component where boxes are created
$files = array(
    'themes/Theme/Component.php',
    'components/Root/Component.php',
    'components/Root/Domain/Component.php',
    'components/Root/Master/Component.php',
);
foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $c = file_get_contents($file);
    $origC = $c;
    $c = preg_replace("#(\\\$ret\['generators'\]\['assets'\] = array\(.*?) *'unique' *=> *true,? *\n?(.*?\);\s*)#s", '\1\2', $c);
    if ($c != $origC) {
        echo "removed unique for assets box: $file\n";
        file_put_contents($file, $c);
    }
}

echo "\n";
echo "run now 'composer update' to update dependencies\n";

