#!/usr/bin/php
<?php
require __DIR__.'/util/globrecursive.php';

if (is_file('vkwf_branch') || is_file('kwf_branch')) {
    die("This script will update from 5.0, update to 5.0 first.\n");
}
if (!is_file('composer.json')) {
    die("composer.json not found.\n");
}

$changed = false;
$c = json_decode(file_get_contents('composer.json'));
foreach ($c->require as $packageName=>$packageVersion) {
    if (substr($packageVersion, 0, 4) == "5.0.") {
        $c->require->$packageName = '5.1.x-dev';
        $changed = true;
    }
}
if (!$changed) {
    die("This script will update from 5.0, update to 5.0 first.\n");
}

file_put_contents('composer.json', json_encode($c, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0) + (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0) ));

$addNewsletterPackage = false;
foreach (glob_recursive('*.php') as $file) {
    $c = file_get_contents($file);
    $origC = $c;

    foreach (array("Kwc_NewsletterCategory", "Kwc_Newsletter") as $class) {
        $c = str_replace("extends {$class}", 'extends KwcNewsletter_Kwc_Newsletter', $c);
        $c = str_replace("'{$class}", '\'KwcNewsletter_Kwc_Newsletter', $c);
        $c = str_replace("\"{$class}", '"KwcNewsletter_Kwc_Newsletter', $c);
    }

    $dependentModels = array(
        'QueueLog' => 'QueueLogs',
        'Log' => 'Logs',
        'ToCategory' => 'ToCategories',
    );
    foreach ($dependentModels as $oldName => $newName) {
        $c = str_replace("'{$oldName}'", "'{$newName}'", $c);
        $c = str_replace("\"{$oldName}\"", "\"{$newName}\"", $c);
    }

    $models = array(
        'KwcNewsletter_Kwc_Newsletter_CategoriesModel' => 'Categories',
        'KwcNewsletter_Kwc_Newsletter_LogModel' => 'NewsletterLogs',
        'KwcNewsletter_Kwc_Newsletter_QueueLogModel' => 'NewsletterQueueLogs',
        'KwcNewsletter_Kwc_Newsletter_QueueModel' => 'NewsletterQueues',
        'KwcNewsletter_Kwc_Newsletter_Model' => 'Newsletters',
        'KwcNewsletter_Kwc_Newsletter_Subscribe_CategoriesModel' => 'SubscribeCategories',
        'KwcNewsletter_Kwc_Newsletter_Subscribe_LogsModel' => 'SubscriberLogs',
        'KwcNewsletter_Kwc_Newsletter_Subscribe_Model' => 'Subscribers',
        'KwcNewsletter_Kwc_Newsletter_Subscribe_SubscriberToCategory' => 'SubscribersToCategories'
    );
    foreach ($models as $oldClass => $newClass) {
        if (strpos($c, "extends {$oldClass}") !== false) {
            $c = str_replace("<?php", "<?php\n\nuse KwcNewsletter\\Bundle\\Model\\{$newClass};\n", $c);
            $c = str_replace("extends {$oldClass}", "extends {$newClass}", $c);
        } else {
            $c = str_replace($oldClass, "KwcNewsletter\\Bundle\\Model\\{$newClass}", $c);
        }
    }

    if ($c != $origC) {
        echo "renamed to KwcNewsletter_Kwc_Newsletter: $file\n";
        file_put_contents($file, $c);

        $addNewsletterPackage = true;
    }
}

$files = array_merge(
    glob_recursive('*.twig'),
    glob_recursive('*.tpl')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;

    foreach (array("NewsletterCategory", "Newsletter") as $dir) {
        $c = str_replace("vendor/koala-framework/koala-framework/Kwc/{$dir}", "vendor/koala-framework/kwc-newsletter/KwcNewsletter/Kwc/Newsletter", $c);
    }

    if ($c != $origC) {
        echo "renamed to kwc-newsletter vendor path: $file\n";
        file_put_contents($file, $c);

        $addNewsletterPackage = true;
    }
}

if ($addNewsletterPackage) {
    $c = json_decode(file_get_contents('composer.json'));
    $c->require->{'koala-framework/kwc-newsletter'} = "1.0.x-dev";
    echo "Added koala-framework/kwc-newsletter to require composer.json\n";
    file_put_contents('composer.json', json_encode($c, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0) + (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0) ));

    require_once __DIR__ . '/upgrade-to-5.1/add-symfony.php';
}

echo "\n";
echo "run now 'composer update' to update dependencies\n";

