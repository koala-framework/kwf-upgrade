#!/usr/bin/php
<?php
require __DIR__.'/util/globrecursive.php';

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
        $c->require->$packageName = 'dev-master';
        $changed = true;
    }
}
if (!$changed) {
    die("This script will update from 4.1, update to 4.1 first.\n");
}

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

echo "\n";
echo "run now 'composer update' to update dependencies\n";

