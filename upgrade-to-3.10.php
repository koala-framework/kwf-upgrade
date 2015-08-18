#!/usr/bin/php
<?php
require __DIR__.'/util/globrecursive.php';

if (is_file('vkwf_branch') || is_file('kwf_branch')) {
    die("This script will update from 3.9, update to 3.9 first.\n");
}
if (!is_file('composer.json')) {
    die("composer.json not found.\n");
}

$changed = false;
$c = json_decode(file_get_contents('composer.json'));
foreach ($c->require as $packageName=>$packageVersion) {
    if (substr($packageVersion, 0, 4) == "3.9.") {
        $c->require->$packageName = '3.10.x-dev';
        $changed = true;
    }
}
if (!$changed) {
    die("This script will update from 3.9, update to 3.9 first.\n");
}
file_put_contents('composer.json', json_encode($c, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0) + (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0) ));

$files = glob_recursive('Master.tpl');
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace("<html", '<html lang="<?=$this->pageLanguage?>"', $c);
    if ($c != $origC) {
        echo "Added lang attribute to <html: $file\n";
        file_put_contents($file, $c);
    }
    $origC = $c;
    $search = '<?=$this->doctype('."'".'XHTML1_STRICT'."'".');?>';
    $c = str_replace($search, '<!DOCTYPE html>', $c);
    if ($c != $origC) {
        echo "Changed doctype to html5 in file: $file\n";
        file_put_contents($file, $c);
    }
    $origC = $c;
    $c = str_replace(' xmlns="http://www.w3.org/1999/xhtml"', '', $c);
    if ($c != $origC) {
        echo "Removed xmlns in file: $file\n";
        file_put_contents($file, $c);
    }

}

$files = glob_recursive('Master.twig');
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace("<html", '<html lang="{{ pageLanguage }}"', $c);
    if ($c != $origC) {
        echo "Added lang attribute to <html: $file\n";
        file_put_contents($file, $c);
    }
    $origC = $c;
    $c = str_replace('<?=$this->doctype(\'XHTML1_STRICT\');?>', '<!DOCTYPE html>', $c);
    if ($c != $origC) {
        echo "Changed doctype to html5 in file: $file\n";
        file_put_contents($file, $c);
    }
    $origC = $c;
    $c = str_replace(' xmlns="http://www.w3.org/1999/xhtml"', '', $c);
    if ($c != $origC) {
        echo "Removed xmlns in file: $file\n";
        file_put_contents($file, $c);
    }
}

$c = file_get_contents('bootstrap.php');
$origC = $c;
$c = str_replace("Kwf_Util_Https::ensureHttps();\n", '', $c);
if ($c != $origC) {
    echo "removed Kwf_Util_Https::ensureHttps: $file\n";
    file_put_contents($file, $c);
}



echo "\n";
echo "run now 'composer update' to update dependencies\n";
