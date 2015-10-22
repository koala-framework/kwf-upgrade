#!/usr/bin/php
<?php
require __DIR__.'/util/globrecursive.php';

if (is_file('vkwf_branch') || is_file('kwf_branch')) {
    die("This script will update from 3.10, update to 3.10 first.\n");
}
if (!is_file('composer.json')) {
    die("composer.json not found.\n");
}

$changed = false;
$c = json_decode(file_get_contents('composer.json'));
foreach ($c->require as $packageName=>$packageVersion) {
    if (substr($packageVersion, 0, 5) == "3.10.") {
        $c->require->$packageName = '3.11.x-dev';
        $changed = true;
    }
}
if (!$changed) {
    die("This script will update from 3.10, update to 3.10 first.\n");
}
file_put_contents('composer.json', json_encode($c, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0) + (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0) ));

$files = glob_recursive('Component.php');
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace("public function getTemplateVars()", 'public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer)', $c);
    $c = str_replace('public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)', 'public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer)', $c);
    $c = str_replace("::getTemplateVars()", '::getTemplateVars($renderer)', $c);
    if ($c != $origC) {
        echo "Added \$renderer in getTemplateVars in file: $file\n";
        file_put_contents($file, $c);
    }
    $origC = $c;
}

echo "\n";
echo "run now 'composer update' to update dependencies\n";
