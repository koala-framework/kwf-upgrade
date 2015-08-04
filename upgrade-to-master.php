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
    $c = str_replace('webStandard', 'kwfUp-webStandard', $c);
    $c = str_replace('webForm', 'kwfUp-webForm', $c);
    $c = str_replace('webListNone', 'kwfUp-webListNone', $c);
    $c = str_replace('webMenu', 'kwfUp-webMenu', $c);
    $c = str_replace('kwcFormError', 'kwfUp-kwcFormError', $c);
    $c = str_replace('printHidden', 'kwfUp-printHidden', $c);
    if ($c != $origC) {
        echo "added kwfUp- class prefix in $file\n";
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
    $c = str_replace('.frontend', '.kwfUp-frontend', $c);
    if ($c != $origC) {
        echo "added kwfUp- class prefix in $file\n";
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
    $c = str_replace('class="clear"', 'class="kwfUp-clear"', $c);
    $c = str_replace('class="left"', 'class="kwfUp-left"', $c);
    $c = str_replace('class="right"', 'class="kwfUp-right"', $c);
    if ($c != $origC) {
        echo "added kwfUp- class prefix in $file\n";
        file_put_contents($file, $c);
    }
}


$files = array_merge(
    glob_recursive('Component.js'),
    glob_recursive('Component.defer.js')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    if (strpos($c, 'Kwf.Utils.ResponsiveEl') !== false) {
        $c = "var responsiveEl = require('kwf/responsive-el');\n".$c;
        $c = str_replace('Kwf.Utils.ResponsiveEl', 'responsiveEl', $c);
    }
    if (strpos($c, 'Kwf.onJElementReady') !== false || strpos($c, 'Kwf.onJElementShow') !== false || strpos($c, 'Kwf.onJElementHide') !== false || strpos($c, 'Kwf.onJElementWidthChange') !== false) {
        $c = "var $ = require('jQuery');\n".$c;
        $c = "var onReady = require('kwf/on-ready');\n".$c;
        $c = str_replace('Kwf.onJElementReady', 'onReady.onRender', $c);
        $c = str_replace('Kwf.onJElementShow', 'onReady.onShow', $c);
        $c = str_replace('Kwf.onJElementHide', 'onReady.onHide', $c);
        $c = str_replace('Kwf.onJElementWidthChange', 'onReady.onResize', $c);
    }
    if (strpos($c, 'Kwf.onElementReady') !== false || strpos($c, 'Kwf.onElementShow') !== false || strpos($c, 'Kwf.onElementHide') !== false || strpos($c, 'Kwf.onElementWidthChange') !== false) {
        $c = "var onReady = require('kwf/on-ready-ext2');\n".$c;
        $c = str_replace('Kwf.onElementReady', 'onReady.onRender', $c);
        $c = str_replace('Kwf.onElementShow', 'onReady.onShow', $c);
        $c = str_replace('Kwf.onElementHide', 'onReady.onHide', $c);
        $c = str_replace('Kwf.onElementWidthChange', 'onReady.onResize', $c);
    }
    if (strpos($c, 'Kwf.onComponentEvent') !== false || strpos($c, 'Kwf.fireComponentEvent') !== false) {
        $c = "var componentEvent = require('kwf/component-event');\n".$c;
        $c = str_replace('Kwf.onComponentEvent', 'componentEvent.on', $c);
        $c = str_replace('Kwf.fireComponentEvent', 'componentEvent.trigger', $c);
    }
    if (strpos($c, 'Kwf.getKwcRenderUrl') !== false) {
        $c = "var getKwcRenderUrl = require('kwf/get-kwc-render-url');\n".$c;
        $c = str_replace('Kwf.getKwcRenderUrl', 'getKwcRenderUrl', $c);
    }
    if (strpos($c, 'Kwc.Form.findForm(') !== false) {
        $c = "var findForm = require('kwf/frontend-form/find-form');\n".$c;
        $c = str_replace('Kwc.Form.findForm(', 'findForm(', $c);
    }
    if (strpos($c, 'Kwf.log(') !== false) {
        $c = "var kwfLog = require('kwf/log');\n".$c;
        $c = str_replace('Kwf.log(', 'kwfLog(', $c);
    }

    if ($c != $origC) {
        echo "Adapted to commonjs require: $file\n";
        file_put_contents($file, $c);
    }
}



if (!is_dir('cache/commonjs')) {
    mkdir('cache/commonjs');
    file_put_contents('cache/commonjs/.gitignore', "*\n!.gitignore\n");
    system("git add cache/commonjs/.gitignore");
    echo "folder \"cache/commonjs\" created\n";
}
if (!is_dir('cache/componentassets')) {
    mkdir('cache/componentassets');
    file_put_contents('cache/componentassets/.gitignore', "*\n!.gitignore\n");
    system("git add cache/componentassets/.gitignore");
    echo "folder \"cache/componentassets\" created\n";
}


$files = array_merge(
    glob_recursive('*.css')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    if (strpos($c, 'var(') !== false) {
        $scssFile = substr($file, 0, -4).'.scss';
        if (file_exists($scssFile)) {
            file_put_contents($scssFile, "\n".$c, FILE_APPEND);
            unlink($file);
        } else {
            rename($file, $scssFile);
        }
        echo "Renamed to scss to support assetVariables: $file\n";
    }
}

$files = array_merge(
    glob_recursive('*.scss')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    if (strpos($c, 'var(') !== false) {
        $c = "@import \"config/colors\";\n".$c;
        $c = preg_replace('#var\(([^\)]+)\)#', '$\1', $c);
        echo "Converted var() to scss: $file\n";
        file_put_contents($file, $c);
    }
}


$assetVariables = array(
    'mainColor' => '#314659',
    'secColor' => '#1E3040',
    'highlightedText' => '#c90000',
    'contentBg' => '#f4f4f4',
    'typo' => '#414742',
    'dark' => '#000',
    'light' => '#fff',
    'lightGrey' => '#707070',
    'errorBg' => '#d11313',
    'errorBorder' => '#bb1d1d',
    'errorText' => '#fff',
    'successBg' => '#7db800',
    'successBorder' => '#1e7638',
    'successText' => '#fff',
);
if (file_exists('assetVariables.ini')) {
    $ini = parse_ini_file('assetVariables.ini');
    foreach ($ini as $k=>$i) {
        $assetVariables[$k] = $i;
    }
    unlink('assetVariables.ini');
}
if (file_exists('config.ini')) {
    $ini = parse_ini_file('config.ini');
    foreach ($ini as $k=>$i) {
        if (substr($k, 0, 15) == 'assetVariables.') {
            $assetVariables[substr($k, 15)] = $i;
        }
    }
    $c = file_get_contents('config.ini');
    $c = preg_replace('#assetVariables\..*\n#', '', $c);
    file_put_contents('config.ini', $c);
}
if (file_exists('themes/Theme/config.ini')) {
    $ini = parse_ini_file('themes/Theme/config.ini');
    foreach ($ini as $k=>$i) {
        if (substr($k, 0, 15) == 'assetVariables.') {
            $assetVariables[substr($k, 15)] = $i;
        }
    }
    $c = file_get_contents('themes/Theme/config.ini');
    $c = preg_replace("#assetVariables\..*\n#", '', $c);
    file_put_contents('themes/Theme/config.ini', $c);
}
$c = '';
foreach ($assetVariables as $k=>$i) {
    $c .= "\$$k: $i;\n";
}
file_put_contents('scss/config/_colors.scss', $c);
echo "generated scss/config/_colors.scss\n";




$files = array_merge(
    glob_recursive('*.css')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('.$cssClass', '.cssClass', $c);
    if ($c != $origC) {
        echo "Converted .\$cssClass to .cssClass: $file\n";
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
    $c = str_replace('.cssClass', '.kwcClass', $c);
    if ($c != $origC) {
        echo "Converted .cssClass to .kwcClass: $file\n";
        file_put_contents($file, $c);
    }
}

$files = array_merge(
    glob_recursive('*.tpl')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('<?=$this->cssClass?>', '<?=$this->rootElementClass?>', $c);
    $c = str_replace('<?=$this->cssClass;?>', '<?=$this->rootElementClass?>', $c);
    if ($c != $origC) {
        echo "Converted cssClass to rootElementClass: $file\n";
        file_put_contents($file, $c);
    }
}

$files = array_merge(
    glob_recursive('*.twig')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('{{ cssClass }}', '{{ rootElementClass }}', $c);
    if ($c != $origC) {
        echo "Converted cssClass to rootElementClass: $file\n";
        file_put_contents($file, $c);
    }
}

$files = array_merge(
    glob_recursive('Component.php')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('[\'cssClass\']', '[\'rootElementClass\']', $c);
    if ($c != $origC) {
        echo "Converted ['cssClass'] to ['rootElementClass']: $file\n";
        file_put_contents($file, $c);
    }
}




$files = array_merge(
    glob_recursive('Component.printcss'),
    glob_recursive('Master.printcss'),
    glob_recursive('Web.printcss')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    unlink($file);
    $c = "\n@media print {\n$c\n}\n";
    if (file_exists(substr($file, 0, -8).'scss')) {
        $filename = substr($file, 0, -8).'scss';
    } else if (file_exists(substr($file, 0, -8).'css')) {
        $filename = substr($file, 0, -8).'css';
    } else {
        $filename = substr($file, 0, -8).'scss';
    }
    file_put_contents($filename, $c, FILE_APPEND);
    echo "Converted to media query: $file\n";
}



echo "\n";
echo "run now 'composer update' to update dependencies\n";
