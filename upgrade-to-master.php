<?php
if (is_file('vkwf_branch') || is_file('kwf_branch')) {
    die("This script will update from 3.8, update to 3.8 first.\n");
}
if (!is_file('composer.json')) {
    die("composer.json not found.\n");
}
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die("Execute composer install in kwf-upgrade first");
}

$changed = false;
$c = json_decode(file_get_contents('composer.json'));
foreach ($c->require as $packageName=>$packageVersion) {
    if (substr($packageVersion, 0, 4) == "3.8.") {
        $c->require->$packageName = 'dev-master';
        $changed = true;
    }
}
if (!$changed) {
    die("This script will update from 3.8, update to 3.8 first.\n");
}
file_put_contents('composer.json', json_encode($c, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0) + (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0) ));

//---------------------------------------------------------
// Replace trl with trlStatic in getSettings
require __DIR__ . '/vendor/autoload.php';
require __DIR__.'/upgrade-to-master/PhpParserVisitor.php';

echo "Parsing code to check for trl in getSettings\n";
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

$parser = new \PhpParser\Parser(new \PhpParser\Lexer);
$traverser = new \PhpParser\NodeTraverser;
$visitor = new PhpParserVisitor;
$traverser->addVisitor($visitor);
$files = glob_recursive('Component.php');
foreach ($files as $file) {
    echo $file."\n";
    $visitor->resetWrongTrlMasks();
    $traverser->traverse($parser->parse(file_get_contents($file)));
    $wrongTrlMasks = $visitor->getWrongTrlMasks();
    $lines = explode("\n", file_get_contents($file));
    $count = 0;
    foreach ($wrongTrlMasks as $trlType => $positions) {
        foreach ($positions as $position) {
            $lines[$position['line']-1] = str_replace($trlType, $trlType.'Static', $lines[$position['line']-1]);
            $count++;
        }
    }
    file_put_contents($file, implode("\n", $lines));
    echo "Changed $count\n";
}
//---------------------------------------------------------


passthru("php ".__DIR__."/upgrade-to-master/upgrade-update-scripts.php", $ret);
if (!$ret) exit($ret);


echo "\n";
echo "run now 'composer update' to update dependencies\n";
