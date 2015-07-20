#!/usr/bin/php
<?php
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
        $c->require->$packageName = 'dev-master';
        $changed = true;
    }
}
if (!$changed) {
    die("This script will update from 3.9, update to 3.9 first.\n");
}

if (!isset($c->extra)) {
    $c->extra = (object)array();
}
if (!isset($c->extra->{'require-bower'})) {
    $c->extra->{'require-bower'} = (object)array();
}
$c->extra->{'require-bower'}->susy = "vivid-planet/susy#8161395e8ad5d75a0a15a0355feb9853ebaad369";
$c->extra->{'require-bower'}->jquery = "1.11.3";
echo "Added susyone and jquery to require-bower\n";
file_put_contents('composer.json', json_encode($c, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0) + (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0) ));


function glob_recursive($pattern, $flags = 0) {
    $files = glob($pattern, $flags);
    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
        if (dirname($dir) == './kwf-lib' || $dir == './kwf-lib') continue;
        if (dirname($dir) == './vkwf-lib' || $dir == './vkwf-lib') continue;
        if (dirname($dir) == './library' || $dir == './library') continue;
        if (dirname($dir) == './vendor' || $dir == './vendor') continue;
        $files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
    }
    return $files;
}

$files = array_merge(
    glob_recursive('*.tpl'),
    glob_recursive('*.twig'),
    glob_recursive('*.css'),
    glob_recursive('*.scss'),
    glob_recursive('*.js')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('webStandard', 'kwfup-webStandard', $c);
    $c = str_replace('webForm', 'kwfup-webForm', $c);
    $c = str_replace('webListNone', 'kwfup-webListNone', $c);
    $c = str_replace('webMenu', 'kwfup-webMenu', $c);
    $c = str_replace('kwcFormError', 'kwfup-kwcFormError', $c);
    $c = str_replace('printHidden', 'kwfup-printHidden', $c);
    if ($c != $origC) {
        echo "added kwfup- class prefix in $file\n";
        file_put_contents($file, $c);
    }
}

$files = array_merge(
    glob_recursive('*.css'),
    glob_recursive('*.scss'),
    glob_recursive('*.js')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('.frontend', '.kwfup-frontend', $c);
    if ($c != $origC) {
        echo "added kwfup- class prefix in $file\n";
        file_put_contents($file, $c);
    }
}

$files = array_merge(
    glob_recursive('*.tpl'),
    glob_recursive('*.twig')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('class="clear"', 'class="kwfup-clear"', $c);
    $c = str_replace('class="left"', 'class="kwfup-left"', $c);
    $c = str_replace('class="right"', 'class="kwfup-right"', $c);
    if ($c != $origC) {
        echo "added kwfup- class prefix in $file\n";
        file_put_contents($file, $c);
    }
}



echo "\n";
echo "run now 'composer update' to update dependencies\n";
