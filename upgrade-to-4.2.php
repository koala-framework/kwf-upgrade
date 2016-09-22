#!/usr/bin/php
<?php
require __DIR__.'/util/globrecursive.php';
require __DIR__.'/util/deleteCacheFolder.php';

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
        $c->require->$packageName = '4.2.x-dev';
        $changed = true;
    }
}
if (!$changed) {
    die("This script will update from 4.1, update to 4.1 first.\n");
}

if (!isset($c->config)) $c->config = new stdClass;
if (!isset($c->config->platform)) $c->config->platform = new stdClass;
if (!isset($c->config->platform->php)) $c->config->platform->php = '5.3.17';
if (!isset($c->config->platform->{'ext-tidy'})) $c->config->platform->{'ext-tidy'} = '2.0';

file_put_contents('composer.json', json_encode($c, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0) + (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0) ));

echo "Replacing short-open-tags with <?php tags...\n";
$files = array_merge(
    glob_recursive('*.php'),
    glob_recursive('*.tpl')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $c = preg_replace('#<\?(?!php|=)#', '<?php ', $c);
    file_put_contents($file, $c);
}

echo "Add node_modules to gitignore";
$gitignore = file_get_contents('.gitignore');
$newIgnoreFiles = array();
foreach (array("/node_modules", "/package.json") as $i) {
    if (strpos($gitignore, $i) === false) {
        $newIgnoreFiles[] = $i;
    }
}

if ($newIgnoreFiles) {
    file_put_contents('.gitignore', $gitignore.implode("\n", $newIgnoreFiles)."\n");
}

echo "Remove node_modules from vendor/koala-framework/koala-framework";
exec("rm -rf vendor/koala-framework/koala-framework/node_modules");


//this has been don already in 3.11 but is now with 4.2 strictly enforced
$files = glob_recursive('Component.php');
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace("public function getTemplateVars()", 'public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer)', $c);
    $c = str_replace('public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)', 'public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer)', $c);
    $c = str_replace("::getTemplateVars()", '::getTemplateVars($renderer)', $c);
    if ($c != $origC) {
        file_put_contents($file, $c);
    }
}


$files = glob_recursive('Component.php');
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;

    $c = str_replace("public static function getSettings()", 'public static function getSettings($param = null)', $c);
    if ($c != $origC) {
        $c = str_replace("::getSettings()", '::getSettings($param)', $c);
    }

    $c = str_replace("public static function getSettings(\$masterComponentClass)", 'public static function getSettings($masterComponentClass = null)', $c);
    if ($c != $origC) {
        $c = str_replace("::getSettings()", '::getSettings($masterComponentClass)', $c);
    }

    $c = str_replace("public static function getSettings(\$masterComponent)", 'public static function getSettings($masterComponent = null)', $c);
    if ($c != $origC) {
        $c = str_replace("::getSettings()", '::getSettings($masterComponent)', $c);
    }

    if ($c != $origC) {
        file_put_contents($file, $c);
    }
}

if (!is_dir('cache/simpleStatic')) {
    mkdir('cache/simpleStatic');
    file_put_contents('cache/simpleStatic/.gitignore', "*\n!.gitignore\n");
    system("git add cache/simpleStatic/.gitignore");
    echo "folder \"cache/simpleStatic\" created\n";
}

copy(__DIR__.'/upgrade-to-4.2/20160913Update4dot2.php', 'app/Update/20160913Update4dot2.php');

echo "\n";
echo "run now 'composer update' to update dependencies\n";

