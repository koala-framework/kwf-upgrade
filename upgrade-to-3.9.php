#!/usr/bin/php
<?php
require __DIR__.'/util/globrecursive.php';

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
        $c->require->$packageName = '3.9.x-dev';
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
require __DIR__.'/upgrade-to-3.9/PhpParserVisitor.php';

echo "Parsing code to check for trl in getSettings\n";

$parser = new \PhpParser\Parser(new \PhpParser\Lexer);
$traverser = new \PhpParser\NodeTraverser;
$visitor = new PhpParserVisitor;
$traverser->addVisitor($visitor);
$files = glob_recursive('Component.php');
foreach ($files as $file) {
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
    if ($count) {
        file_put_contents($file, implode("\n", $lines));
        echo "$file: Changed $count\n";
    }
}


//---------------------------------------------------------
// Convert trl.xml into po files
if (file_exists("trl.xml")) {
    $trlXmlDocument = simplexml_load_string(file_get_contents("trl.xml"));
    $languages = array();
    foreach ($trlXmlDocument->text as $trls) {
        foreach ($trls as $lang => $trl) {
            if ($lang === 'id') continue;
            if (strpos($lang, '_plural') !== false) continue;
            if (strpos($lang, 'context') !== false) continue;
            $languages[$lang] = true;
        }
    }
    if (!is_dir('trl')) mkdir('trl');
    $config = parse_ini_file('config.ini');
    $webcodeLanguage = $config['webCodeLanguage'];
    foreach (array_keys($languages) as $lang) {
        passthru('php '.__DIR__.'/upgrade-to-3.9/trl convertTrlXmlToPo '
            .'--outputPath="'.getcwd().'/trl/'.$lang.'.po" '
            .'--webcodeLanguage="'.$webcodeLanguage.'" '
            .'--targetLanguage="'.$lang.'" '
            .'"'.getcwd().'/trl.xml"', $ret);
        if ($ret) exit($ret);
    }
    unlink('trl.xml');
    echo "Converted trl.xml into po files. Languages: ".implode(", ", array_keys($languages))."\n";
    if (file_exists('vendor/vivid-planet/vkwf')) {
        echo "trl folder added to gitignore\n";
        file_put_contents('.gitignore', file_get_contents('.gitignore')."trl\n");
    } else {
        echo "no vivid-planet/vkwf existing. Didn't add trl folder to gitignore\n";
    }
}


//---------------------------------------------------------

passthru("php ".__DIR__."/upgrade-to-3.9/upgrade-update-scripts.php", $ret);
if ($ret) exit($ret);


$c = file_get_contents('.htaccess');
$c .= "
<IfModule dir_module>
    DirectorySlash Off
</IfModule>
";
file_put_contents('.htaccess', $c);

echo "\n";
echo "run now 'composer update' to update dependencies\n";
