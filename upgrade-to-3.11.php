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

$ini = parse_ini_file('config.ini');
$isPartnernetWeb = false;
foreach ($ini as $k => $i) {
    if (strpos($k, 'partnernet') !== false) {
        $isPartnernetWeb = true;
        $c->require->{"vivid-planet/partnernet"} = "1.x";
        break;
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

if ($isPartnernetWeb) {
    // Rename components from packages to new package-naming-convention
    $files = glob_recursive('*.php');
    $files = array_merge($files, glob_recursive('config.ini'));
    foreach ($files as $file) {
        $c = file_get_contents($file);
        $origC = $c;

        $c = str_replace('Vkwf_PartnerNet_Model_Auth_PnLogin', 'PartnerNet_Auth_Legacy_PnLogin', $c);
        $c = str_replace('Vkwf_Auth_Adapter_PartnerNet', 'PartnerNet_Auth_Legacy_Adapter', $c);

        $c = str_replace('Vkwf_PartnerNet_', 'PartnerNet_', $c); //Für /Controller, /Model, Client.php, UserRoleData.php
        $c = str_replace('Vkwc_PartnerNet_', 'PartnerNet_Kwc_', $c);

        if ($c != $origC) {
            echo "renamed $origC to $c: $file\n";
            file_put_contents($file, $c);
        }
    }

    echo "\n";
    echo "PARTNER NET:\n";
    echo "   dont extend PartnerNet_Acl!\n";
    echo '   add PartnerNet_Acl::initialise($this); to your Acl__construct-Function'."\n";
}

echo "\n";
echo "run now 'composer update' to update dependencies\n";
